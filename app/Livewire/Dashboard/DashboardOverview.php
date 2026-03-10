<?php

namespace App\Livewire\Dashboard;

use App\Enums\AssetStatus;
use App\Models\MaintenanceOccurrence;
use App\Models\MaintenanceTask;
use App\Models\ServiceRecord;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DashboardOverview extends Component
{
    /**
     * Total count of active (non-archived) assets owned by the authenticated user.
     */
    #[Computed]
    public function assetCount(): int
    {
        return Auth::user()->assets()->where('status', AssetStatus::Active)->count();
    }

    /**
     * Count of incomplete maintenance occurrences past their due date,
     * scoped to the authenticated user's active tasks.
     */
    #[Computed]
    public function overdueMaintenanceCount(): int
    {
        return MaintenanceOccurrence::query()
            ->whereNull('completed_at')
            ->whereHas('task', fn ($q) => $q->where('user_id', Auth::id())->active())
            ->where('due_date', '<', today())
            ->count();
    }

    /**
     * Count of incomplete maintenance occurrences due within the next 7 days,
     * scoped to the authenticated user's active tasks.
     */
    #[Computed]
    public function upcomingMaintenanceCount(): int
    {
        return MaintenanceOccurrence::query()
            ->whereNull('completed_at')
            ->whereHas('task', fn ($q) => $q->where('user_id', Auth::id())->active())
            ->whereBetween('due_date', [today(), today()->addDays(7)])
            ->count();
    }

    /**
     * The most recently dated service record for the authenticated user,
     * with the related asset eager-loaded.
     */
    #[Computed]
    public function latestServiceRecord(): ?ServiceRecord
    {
        return Auth::user()->serviceRecords()->with('asset')->latest('service_date')->first();
    }

    /**
     * Whether the authenticated user has any active maintenance tasks.
     * Used to distinguish "all caught up" from "no tasks yet" in the view.
     */
    #[Computed]
    public function hasMaintenanceTasks(): bool
    {
        return MaintenanceTask::query()
            ->where('user_id', Auth::id())
            ->active()
            ->exists();
    }

    /**
     * Count of active assets with replacement tracking configured whose replacement
     * date falls on or before 12 months from today. Returns null when no assets
     * have replacement tracking configured (install_date + expected_lifespan_years).
     */
    #[Computed]
    public function approachingReplacementCount(): ?int
    {
        $trackedAssets = Auth::user()
            ->assets()
            ->where('status', AssetStatus::Active)
            ->whereNotNull('install_date')
            ->whereNotNull('expected_lifespan_years')
            ->get();

        if ($trackedAssets->isEmpty()) {
            return null;
        }

        return $trackedAssets
            ->filter(fn ($asset) => $asset->install_date->copy()->addYears($asset->expected_lifespan_years)->lte(now()->addYear()))
            ->count();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard.dashboard-overview')
            ->layout('layouts.app');
    }
}
