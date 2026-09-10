<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Staff;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Turns a doctor's recurring weekly schedule into concrete bookable slots
 * for a given date, minus slots already taken.
 */
class AppointmentSlots
{
    /**
     * @return Collection<int, array{time:string, label:string, branch_id:int, taken:bool}>
     */
    public function for(Staff $doctor, CarbonImmutable $date, ?int $branchId = null): Collection
    {
        $schedules = $doctor->schedules()
            ->where('is_active', true)
            ->where('day_of_week', $date->dayOfWeek)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when(true, fn ($q) => $q
                ->where(fn ($w) => $w->whereNull('effective_from')->orWhereDate('effective_from', '<=', $date))
                ->where(fn ($w) => $w->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date)))
            ->get();

        $taken = Appointment::where('doctor_id', $doctor->id)
            ->forDate($date)
            ->open()
            ->whereNotNull('scheduled_time')
            ->pluck('scheduled_time')
            ->map(fn ($t) => substr((string) $t, 0, 5))
            ->all();

        $slots = collect();

        foreach ($schedules as $schedule) {
            $cursor = $date->setTimeFromTimeString((string) $schedule->start_time);
            $end = $date->setTimeFromTimeString((string) $schedule->end_time);
            $count = 0;

            while ($cursor->lt($end)) {
                if ($schedule->max_tokens && $count >= $schedule->max_tokens) {
                    break;
                }

                $time = $cursor->format('H:i');

                $slots->push([
                    'time' => $time,
                    'label' => $cursor->format('g:i A'),
                    'branch_id' => $schedule->branch_id,
                    'taken' => in_array($time, $taken, true),
                ]);

                $cursor = $cursor->addMinutes($schedule->slot_minutes);
                $count++;
            }
        }

        return $slots->unique('time')->sortBy('time')->values();
    }

    public function nextTokenNo(Staff $doctor, CarbonImmutable $date): int
    {
        return Appointment::where('doctor_id', $doctor->id)
            ->forDate($date)
            ->whereIn('status', [...Appointment::OPEN_STATUSES, 'completed'])
            ->max('token_no') + 1;
    }
}
