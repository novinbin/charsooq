<?php

namespace App\Http\Controllers;

use App\Http\Resources\DelayedFactorResource;
use App\Http\Resources\FactorResource;
use App\Models\Factor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class FactorController extends Controller
{
    public function getUserFactors(Request $request, User $user)
    {
        return FactorResource::collection($user->factors()->latest()->paginate($request->query('per_page', 10)));
    }

    public function getUserDelayedFactors(Request $request, User $user)
    {
        return DelayedFactorResource::collection($user->factors()->latest()->paginate($request->query('per_page', 10)));
    }

    public function getFactor(Factor $factor)
    {
        return new FactorResource($factor);
    }

    public function create(Request $request, User $user)
    {
        $request->validate([
            'description' => ['nullable', 'string', 'max:255'],
            'products' => ['nullable', 'json'],
            'discount' => ['nullable', 'integer'],
            'dasht_invoice_id' => ['nullable' , 'numeric'],
            'date' => ['required', 'date']
        ]);

        $dasht_products = [];
        $dasht_info = collect();
        if ($request->has('dasht_invoice_id')) {
            try {
                $dasht_info = dasht()->getInvoice($request->dasht_invoice_id);
                foreach ($dasht_info['Items'] as $product) {
                    $dasht_products[] = dasht()->getProduct($product['ItemRef']);
                }
            } catch (\Exception $e) {
                return response(['message' => $e->getMessage()], 422);
            }
        }

        $factor = Factor::create([
            'description' => $request->description,
            'total_price' => 0,
            'products' => collect(json_decode($request->products, true)),
            'user_id' => $user->id,
            'discount' => $request->get('discount', 0),
            'final_price' => 0,
            'dasht_info' => collect($dasht_info),
            'date' => $request->date
        ]);

        $total_price = 0;
        $items = [];
        foreach ($dasht_products as $key => $product) {
            $items[] = [
                'dasht_info' => collect($product),
                'title' => $product['Title'],
                'code' => $product['Code'],
                'price' => $dasht_info['Items'][$key]['Price'] / 10,
                'count' => $dasht_info['Items'][$key]['Quantity'],
                'total' => $dasht_info['Items'][$key]['Price'] * $dasht_info['Items'][$key]['Quantity'] / 10,
            ];
            $total_price += $dasht_info['Items'][$key]['Price'] * $dasht_info['Items'][$key]['Quantity'] / 10;
        }
        $factor->items()->createMany($items);
        $factor->total_price = $total_price;
        $factor->save();

        return response(new FactorResource($factor), 201);
    }

    public function update(Request $request, Factor $factor)
    {
        $request->validate([
            'description' => ['nullable', 'string', 'max:255'],
            'products' => ['nullable', 'json'],
            'discount' => ['nullable', 'integer'],
            'dasht_invoice_id' => ['nullable' , 'numeric'],
            'date' => ['required', 'date']
        ]);

        $dasht_info = collect();
        if ($request->has('dasht_invoice_id')) {
            try {
                $dasht_info = collect(dasht()->getInvoice($request->dasht_invoice_id));
            } catch (\Exception $e) {
                return response(['message' => $e->getMessage()], 422);
            }
        }

        $factor->update([
            'description' => $request->description,
            'products' => collect(json_decode($request->products, true)),
            'discount' => $request->get('discount', 0),
            'dasht_info' => $dasht_info,
            'date' => $request->date
        ]);

        FactorItemController::calculateFactor($factor->id);

        return new FactorResource($factor);
    }

    public function delete(Factor $factor)
    {
        if ($factor->installments->isNotEmpty()) {
            return response(['message' => 'برای این فاکتور اقساط تعریف شده است به همین دلیل نمی توانید آنرا حذف کنید.'], 403);
        }

        $factor->delete();
        return response(null, 204);
    }

    public function getMyFactors(Request $request)
    {
        return FactorResource::collection($request
            ->user()
            ->factors()
            ->latest()
            ->paginate($request->query('per_page', 10)));
    }

    public function foreignFactor(Request $request)
    {
        $request->validate([
            'dasht_invoice_id' => ['required', 'numeric'],
        ]);
        try {
            $dasht_factor = dasht()->getInvoice($request->dasht_invoice_id);
            $dasht_customer = dasht()->getCustomer($dasht_factor['CustomerRef']);
            $dasht_products = [];
            foreach ($dasht_factor['Items'] as $product) {
                $dasht_products[] = dasht()->getProduct($product['ItemRef']);
            }
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], 400);
        }
        $user_name =  $dasht_customer['Name'] . ' ' . $dasht_customer['LastName'];
        $user_phone = trim($dasht_customer['PhoneNumber'], ' \n\r\t\v@');
        if (str_contains($user_phone, '@')) {
            $user_phone = trim((string)(explode('@', $user_phone)[0]), ' \n\r\t\v@');
        }
        if (!str_starts_with($user_phone, '0')) {
            $user_phone = '0' . $user_phone;
        }
        $user = User::where('name', $user_name)->where('phone', $user_phone)->first();
        if (!$user) {
            $user = User::create([
                'name' => $user_name,
                'first_name' =>  $dasht_customer['Name'],
                'last_name' => $dasht_customer['LastName'],
                'phone' => $user_phone,
                'national_code' => $dasht_customer['NationalID'],
                // 'address' => $dasht_customer['Addresses'][0]['Address'],
                // 'postal_code' => $dasht_customer['Addresses'][0]['ZipCode'],
                'code' => User::generateCode(),
                'gender' => null, //TODO bruh todo!
                'password' => Hash::make($user_phone)
            ]);
        }
        $factor = $user->factors()->create([
            'total_price' => $dasht_factor['TotalPrice'] / 10,
            'final_price' => $dasht_factor['TotalPrice'] / 10,
            'remaining' => $dasht_factor['RemainingPrice'] / 10,
            'dasht_info' => collect($dasht_factor),
        ]);
        $items = [];
        foreach ($dasht_products as $key => $product) {
            $items[] = [
                'dasht_info' => collect($product),
                'title' => $product['Title'],
                'code' => $product['Code'],
                'price' => $dasht_factor['Items'][$key]['Price'] / 10,
                'count' => $dasht_factor['Items'][$key]['Quantity'],
                'total' => $dasht_factor['Items'][$key]['Price'] * $dasht_factor['Items'][$key]['Quantity'] / 10,
            ];
        }
        $factor->items()->createMany($items);

        return response(new FactorResource($factor), 201);
    }
}
