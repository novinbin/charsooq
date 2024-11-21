<?php

use App\Enums\InstallmentStatus;
use App\Models\User;
use App\Schedules\FactorDelay;
use App\Schedules\InstallmentDelay;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Artisan;
use App\Enums\BalanceRequestStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Schedule::call(new InstallmentDelay)->dailyAt("01:00");
Schedule::call(new FactorDelay)->dailyAt("01:30");

Artisan::command('testFeatures', function () {
    print_r(
        DB::table('users')
            ->join('installments', function (JoinClause $join) {
                $join->on('users.id', '=', 'installments.user_id')
                    ->where('installments.delay_days', '>', 0)
                    ->where('installments.status', '!=', InstallmentStatus::Paid);
            })
            ->select('users.id', 'users.name', 'users.phone', DB::raw('sum(installments.delay_days) as delay_sum'), DB::raw('count(installments.id) as delay_count'), DB::raw('sum(installments.amount + installments.delay_fine) as delay_amount'))
            ->groupBy('users.name', 'users.id', 'users.phone')
            ->orderByDesc('delay_sum')
            ->get()
    );
});

Artisan::command('setUserId', function () {
    \App\Models\Factor::query()->chunk(20, function (\Illuminate\Support\Collection $factors) {
        foreach ($factors as $factor) {
            $factor->installments()->update([
                'user_id' => $factor->user_id,
            ]);
        }
    });
});

Artisan::command('runInvokes', function () {
    $start = now();
    (new InstallmentDelay)->__invoke();
    (new FactorDelay)->__invoke();
    echo $start->diffInSeconds(now());
});

Artisan::command('recalculateBalances', function () {
    foreach (User::all() as $user) {
        $user->balance = round($user->BalanceInceases()->where('status', BalanceRequestStatus::Approved)->sum('amount'));
        $user->save();
    }
});

Artisan::command('calculateInstallmentBalance', function () {
    foreach (User::all() as $user) {
        $diff = 0;
        foreach (($user->factors ?? []) as $factor) {
            $diff += $factor->installments()->where('status', '!=', InstallmentStatus::Paid)->sum('amount');
        }
        if ($diff) {
            $user->subBalance($diff);
        }
    }
});

Artisan::command('testCalculateInstallmentBalance', function () {
    foreach (User::all() as $user) {
        $diff = 0;
        foreach (($user->factors ?? []) as $factor) {
            $diff += $factor->installments()->where('status', '!=', InstallmentStatus::Paid)->sum('amount');
        }
        echo $user->id . " -> $diff" . PHP_EOL;
    }
});

Artisan::command('lorem', function () {
    try {
        $dasht_factor = dasht()->getInvoice($this->ask('id ? '));
        $dasht_customer = dasht()->getCustomer($dasht_factor['CustomerRef']);
        $dasht_products = [];
        foreach ($dasht_factor['Items'] as $product) {
            $dasht_products[] = dasht()->getProduct($product['ItemRef']);
        }
    } catch (\Exception $e) {
        return response(['message' => $e->getMessage()], 400);
    }
    print_r($dasht_factor);
    print_r($dasht_customer);
    print_r($dasht_products);
});

Artisan::command('lorem:invoice', function () {
    try {
        $x = dasht()->getInvoice($this->ask('enter id: '));
        print_r($x);
    } catch (Exception $e) {
        print_r(json_decode($e->getMessage(), true));
    }
});

Artisan::command('lorem:login', function () {
    $guid = $this->ask('What is your Guid?');
    try {
        dasht()->login($guid);
        echo "Logged in successfully!";
    } catch (Exception $e) {
        print_r(json_decode($e->getMessage(), true));
    }
});

Artisan::command('lorem:user', function () {
    try {
        $x = dasht()->getCustomer($this->ask('enter id: '));
        print_r($x);
    } catch (Exception $e) {
        print_r(json_decode($e->getMessage(), true));
    }
});

Artisan::command('lorem:item', function () {
    try {
        $x = dasht()->getProduct($this->ask('enter id: '));
        print_r($x);
    } catch (Exception $e) {
        print_r(json_decode($e->getMessage(), true));
    }
});

Artisan::command('lorem:products', function () {
    try {
        $x = dasht()->getProducts(5);
        print_r($x);
    } catch (Exception $e) {
        print_r(json_decode($e->getMessage(), true));
    }
});

