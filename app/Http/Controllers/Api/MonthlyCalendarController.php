<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonthlyCalendarController extends Controller
{
    public function monthlyCalendar(Request $request, $month = null)
    {
        $user = $request->user();

        if ($month < 1 || $month > 12) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid month',
            ], 400);
        }

        $year = now()->year;

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate   = Carbon::create($year, $month, 1)->endOfMonth();

        $logs = $user->MedicationLogs()
            ->whereBetween('log_date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($log) => $log->log_date);

        $calendar = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateString = $date->toDateString();

            if ($logs->has($dateString)) {
                $status = 'taken';
            } elseif ($date->lt(now()->startOfDay())) {
                $status = 'missed';
            } else {
                $status = 'none';
            }

            $calendar[] = [
                'date'   => $dateString,
                'status' => $status,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'month_label' => $startDate->translatedFormat('F'),
                'logs'        => $calendar,
            ],
        ]);
    }

}
