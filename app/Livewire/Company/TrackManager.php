<?php

namespace App\Livewire\Company;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LearningTrack;
use App\Models\TrackEnrollment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TrackManager extends Component
{
    public bool $showModal = false;
    public ?int $editingTrackId = null;

    public string $title = '';
    public string $description = '';
    public string $department = '';
    public bool $is_mandatory = true;

    /** @var array<int> */
    public array $selectedCourses = [];

    /** @var array<int> */
    public array $selectedUsers = [];

    public bool $assignAllEmployees = false;
    public string $search = '';

    protected array $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'department' => 'nullable|string|max:100',
        'is_mandatory' => 'boolean',
        'selectedCourses' => 'nullable|array',
        'selectedCourses.*' => 'integer|exists:courses,id',
        'selectedUsers' => 'nullable|array',
        'selectedUsers.*' => 'integer|exists:users,id',
    ];

    protected array $messages = [
        'title.required' => 'O título da trilha é obrigatório.',
        'title.max' => 'O título não pode ter mais de 255 caracteres.',
    ];

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset([
            'editingTrackId',
            'title',
            'description',
            'department',
            'selectedCourses',
            'selectedUsers',
            'assignAllEmployees',
        ]);
        $this->is_mandatory = true;
        $this->showModal = true;
    }

    public function editTrack(int $trackId): void
    {
        $this->resetValidation();
        $track = LearningTrack::with(['courses', 'enrollments'])->findOrFail($trackId);

        $this->editingTrackId = $track->id;
        $this->title = $track->title;
        $this->description = $track->description ?? '';
        $this->department = $track->department ?? '';
        $this->is_mandatory = (bool) $track->is_mandatory;
        $this->selectedCourses = $track->courses->pluck('id')->toArray();
        $this->selectedUsers = $track->enrollments->pluck('user_id')->toArray();
        $this->assignAllEmployees = false;
        $this->showModal = true;
    }

    public function toggleCourse(int $courseId): void
    {
        if (in_array($courseId, $this->selectedCourses)) {
            $this->selectedCourses = array_values(array_diff($this->selectedCourses, [$courseId]));
        } else {
            $this->selectedCourses[] = $courseId;
        }
    }

    public function moveCourseUp(int $index): void
    {
        if ($index > 0 && isset($this->selectedCourses[$index])) {
            $temp = $this->selectedCourses[$index - 1];
            $this->selectedCourses[$index - 1] = $this->selectedCourses[$index];
            $this->selectedCourses[$index] = $temp;
        }
    }

    public function moveCourseDown(int $index): void
    {
        if ($index < count($this->selectedCourses) - 1 && isset($this->selectedCourses[$index])) {
            $temp = $this->selectedCourses[$index + 1];
            $this->selectedCourses[$index + 1] = $this->selectedCourses[$index];
            $this->selectedCourses[$index] = $temp;
        }
    }

    public function toggleUser(int $userId): void
    {
        if (in_array($userId, $this->selectedUsers)) {
            $this->selectedUsers = array_values(array_diff($this->selectedUsers, [$userId]));
        } else {
            $this->selectedUsers[] = $userId;
        }
    }

    public function saveTrack(): void
    {
        $this->validate();

        $user = Auth::user();
        $companyId = $user->company_id;

        if (! $companyId) {
            $this->dispatch('toast', message: 'Você precisa estar vinculado a uma empresa para gerenciar trilhas.', type: 'error');
            return;
        }

        if ($this->editingTrackId) {
            $track = LearningTrack::findOrFail($this->editingTrackId);
            $track->update([
                'title' => $this->title,
                'description' => $this->description ?: null,
                'department' => $this->department ?: null,
                'is_mandatory' => $this->is_mandatory,
            ]);
        } else {
            $track = LearningTrack::create([
                'company_id' => $companyId,
                'title' => $this->title,
                'description' => $this->description ?: null,
                'department' => $this->department ?: null,
                'is_mandatory' => $this->is_mandatory,
            ]);
        }

        // Sync courses with their order
        $syncCourses = [];
        foreach ($this->selectedCourses as $orderIndex => $courseId) {
            $syncCourses[$courseId] = ['order' => $orderIndex + 1];
        }
        $track->courses()->sync($syncCourses);

        // Determine users to enroll
        $targetUserIds = $this->selectedUsers;
        if ($this->assignAllEmployees) {
            $companyUserIds = User::where('company_id', $companyId)->pluck('id')->toArray();
            $targetUserIds = array_unique(array_merge($targetUserIds, $companyUserIds));
        }

        // Enroll users in the track and its courses
        foreach ($targetUserIds as $uId) {
            $trackEnrollment = TrackEnrollment::firstOrCreate([
                'company_id' => $companyId,
                'user_id' => $uId,
                'track_id' => $track->id,
            ]);

            // Ensure enrollment in all courses belonging to this track
            foreach ($this->selectedCourses as $courseId) {
                Enrollment::firstOrCreate(
                    [
                        'company_id' => $companyId,
                        'user_id' => $uId,
                        'course_id' => $courseId,
                    ],
                    [
                        'progress_percent' => 0,
                        'status' => 'enrolled',
                    ]
                );
            }

            $trackEnrollment->updateConsolidatedProgress();
        }

        $this->showModal = false;
        $this->dispatch('toast', message: 'Trilha corporativa salva com sucesso!', type: 'success');
    }

    public function deleteTrack(int $trackId): void
    {
        $track = LearningTrack::findOrFail($trackId);
        $track->delete();

        $this->dispatch('toast', message: 'Trilha corporativa removida com sucesso.', type: 'info');
    }

    public function render()
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        $tracksQuery = LearningTrack::with(['courses', 'enrollments'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('title', 'like', "%{$this->search}%")
                        ->orWhere('department', 'like', "%{$this->search}%");
                });
            })
            ->latest();

        $tracks = $tracksQuery->get();

        // Available published courses of this company
        $availableCourses = Course::where('company_id', $companyId)
            ->where('status', 'published')
            ->orderBy('title')
            ->get();

        // Available employees of this company
        $availableEmployees = User::where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        return view('livewire.company.track-manager', [
            'tracks' => $tracks,
            'availableCourses' => $availableCourses,
            'availableEmployees' => $availableEmployees,
        ])->layout('layouts.app', ['title' => 'Trilhas de Aprendizado Corporativas — ALVORA']);
    }
}
