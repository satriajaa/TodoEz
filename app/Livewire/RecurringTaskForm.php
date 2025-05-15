<?php

namespace App\Livewire;

use Livewire\Component;

class RecurringTaskForm extends Component
{
    public $recurringPattern;
    public $recurringUntil;

    // Di dalam class RecurringTaskForm
    protected $listeners = ['refreshComponent' => '$refresh'];

    public function updatedRecurringPattern()
    {
        $this->dispatch('refreshComponent'); // Paksa render ulang
    }
    // Terima parameter, beri default value kosong
    public function mount($recurringPattern = '', $recurringUntil = '')
    {
        $this->recurringPattern = $recurringPattern ?? '';
        $this->recurringUntil = $recurringUntil ?? '';
    }

    // Validasi real-time
    protected $rules = [
        'recurringPattern' => 'nullable|in:daily,weekly,monthly,yearly',
        'recurringUntil' => 'required_if:recurringPattern,!=,null|date|after:today'
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
        if ($propertyName === 'recurringUntil' && $this->recurringPattern && $this->deadline) {
            if ($this->recurringUntil <= $this->deadline) {
                $this->addError('recurringUntil', 'Tanggal terakhir harus setelah deadline');
            }
        }
    }

    public function render()
    {
        return view('livewire.recurring-task-form');
    }
}
