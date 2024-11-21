<?php

namespace App\Http\Controllers;

use App\Enums\InstallmentStatus;
use App\Enums\PaymentType;
use App\Http\Resources\TransactionResource;
use App\Models\Factor;
use App\Models\Installment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Shetabit\Payment\Facade\Payment;

class TransactionController extends Controller
{
    public function payInstallment(Request $request, Installment $installment)
    {
        if ($installment->factor->user->id != $request->user()->id || $installment->status == InstallmentStatus::Paid) {
            abort(403);
        }
        $amount = (int)$installment->amount + (int)$installment->delay_fine;

        return Payment::amount($amount)->purchase(
            null,
            function ($driver, $transactionId) use ($installment, $request, $amount) {
                $transaction = Transaction::firstOrcreate([
                    'user_id' => $request->user()->id,
                    'transaction_id' => $transactionId,
                    'type' => PaymentType::SingleInstallment,
                    'object_id' => $installment->id,
                    'amount' => $amount,
                ]);
                session(['transaction_id' => $transaction->id]);
            }
        )->pay()->render();
    }

    public function payAllInstallments(Request $request, Factor $factor)
    {
        if ($factor->status == 'paid' || $request->user()->id != $factor->user_id || !$factor->hasInstallments()) {
            abort(403);
        }

        $amount = 0;
        foreach ($factor->installments()->get() as $installment) {
            if ($installment->status != InstallmentStatus::Paid) {
                $amount += $installment->amount + $installment->calculateDelay()['delay_fine'];
            }
        }

        return Payment::amount($amount)->purchase(
            null,
            function ($driver, $transactionId) use ($factor, $request, $amount) {
                $transaction = Transaction::create([
                    'user_id' => $request->user()->id,
                    'transaction_id' => $transactionId,
                    'type' => PaymentType::AllInstallments,
                    'object_id' => $factor->id,
                    'amount' => $amount,
                ]);
                session(['transaction_id' => $transaction->id]);
            }
        )->pay()->render();
    }

    public function payFactor(Request $request, Factor $factor)
    {

    }

    public function verifyPayment(Request $request)
    {
        $transaction = Transaction::find(session('transaction_id'));
        if (!$transaction) {
            echo "مشکلی پیش آمده است. مبلغ پرداخت شده تا 48 ساعت آینده به حساب شما باز میگردد.";
            return null;
        }

        session()->forget('transaction_id');
        try {
            $receipt = Payment::amount($transaction->amount)->transactionId($transaction->transaction_id)->verify();
            $transaction->update([
                'reference_id' => $receipt->getReferenceId(),
                'status' => 'paid',
            ]);

            $transaction->object()->setPaid();
            $transaction->setPaid();
            return redirect(env('FRONTEND_URL') . '/user/dashboard' );

        } catch (InvalidPaymentException $exception) {
            $transaction->setFailed();
            echo $exception->getMessage();
            return null;
        }

    }

    public function getTodayTransactions(Request $request)
    {
        return TransactionResource::collection(
            $this->setTransactionsSearchQuery($request)
                ->whereBetween('transactions.updated_at', [today()->startOfDay(), today()->endOfDay()])
                ->latest()
                ->select('transactions.*')
                ->paginate($request->query('per_page', 15))
        );
    }

    public function getWeekTransactions(Request $request)
    {
        return TransactionResource::collection(
            $this->setTransactionsSearchQuery($request)
                ->whereBetween('transactions.updated_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->latest()
                ->select('transactions.*')
                ->paginate($request->query('per_page', 15))
        );
    }

    public function getMonthTransactions(Request $request)
    {
        return TransactionResource::collection(
            $this->setTransactionsSearchQuery($request)
                ->whereBetween('transactions.updated_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->latest()
                ->select('transactions.*')
                ->paginate($request->query('per_page', 15))
        );
    }

    public function getAllTransactions(Request $request)
    {
        return TransactionResource::collection(
            $this->setTransactionsSearchQuery($request)
                ->latest()
                ->select('transactions.*')
                ->paginate($request->query('per_page', 15))
        );
    }

    public function transactionsOfUsers(Request $request)
    {
        $query = User::query();
        if ($name = $request->query('name')) {
            $query->where('name', 'like', '%' . $name . '%');
        } elseif ($phone = $request->query('phone')) {
            $query->where('phone', 'like', '%' . $phone . '%');
        }
        return $query
            ->whereExists(function (Builder $query) {
                $query->select(DB::raw(1))
                    ->from('transactions')
                    ->whereColumn('transactions.user_id', 'users.id');
            })
            ->paginate($request->query('per_page', 15));
    }

    public function getUserTransactions(Request $request, User $user)
    {
        return TransactionResource::collection(
            $user
                ->transactions()
                ->latest()
                ->paginate($request->query('per_page', 15))
        );
    }

    private function setTransactionsSearchQuery(Request $request)
    {
        $query = Transaction::query();
        if ($name = $request->query('name')) {
            $query->join('users', function (JoinClause $join) use ($name) {
                $join->on('transactions.user_id', '=', 'users.id')
                    ->where('users.name', 'like', '%' . $name . '%');
            });
        } elseif ($phone = $request->query('phone')) {
            $query->join('users', function (JoinClause $join) use ($phone) {
                $join->on('transactions.user_id', '=', 'users.id')
                    ->where('users.phone', 'like', '%' . $phone . '%');
            });
        }
        return $query;
    }

}
