<?php

namespace App\Livewire\Maintenance;

use App\Models\MaintenanceOccurrence;
use App\Services\MaintenanceScheduler;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class MaintenanceSchedule extends Component
{
    public ?int $filterAssetId = null;

    #[Computed]
    public function occurrences(): Collection
    {
        return MaintenanceOccurrence::query()
            ->whereNull('completed_at')
            ->whereHas('task', fn ($q) => $q->where('user_id', Auth::id())->active())
            ->when($this->filterAssetId, fn ($q) => $q->whereHas('task', fn ($q2) => $q2->where('asset_id', $this->filterAssetId)))
            ->with(['task', 'task.asset'])
            ->orderBy('due_date')
            ->get();
    }

    #[Computed]
    public function assets(): Collection
    {
        return Auth::user()->assets()->get();
    }

    #[On('task-created')]
    public function handleTaskCreated(): void
    {
        unset($this->occurrences);
    }

    public function filterByAsset(?int $assetId): void
    {
        $this->filterAssetId = $assetId;
        unset($this->occurrences);
    }

    public function completeOccurrence(int $occurrenceId, MaintenanceScheduler $scheduler): void
    {
        $occurrence = MaintenanceOccurrence::findOrFail($occurrenceId);
        abort_if($occurrence->task->user_id !== Auth::id(), 403);
        $scheduler->completeOccurrence($occurrence);
        unset($this->occurrences);
    }
}
