<?php

namespace App\Console\Commands;

use App\Enums\ReplacementAlertType;
use App\Models\Asset;
use App\Models\AssetReplacementAlert;
use App\Notifications\ReplacementAlertNotification;
use Illuminate\Console\Command;

class SendReplacementAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'replacement:send-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send replacement approaching and overdue alert notifications for home assets.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $assets = Asset::query()
            ->whereNotNull('install_date')
            ->whereNotNull('expected_lifespan_years')
            ->where('replacement_alerts_enabled', true)
            ->whereHas('user', fn ($q) => $q
                ->whereNotNull('email_verified_at')
                ->where('replacement_alerts_enabled', true)
            )
            ->with('user')
            ->get();

        foreach ($assets as $asset) {
            $replacementDate = $asset->install_date->copy()->addYears($asset->expected_lifespan_years);
            $daysRemaining = (int) now()->diffInDays($replacementDate, false);

            $alertType = $this->resolveAlertType($daysRemaining);

            if ($alertType === null) {
                continue;
            }

            if ($this->shouldSkip($asset->id, $alertType)) {
                continue;
            }

            $alert = AssetReplacementAlert::updateOrCreate(
                ['asset_id' => $asset->id, 'alert_type' => $alertType],
                ['sent_at' => now(), 'dismissed_at' => null],
            );

            $asset->user->notify(new ReplacementAlertNotification($asset, $alertType, $alert));
        }

        return self::SUCCESS;
    }

    private function resolveAlertType(int $daysRemaining): ?ReplacementAlertType
    {
        if ($daysRemaining <= 0) {
            return ReplacementAlertType::Overdue;
        }

        if ($daysRemaining <= 365) {
            return ReplacementAlertType::OneYear;
        }

        if ($daysRemaining <= 730) {
            return ReplacementAlertType::TwoYear;
        }

        return null;
    }

    private function shouldSkip(int $assetId, ReplacementAlertType $alertType): bool
    {
        return AssetReplacementAlert::where('asset_id', $assetId)
            ->where('alert_type', $alertType)
            ->where(fn ($q) => $q
                ->where(fn ($q2) => $q2->whereNotNull('sent_at')->whereNull('dismissed_at'))
                ->orWhereNotNull('dismissed_at')
            )
            ->exists();
    }
}
