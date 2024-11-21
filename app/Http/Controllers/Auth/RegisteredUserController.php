<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response
    {
        return response(['message' => "در حال حاضر، ثبت نام تنها توسط فروشگاه چارسوق امکان پذیر می باشد."], 403);

        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'phone' => ['required', 'string', 'max:255', 'regex:/^(\+98|0)?9\d{9}$/', 'unique:' . User::class],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $code = $this->makeUserCode();

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->first_name . ' ' . $request->last_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'gender' => $request->gender,
            'code' => $code,
            'password' => Hash::make($request->string('password')),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return response()->noContent();
    }

    public static function makeUserCode()
    {
        $code = rand(1000000, 9999999);
        while (User::where('code', $code)->get()->isNotEmpty()) {
            $code = rand(1000000, 9999999);
        }
        return $code;
    }

    public function show(Request $request)
    {
        return $request->user();
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'phone' => ['required', 'string', 'max:255', 'regex:/^(\+98|0)?9\d{9}$/', Rule::unique('users', 'phone')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:255'],
            'national_code' => ['nullable', 'integer', 'digits:10'],
        ]);

        if ($request->national_code && !validate_national_code($request->national_code)) {
            return response(['message' => "کد ملی معتبر نمی باشد."], 422);
        }


        $input = $request->only([
            'first_name',
            'last_name',
            'national_code',
            'gender',
            'phone',
            'email',
            'address',
            'city',
            'state',
            'postal_code'
        ]);
        $input['name'] = $request->first_name . ' ' . $request->last_name;

        $user->update($input);

        return response($user);
    }
}
