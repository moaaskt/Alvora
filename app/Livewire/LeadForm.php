<?php

namespace App\Livewire;

use App\Models\Lead;
use Livewire\Component;

class LeadForm extends Component
{
    public string $name = '';
    public string $email = '';
    public string $company = '';
    public string $role = '';
    public string $employees_count = '21-100';
    public string $message = '';
    public bool $submitted = false;

    protected array $rules = [
        'name' => 'required|string|min:3|max:255',
        'email' => 'required|email|max:255',
        'company' => 'required|string|min:2|max:255',
        'role' => 'nullable|string|max:100',
        'employees_count' => 'nullable|string|max:50',
        'message' => 'nullable|string|max:1000',
    ];

    protected array $messages = [
        'name.required' => 'O nome é obrigatório.',
        'name.min' => 'O nome deve ter no mínimo 3 caracteres.',
        'email.required' => 'O e-mail corporativo é obrigatório.',
        'email.email' => 'Informe um endereço de e-mail corporativo válido.',
        'company.required' => 'O nome da empresa é obrigatório.',
    ];

    public function submit(): void
    {
        $validated = $this->validate();

        Lead::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'company' => $validated['company'],
            'role' => $validated['role'] ?: null,
            'employees_count' => $validated['employees_count'] ?: null,
            'message' => $validated['message'] ?: null,
            'status' => 'novo',
        ]);

        $this->reset(['name', 'email', 'company', 'role', 'message']);
        $this->employees_count = '21-100';
        $this->submitted = true;

        $this->dispatch('toast', 
            message: 'Solicitação de demonstração enviada! Entraremos em contato em breve.', 
            type: 'success'
        );
    }

    public function resetForm(): void
    {
        $this->submitted = false;
    }

    public function render()
    {
        return view('livewire.lead-form');
    }
}
