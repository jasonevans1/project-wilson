<?php

namespace App\Livewire\Maintenance;

use App\Models\Asset;
use App\Services\MaintenanceScheduler;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Prop;
use Livewire\Component;

class MaintenanceTaskList extends Component
{
    #[Prop]
    public Asset $asset;

    public ?int $editingOccurrenceId = null;

    public ?string $editDueDate = null;

    public function mount(): void
    {
        abort_if($this->asset->user_id !== Auth::id(), 403);
    }

    #[Computed]
    public function tasks(): Collection
    {
        return $this->asset->maintenanceTasks()
            ->active()
            ->with(['pendingOccurrence'])
            ->get();
    }

    #[On('task-created')]
    public function handleTaskCreated(): void
    {
        unset($this->tasks);
    }

    public function deactivateTask(int $taskId): void
    {
        Auth::user()->maintenanceTasks()->findOrFail($taskId)->update(['is_active' => false]);
        unset($this->tasks);
    }

    public function completeOccurrence(int $occurrenceId, MaintenanceScheduler $scheduler): void
    {
        $occurrence = \App\Models\MaintenanceOccurrence::findOrFail($occurrenceId);
        abort_if($occurrence->task->user_id !== Auth::id(), 403);
        $scheduler->completeOccurrence($occurrence);
        unset($this->tasks);
    }
}
