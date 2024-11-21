<?php

namespace App\Http\Controllers;

use App\Enums\BalanceRequestStatus;
use App\Enums\Gender;
use App\Enums\InstallmentStatus;
use App\Enums\UserType;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Resources\BalanceRequestResource;
use App\Models\User;
use App\Models\UserCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function getDashboard(Request $request)
    {
        $user = $request->user();
        $all_checks = $user->BalanceInceases()->count();
        $pending_checks = $user->BalanceInceases()->where('status', BalanceRequestStatus::Pending)->count();
        $accepted_checks = $user->BalanceInceases()->where('status', BalanceRequestStatus::Approved)->count();
        $rejected_checks = $user->BalanceInceases()->where('status', BalanceRequestStatus::Rejected)->count();

        $factors = $user->factors;
        $all_factors = $factors->count();
        $total_shopping = $user->factors()->sum('final_price');

        $all_installments = 0;
        $pending_installments = 0;
        $delayed_installments = 0;
        $paid_installments = 0;
        foreach ($factors as $factor) {
            $all_installments += $factor->installments()->count();
            $pending_installments += $factor->installments()->where('status', InstallmentStatus::InDue)->count();
            $delayed_installments += $factor->installments()->where('status', InstallmentStatus::Delayed)->count();
            $paid_installments += $factor->installments()->where('status', InstallmentStatus::Paid)->count();
        }

        return [
            'all_checks' => $all_checks,
            'pending_checks' => $pending_checks,
            'accepted_checks' => $accepted_checks,
            'rejected_checks' => $rejected_checks,
            'all_factors' => $all_factors,
            'total_shopping' => $total_shopping,
            'all_installments' => $all_installments,
            'pending_installments' => $pending_installments,
            'delayed_installments' => $delayed_installments,
            'paid_installments' => $paid_installments,
        ];
    }

    public function myBalanceIncreases(Request $request)
    {
        $user = $request->user();
        return BalanceRequestResource::collection($user->BalanceInceases()->paginate(10));
    }

    public function getUsersByCategory(Request $request, UserCategory $category)
    {
        return User::where('user_category_id', $category->id)->latest()->paginate($request->get('per_page', 10));
    }

    public function getAll(Request $request)
    {
        if ($name = $request->query('name')) {
            return User::where('name', 'like', '%' . $name . '%')->orderBy('created_at', 'desc')->paginate(10);
        }

        if ($phone = $request->query('phone')) {
            return User::where('phone', 'like', '%' . $phone . '%')->orderBy('created_at', 'desc')->paginate(10);
        }

        return User::orderBy('created_at', 'desc')->paginate($request->query('per_page', 10));
    }

    public function getAllCustomers(Request $request)
    {
        $query = User::where('role', UserType::User);
        if ($name = $request->query('name')) {
            return $query->where('name', 'like', '%' . $name . '%')->orderBy('created_at', 'desc')->paginate(10);
        }

        if ($phone = $request->query('phone')) {
            return $query->where('phone', 'like', '%' . $phone . '%')->orderBy('created_at', 'desc')->paginate(10);
        }

        return $query->orderBy('created_at', 'desc')->paginate($request->query('per_page', 10));
    }

    public function read(Request $request, User $user)
    {
        return $user;
    }

    public function update(Request $request, User $user)
    {
        $this->validateUser($request, $user);
        if ($request->user()->isEmployee() && $user->isAdmin()) {
            return response(['message' => "شما نمی توانید اطلاعات مدیر را تغییر دهید."], 403);
        }
        if ($request->user()->isEmployee() && $user->isAuthor()) {
            return response(['message' => "شما نمی تواند اطلاعات نویسندگان را ویرایش کنید."], 403);
        }
        if ($request->national_code && !validate_national_code($request->national_code)) {
            return response(['message' => "کد ملی معتبر نمی باشد."], 422);
        }
        $input = $request->except(['password']);
        $input['name'] = $request->get('first_name') . ' ' . $request->get('last_name');
        if ($request->has('password')) {
            $request->validate(['password' => ['required', 'confirmed', Rules\Password::defaults()]]);
            $input['password'] = Hash::make($request->get('password'));
        }

        $user->update($input);

        return $user;
    }

    public function updateSelf(Request $request)
    {
        $user = $request->user();
        $this->validateUser($request, $user);
        if ($request->user()->isEmployee() && $user->isAdmin()) {
            return response(['message' => "شما نمی توانید اطلاعات مدیر را تغییر دهید."], 403);
        }
        if ($request->user()->isEmployee() && $user->isAuthor()) {
            return response(['message' => "شما نمی تواند اطلاعات نویسندگان را ویرایش کنید."], 403);
        }

        if ($request->national_code && !validate_national_code($request->national_code)) {
            return response(['message' => "کد ملی معتبر نمی باشد."], 422);
        }

        $input = $request->only([
            'first_name',
            'last_name',
            'gender',
            'phone',
            'email',
            'address',
            'city',
            'state',
            'postal_code',
            'national_code',
            'password',
        ]);
        $input['name'] = $request->get('first_name') . ' ' . $request->get('last_name');
        if ($request->has('password')) {
            $request->validate(['password' => ['required', 'confirmed', Rules\Password::defaults()]]);
            $input['password'] = Hash::make($request->get('password'));
        }

        $user->update($input);

        return $user;
    }

    public function delete(Request $request ,User $user)
    {
        if ($request->user()->isEmployee() && $user->isAdmin()) {
            return response(['message' => "شما نمی توانید اطلاعات مدیر را تغییر دهید."], 403);
        }
        if ($request->user()->isEmployee() && $user->isAuthor()) {
            return response(['message' => "شما نمی تواند اطلاعات نویسندگان را ویرایش کنید."], 403);
        }
        $user->delete();
        return response()->noContent();
    }

    public function create(Request $request)
    {
        $this->validateUser($request, new User());
        $request->validate(['password' => ['required', 'confirmed', Rules\Password::defaults()]]);

        if ($request->national_code && !validate_national_code($request->national_code)) {
            return response(['message' => "کد ملی معتبر نمی باشد."], 422);
        }

        $input = $request->only([
            'first_name',
            'last_name',
            'gender',
            'phone',
            'email',
            'address',
            'city',
            'state',
            'postal_code',
            'national_code',
            'password',
            'user_category_id',
            'organ_id',
        ]);
        $input['name'] = $request->get('first_name') . ' ' . $request->get('last_name');
        $input['password'] = Hash::make($input['password']);
        $input['code'] = RegisteredUserController::makeUserCode();
        $user = User::create($input);

        return response($user, 201);
    }

    public function managerUserCreate(Request $request)
    {
        $this->validateUser($request, new User());
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', Rule::enum(UserType::class)]
        ]);

        if ($request->national_code && !validate_national_code($request->national_code)) {
            return response(['message' => "کد ملی معتبر نمی باشد."], 422);
        }

        $input = $request->only([
            'first_name',
            'last_name',
            'phone',
            'email',
            'gender',
            'address',
            'city',
            'state',
            'postal_code',
            'national_code',
            'password',
            'user_category_id',
            'organ_id',
        ]);
        $input['password'] = Hash::make($input['password']);
        $input['code'] = RegisteredUserController::makeUserCode();
        $input['role'] = match ($request->get('role')) {
            'manager' => UserType::Manager,
            'employee' => UserType::Employee,
            'author' => UserType::Author,
            'organ' => UserType::Organ,
            default => UserType::User
        };
        $input['name'] = $request->get('first_name') . ' ' . $request->get('last_name');

        $user = User::create($input);

        return response($user, 201);
    }

    public function changeAccess(Request $request, User $user)
    {
        $access = match ($request->query('access')) {
            'manager' => UserType::Manager,
            'employee' => UserType::Employee,
            'author' => UserType::Author,
            'organ' => UserType::Organ,
            default => UserType::User
        };

        if ($access) {
            $user->update(['role' => $access]);
        }

        return response($user, 200);
    }

    private function validateUser(Request $request, User $user)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255', 'regex:/^(\+98|0)?9\d{9}$/', Rule::unique('users', 'phone')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'national_code' => ['nullable', 'string', 'max:10'],
            'user_category_id' => ['nullable', 'integer', 'exists:user_categories,id'],
            'organ_id' => ['nullable', 'integer', 'exists:organs,id'],
            'gender' => ['required', Rule::enum(Gender::class)],
        ]);
    }
}
