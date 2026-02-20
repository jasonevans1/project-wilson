<?php

namespace App\Livewire\Maintenance;

use App\Enums\RecurrenceUnit;
use App\Services\MaintenanceScheduler;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Prop;
use Livewire\Component;

class MaintenanceTaskForm extends Component
{
    #[Prop]
    public int $assetId;

    public string $name = '';

    public string $description = '';

    public string $recurrenceUnit = 'monthly';

    public int $recurrenceCount = 1;

    public ?string $startDate = null;

    public function save(MaintenanceScheduler $scheduler): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'assetId' => [
                'required',
                'integer',
                Rule::exists('assets', 'id')->where('user_id', Auth::id()),
            ],
            'recurrenceUnit' => ['required', Rule::enum(RecurrenceUnit::class)],
            'recurrenceCount' => ['required', 'integer', 'min:1', 'max:365'],
            'description' => ['nullable', 'string', 'max:2000'],
            'startDate' => ['nullable', 'date'],
        ]);

        $task = Auth::user()->maintenanceTasks()->create([
            'asset_id' => $this->assetId,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'recurrence_unit' => $this->recurrenceUnit,
            'recurrence_count' => $this->recurrenceCount,
        ]);

        $startDate = $this->startDate ? Carbon::parse($this->startDate) : null;
        $scheduler->generateFirstOccurrence($task, $startDate);

        $this->dispatch('task-created');
        $this->reset(['name', 'description', 'recurrenceUnit', 'recurrenceCount', 'startDate']);
        $this->recurrenceUnit = 'monthly';
        $this->recurrenceCount = 1;
    }
}
