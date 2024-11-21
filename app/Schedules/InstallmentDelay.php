<?php

namespace App\Schedules;

use App\Enums\InstallmentStatus;
use App\Models\Factor;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

class InstallmentDelay
{
    public function __invoke()
    {
        Factor::query()->chunk(50, function (Collection $factors) {
            foreach ($factors as $factor) {
                $installments = $factor
                    ->Installments()
                    ->where('installments.status', '!=', InstallmentStatus::Paid)
                    ->where('installments.due_date', '<', now())
                    ->get();
                foreach ($installments as $installment) {
                    $installment->calculateDelay();
                }
            }
        });
    }
}
