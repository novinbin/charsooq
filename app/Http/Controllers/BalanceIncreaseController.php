<?php

namespace App\Http\Controllers;

use App\Enums\BalanceRequestStatus;
use App\Http\Resources\BalanceRequestResource;
use App\Models\BalanceIncrease;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BalanceIncreaseController extends Controller
{
    public function approve(Request $request, BalanceIncrease $balance)
    {
        if (!$request->user()->isAdmin()) {
            return response(['message' => "شما نمی توانید این کار را انجام دهید."], 403);
        }

        $balance->update(['status' => BalanceRequestStatus::Approved]);
        $user = $balance->user;
        $user->balance += $balance->amount;
        $user->save();

        return new BalanceRequestResource($balance);
    }

    public function reject(Request $request, BalanceIncrease $balance)
    {
        if (!$request->user()->isAdmin()) {
            return response(['message' => "شما نمی توانید این کار را انجام دهید."], 403);
        }

        $balance->update(['status' => BalanceRequestStatus::Rejected]);

        return response()->noContent();
    }

    public function getPending(Request $request)
    {
        return BalanceRequestResource::collection(
            BalanceIncrease::where('status', 'pending')
                ->paginate($request->query('per_page', 10))
        );
    }

    public function getUserRequestList(Request $request, User $user)
    {
        return BalanceRequestResource::collection(
            BalanceIncrease::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate($request->query('per_page', 10))
        );
    }

    public function create(Request $request, User $user)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'description' => ['nullable', 'string'],
            'check_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $input = [
            'employee_id' => $request->user()->id,
            'user_id' => $user->id,
            'amount' => $request->amount,
            'description' => $request->description,
        ];
        if ($request->hasFile('check_photo')) {
            $input['check_photo'] = $request->file('check_photo')->store('checks', 'public');
        }
        $balance = BalanceIncrease::create($input);

        return response(new BalanceRequestResource($balance), 201);
    }

    public function update(Request $request, BalanceIncrease $balance)
    {
        if ($balance->status == BalanceRequestStatus::Approved) {
            return response(['message' => "شما نمی توانید درخواست تایید شده را ویرایش کنید."], 403);
        }
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'description' => ['nullable', 'string'],
            'check_photo' => ['nullable', 'image', 'mimes:jpeg,png', 'max:2048'],
        ]);

        $input = $request->only(['amount', 'description']);
        $input['employee_id'] = $request->user()->id;
        $input['status'] = BalanceRequestStatus::Pending;
        if ($request->hasFile('check_photo')) {
            Storage::disk('public')->delete($balance->check_photo);
            $input['check_photo'] = $request->file('check_photo')->store('checks', 'public');
        }
        $balance->update($input);

        return new BalanceRequestResource($balance);
    }

    public function delete (BalanceIncrease $balance)
    {
        if ($balance->status == BalanceRequestStatus::Approved) {
            $user = $balance->user;
            $user->balance -= $balance->amount;
            $user->save();
        }
        $balance->delete();
        return response(null, 204);
    }

    public function getBalance(Request $request, BalanceIncrease $balance)
    {
        return new BalanceRequestResource($balance);
    }
}
