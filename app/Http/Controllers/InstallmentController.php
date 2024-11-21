<?php

namespace App\Http\Controllers;

use App\Enums\InstallmentStatus;
use App\Enums\UserType;
use App\Http\Resources\InstallmentResource;
use App\Models\Factor;
use App\Models\Installment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InstallmentController extends Controller
{
    public function getAllDelayUsers(Request $request)
    {
//        $a = User::where('role', UserType::User)
//            ->join('factors', function (JoinClause $join) {
//                $join->on('users.id', '=', 'factors.user_id')
//                    ->where('is_delayed', '=', true);
//            })
//            ->select('users.*')
//            ->distinct()
//            ->paginate($request->query('per_page', 15));

//        return DB::table('users')
//            ->join('installments', function (JoinClause $join) {
//                $join->on('users.id', '=', 'installments.user_id')
//                    ->where('delay_days', '>', 0);
//            })
//            ->selectRaw('users.id, users.name, users.phone, users.national_code, sum(installments.delay_days) as delayed_days, count(installments.id) as delayed_installments')
//            ->groupBy('users.id')
//            ->paginate($request->query('per_page', 15));

        return DB::table('users')
            ->join('installments', function (JoinClause $join) {
                $join->on('users.id', '=', 'installments.user_id')
                    ->where('installments.delay_days', '>', 0);
            })
            ->select('users.id', 'users.name', 'users.phone', DB::raw('sum(installments.delay_days) as delay_sum'), DB::raw('count(installments.id) as delay_count'), DB::raw('sum(installments.amount + installments.delay_fine) as delay_amount'))
            ->groupBy('users.name', 'users.id', 'users.phone')
            ->orderByDesc('delay_sum')
            ->paginate($request->query('per_page', 15));
    }

    public function getCurrentDelayUsers(Request $request)
    {
//        return User::where('role', UserType::User)
//            ->join('factors', 'factors.user_id', '=', 'users.id')
//            ->join('installments', function (JoinClause $join) {
//                $join->on('installments.factor_id', '=', 'factors.id')
//                    ->where('installments.delay_days', '>', 0)
//                    ->where('installments.status', '!=', InstallmentStatus::Paid);
//            })
//            ->select('users.*')
//            ->distinct()
//            ->paginate($request->query('per_page', 15));

        return DB::table('users')
            ->join('installments', function (JoinClause $join) {
                $join->on('users.id', '=', 'installments.user_id')
                    ->where('installments.delay_days', '>', 0)
                    ->where('installments.status', '!=', InstallmentStatus::Paid);
            })
            ->select('users.id', 'users.name', 'users.phone', DB::raw('sum(installments.delay_days) as delay_sum'), DB::raw('count(installments.id) as delay_count'), DB::raw('sum(installments.amount + installments.delay_fine) as delay_amount'))
            ->groupBy('users.name', 'users.id', 'users.phone')
            ->orderByDesc('delay_sum')
            ->paginate($request->query('per_page', 15));
    }

    public function getNoDelayUsers(Request $request)
    {
        return User::where('role', UserType::User)
            ->join('factors', function (JoinClause $join) {
                $join->on('users.id', '=', 'factors.user_id')
                    ->where('is_delayed', '=', false);
            })
            ->select('users.*')
            ->distinct()
            ->paginate($request->query('per_page', 15));
    }

    public function getFactorInstallments(Factor $factor)
    {
        return InstallmentResource::collection($factor->Installments()->orderBy('due_date')->get());
    }

    public function changeStatus(Request $request, Installment $installment)
    {
        $request->validate([
            'status' => ['required', Rule::enum(InstallmentStatus::class)],
            'delay_days' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($installment->status == InstallmentStatus::Paid && $request->status != 'paid') {
            $installment->factor->user->subBalance($installment->amount);
        } elseif ($installment->status != InstallmentStatus::Paid && $request->status == 'paid') {
            $installment->factor->user->addBalance($installment->amount);
        }

        $status = match ($request->status) {
            'paid' => InstallmentStatus::Paid,
            'in_due' => InstallmentStatus::InDue,
        };

        $installment->update([
            'status' => $status,
            'delay_days' => $request->delay_days && $status == InstallmentStatus::Paid ? abs($request->delay_days) : 0,
            'delay_fine' => $request->delay_days && $status == InstallmentStatus::Paid ? round(($request->delay_days * 0.00117) * $installment->amount) : 0
        ]);
        $installment->refresh();

        return new InstallmentResource($installment);
    }

    public function createInstallments(Request $request, Factor $factor)
    {
        if ($factor->installments->isNotEmpty()) {
            return response(['message' => "اقساط این فاکتور قبلا تولید شده است!"], 403);
        }
        $request->validate([
            'months_count' => ['required', 'integer', 'min:1', 'max:9'],
            'profit_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'starting_date' => ['required', 'date'],
        ]);

        $total_installment_amount = $factor->final_price + ($factor->final_price * ($request->profit_percent / 100));
        $installment_amount = floor($total_installment_amount / $request->months_count);
        if ($installment_amount % 1000 != 0) {
            $installment_amount -= ($installment_amount % 1000);
            $installment_amount += 1000;
        }

        if ($total_installment_amount > $factor->user->balance) {
            return response(['message' => "اعتبار کاربر کافی نمی باشد."], 403);
        }
        $factor->user->subBalance($installment_amount * $request->months_count);

        $date = new Carbon($request->starting_date);
        $installments = [];
        for($i = 0; $i < $request->months_count; $i++) {
            $installments[] = [
                'user_id' => $factor->user_id,
                'factor_id' => $factor->id,
                'amount' => $installment_amount,
                'due_date' => $date->format('Y-m-d'),
            ];
            $month = (int) shamsi($date, false, "M", 'en_US');
            if ($month <= 6) {
                $date->addDays(31);
            } else {
                $date->addDays(30);
            }
        }

        $factor->installments()->createMany($installments);
        $factor->installment_profit_rate = $request->profit_percent;
        $factor->delay()->create([
            'factor_id' => $factor->id,
            'user_id' => $factor->user_id,
        ]);
        $factor->save();

        return InstallmentResource::collection($factor->Installments()->orderBy('due_date')->get());
    }

    public function deleteInstallments(Factor $factor)
    {
        if ($factor->installments->where('status', 'paid')->isNotEmpty()) {
            return response(['message' => "شما نمی توانید اقساط این فاکتور را حذف کنید. زیرا تعدادی اقساط پرداخت شده وجود دارد."], 403);
        }

        $factor->user->addBalance($factor->installments()->first()->amount * $factor->installments()->where('status', '!=' ,InstallmentStatus::Paid)->count());
        $factor->installments()->delete();
        $factor->delay()->delete();
        $factor->installment_profit_rate = null;

        return response()->noContent();
    }

    public function calculateRemaining(Factor $factor)
    {
        $remaining = 0;
        foreach($factor->installments()->where('status', '!=', InstallmentStatus::Paid)->get() as $installment) {
            $remaining += $installment->amount + ($installment->amount * ($installment->profit_rate / 100)) + $installment->calculateDelay()['delay_fine'];
        }


        $points = $factor
            ->installments()
            ->where('status', '!=', InstallmentStatus::Paid)
            ->where('due_date', '>=', now()->addDays(20))
            ->count();

        return [
            'total_price' => $remaining,
            'payable_price' => $remaining,
            'points' => $points,
        ];
    }

    public function calculateInstallment(Request $request, Installment $installment)
    {
        if (new Carbon($installment->due_date) < now()->addDays(20)) {
            return [
                'total_price' => $installment->amount + ($installment->amount * ($installment->profit_rate / 100)),
                'payable_price' => $installment->amount + ($installment->amount * ($installment->profit_rate / 100)) + $installment->calculateDelay()['delay_fine'],
                'points' => 0
            ];
        } else {
            return [
                'total_price' => $installment->amount + ($installment->amount * ($installment->profit_rate / 100)),
                'payable_price' => $installment->amount + ($installment->amount * ($installment->profit_rate / 100)) + $installment->calculateDelay()['delay_fine'],
                'points' => 1
            ];
        }
    }
}
