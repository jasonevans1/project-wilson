<?php

namespace App\Services;

use App\Enums\RecurrenceUnit;
use App\Models\MaintenanceOccurrence;
use App\Models\MaintenanceTask;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class MaintenanceScheduler
{
    public function nextDueDate(CarbonInterface $fromDate, RecurrenceUnit $unit, int $count): Carbon
    {
        $date = Carbon::parse($fromDate);

        return match ($unit) {
            RecurrenceUnit::Daily => $date->addDays($count),
            RecurrenceUnit::Weekly => $date->addWeeks($count),
            RecurrenceUnit::Monthly => $date->addMonthsNoOverflow($count),
            RecurrenceUnit::Yearly => $date->addYearsNoOverflow($count),
        };
    }

    public function generateFirstOccurrence(MaintenanceTask $task, ?Carbon $startDate = null): MaintenanceOccurrence
    {
        return $task->occurrences()->create([
            'due_date' => $startDate ?? today(),
        ]);
    }

    public function completeOccurrence(MaintenanceOccurrence $occurrence): MaintenanceOccurrence
    {
        return DB::transaction(function () use ($occurrence) {
            $occurrence->update(['completed_at' => now()]);

            $nextDueDate = $this->nextDueDate(
                $occurrence->due_date,
                $occurrence->task->recurrence_unit,
                $occurrence->task->recurrence_count,
            );

            return $occurrence->task->occurrences()->create([
                'due_date' => $nextDueDate,
            ]);
        });
    }
}
