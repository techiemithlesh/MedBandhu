<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/**
 * Parses a report's date range from the request, defaulting to the
 * current month. Also offers common presets for the filter UI.
 */
class ReportRange
{
    public function __construct(
        public readonly CarbonImmutable $from,
        public readonly CarbonImmutable $to,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $to = $request->filled('to')
            ? CarbonImmutable::parse($request->date('to'))->endOfDay()
            : CarbonImmutable::today()->endOfDay();

        $from = $request->filled('from')
            ? CarbonImmutable::parse($request->date('from'))->startOfDay()
            : CarbonImmutable::today()->startOfMonth();

        if ($from->gt($to)) {
            [$from, $to] = [$to->startOfDay(), $from->endOfDay()];
        }

        return new self($from, $to);
    }

    public function days(): int
    {
        return $this->from->startOfDay()->diffInDays($this->to->startOfDay()) + 1;
    }

    /** @return array<string, array{from:string, to:string}> */
    public static function presets(): array
    {
        $today = CarbonImmutable::today();

        return [
            'Today' => ['from' => $today->toDateString(), 'to' => $today->toDateString()],
            'This week' => ['from' => $today->startOfWeek()->toDateString(), 'to' => $today->toDateString()],
            'This month' => ['from' => $today->startOfMonth()->toDateString(), 'to' => $today->toDateString()],
            'Last month' => ['from' => $today->subMonthNoOverflow()->startOfMonth()->toDateString(), 'to' => $today->subMonthNoOverflow()->endOfMonth()->toDateString()],
            'Last 90 days' => ['from' => $today->subDays(89)->toDateString(), 'to' => $today->toDateString()],
        ];
    }

    public function label(): string
    {
        return $this->from->isSameDay($this->to)
            ? $this->from->format('d M Y')
            : $this->from->format('d M Y').' — '.$this->to->format('d M Y');
    }
}
