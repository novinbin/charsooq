<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function getAllCodes(Request $request)
    {
        return Discount::orderBy('created_at', 'desc')->paginate($request->query('per_page', 10));
    }

    public function getCustomerDiscounts(Request $request)
    {
        return Discount::where('user_id', '!=', null)->orderBy('created_at', 'desc')->paginate($request->query('per_page', 10));
    }

    public function getProductDiscounts(Request $request)
    {
        return Discount::where('user_id', null)->orderBy('created_at', 'desc')->paginate($request->query('per_page', 10));
    }

    public function read(Discount $discount)
    {
        return $discount;
    }

    public function create(Request $request)
    {
        $request->validate([
            'user_id' => ['nullable', 'integer' ,'exists:users,id'],
            'expiration' => ['nullable', 'date'],
            'discount_rate' => ['required', 'integer', 'min:0', 'max:100'],
            'max_discount' => ['nullable', 'integer'],
            'type' => ['required', 'string'],
            'amount' => ['nullable', 'integer', 'min:0'],
        ]);

        $discount = Discount::create([
            'user_id' => $request->user_id,
            'expiration' => $request->expiration,
            'discount_rate' => $request->discount_rate,
            'max_discount' => $request->max_discount,
            'code' => Discount::generateCode(),
            'type' => $request->type,
            'amount' => $request->amount,
        ]);

        return response($discount, 201);
    }

    public function delete(Discount $discount)
    {
        $discount->delete();
        return response()->noContent();
    }

    public function sendSms()
    {
        //TODO : Notify the user by sms.
    }
}
