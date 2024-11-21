<?php

namespace App\Schedules;

use App\Enums\InstallmentStatus;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class FactorDelay
{
    public function __invoke()
    {
        DB::table('factors')
            ->where('factors.is_delayed', '=' , false)
            ->join('installments', function (JoinClause $join) {
                $join->on('factors.id', '=', 'installments.factor_id')
                    ->where('installments.delay_days', '>', 0);
            })
            ->select('factors.*')
            ->distinct()
            ->update([
                'factors.is_delayed' => true
            ]);

    }
}
