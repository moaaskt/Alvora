@props([
    'course',
    'enrollment' => null, // Optional Enrollment model if already enrolled
])

@php
    // If enrollment is not passed explicitly, attempt to get it from loaded relations
    if (!$enrollment && $course->relationLoaded('enrollments')) {
        $enrollment = $course->enrollments->firstWhere('user_id', auth()->id());
    }

    $isEnrolled = (bool) $enrollment;
    $progressPercent = $isEnrolled ? (int) $enrollment->progress_percent : 0;
    $isCompleted = $isEnrolled && ($enrollment->status === 'completed' || $progressPercent >= 100);
    $inProgress = $isEnrolled && !$isCompleted && $progressPercent > 0;
    $notStarted = $isEnrolled && !$isCompleted && $progressPercent === 0;

    // Counts
    $lessonsCount = $course->lessons_count ?? $course->lessons()->count();
    $quizzesCount = $course->quizzes_count ?? 0;
    $totalMinutes = $course->total_minutes ?? 0;
    $durationText = \App\Helpers\DurationHelper::format((int) $totalMinutes);

    $aulasText = $lessonsCount === 1 ? '1 aula' : "{$lessonsCount} aulas";
    $quizzesText = $quizzesCount === 1 ? '1 quiz' : "{$quizzesCount} quizzes";
@endphp

<div class="group flex flex-col h-full bg-edtech-surface rounded-[16px] border border-edtech-border shadow-card hover:shadow-card-hover motion-safe:hover:-translate-y-1.5 transition-all duration-300 p-4 justify-between">
    <div class="flex flex-col flex-1">
        <!-- 1. Cover Image (16:10 aspect ratio, zoom 1.05 on hover) -->
        <div class="w-full aspect-[16/10] rounded-[12px] overflow-hidden bg-edtech-border-light shrink-0 relative border border-edtech-border/60">
            @if ($course->thumbnail_url)
                <img src="{{ $course->thumbnail_url }}"
                     alt="{{ $course->title }}"
                     loading="lazy"
                     class="w-full h-full object-cover motion-safe:group-hover:scale-105 transition-transform duration-300 ease-out">
            @else
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-edtech-navy to-edtech-blue-dark text-white motion-safe:group-hover:scale-105 transition-transform duration-300 ease-out">
                    <x-icon name="book-open" class="w-10 h-10 text-white/85" />
                </div>
            @endif
        </div>

        <!-- 2. Info Row (Appears EXACTLY ONCE, directly below cover and above title: 12px text, 14px slate line icons, 14px gap, no dividers, whitespace-nowrap, on one line) -->
        <div class="flex items-center gap-[14px] text-[12px] text-edtech-muted mt-3 mb-2 whitespace-nowrap overflow-x-auto no-scrollbar">
            <!-- Aulas -->
            <span class="flex items-center gap-1.5 font-normal shrink-0">
                <x-icon name="book-open" class="w-3.5 h-3.5 text-edtech-muted shrink-0" />
                <span>{{ $aulasText }}</span>
            </span>

            <!-- Quizzes (hidden when 0) -->
            @if ($quizzesCount > 0)
                <span class="flex items-center gap-1.5 font-normal shrink-0">
                    <x-icon name="clipboard-check" class="w-3.5 h-3.5 text-edtech-muted shrink-0" />
                    <span>{{ $quizzesText }}</span>
                </span>
            @endif

            <!-- Duração (hidden when 0) -->
            @if (!empty($durationText))
                <span class="flex items-center gap-1.5 font-normal shrink-0">
                    <x-icon name="clock" class="w-3.5 h-3.5 text-edtech-muted shrink-0" />
                    <span>{{ $durationText }}</span>
                </span>
            @endif
        </div>

        <!-- 3. Title (max 2 lines, clean height) -->
        <h3 class="font-semibold text-[17px] text-edtech-text line-clamp-2 leading-snug min-h-[2.75rem]" title="{{ $course->title }}">
            {{ $course->title }}
        </h3>

        <!-- 4. Description (max 2 lines) -->
        <p class="text-[13px] text-edtech-muted mt-1.5 line-clamp-2 leading-relaxed min-h-[2.5rem]">
            {{ $course->description ?: 'Sem descrição informada.' }}
        </p>

        <!-- 5. Progress Bar / Spacer (Normalized height to align buttons) -->
        <div class="mt-auto pt-4 pb-2 min-h-[38px] flex flex-col justify-end">
            @if ($isCompleted)
                <div class="flex items-center justify-between text-[12px] mb-1.5">
                    <span class="font-medium text-alvora-emerald flex items-center gap-1">
                        <x-icon name="check-circle" class="w-3.5 h-3.5 text-alvora-emerald shrink-0" />
                        Concluído
                    </span>
                    <span class="font-semibold text-alvora-emerald tabular-nums">100%</span>
                </div>
                <div class="w-full h-2 bg-edtech-border rounded-full overflow-hidden">
                    <div class="h-full bg-alvora-emerald rounded-full" style="width: 100%;"></div>
                </div>
            @elseif ($inProgress)
                <div class="flex items-center justify-between text-[12px] mb-1.5">
                    <span class="text-edtech-muted font-medium">Progresso</span>
                    <span class="font-semibold text-edtech-blue tabular-nums">{{ $progressPercent }}%</span>
                </div>
                <div class="w-full h-2 bg-edtech-border rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-edtech-blue to-edtech-blue-dark rounded-full transition-all duration-300" style="width: {{ $progressPercent }}%;"></div>
                </div>
            @elseif ($notStarted)
                <div class="flex items-center justify-between text-[12px] mb-1.5">
                    <span class="text-edtech-muted font-medium">Progresso</span>
                    <span class="font-semibold text-edtech-muted tabular-nums">0%</span>
                </div>
                <div class="w-full h-2 bg-edtech-border rounded-full overflow-hidden">
                    <div class="h-full bg-edtech-border rounded-full" style="width: 0%;"></div>
                </div>
            @else
                <!-- Available / Not enrolled spacer -->
                <div class="h-2 w-full"></div>
            @endif
        </div>
    </div>

    <!-- 6. Bottom Action Button (44px height) -->
    <div class="mt-3">
        @if ($isCompleted)
            <div class="flex flex-col gap-2">
                <button type="button"
                        wire:click="continueCourse({{ $course->id }})"
                        class="w-full h-[44px] px-4 rounded-xl border border-edtech-blue text-edtech-blue font-semibold text-sm hover:bg-edtech-blue-light transition-colors cursor-pointer flex items-center justify-center">
                    Rever Aulas
                </button>
            </div>
        @elseif ($inProgress)
            <button type="button"
                    wire:click="continueCourse({{ $course->id }})"
                    class="w-full h-[44px] px-4 rounded-xl bg-edtech-blue hover:bg-edtech-blue-hover text-white font-semibold text-sm transition-colors shadow-xs cursor-pointer flex items-center justify-center">
                Continuar Aula
            </button>
        @elseif ($notStarted)
            <button type="button"
                    wire:click="continueCourse({{ $course->id }})"
                    class="w-full h-[44px] px-4 rounded-xl bg-edtech-blue hover:bg-edtech-blue-hover text-white font-semibold text-sm transition-colors shadow-xs cursor-pointer flex items-center justify-center">
                Começar
            </button>
        @else
            <button type="button"
                    wire:click="enroll({{ $course->id }})"
                    class="w-full h-[44px] px-4 rounded-xl bg-edtech-blue hover:bg-edtech-blue-hover text-white font-semibold text-sm transition-colors shadow-xs cursor-pointer flex items-center justify-center">
                Matricular-se Gratuitamente
            </button>
        @endif
    </div>
</div>

