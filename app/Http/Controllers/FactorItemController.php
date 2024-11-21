<?php

namespace App\Http\Controllers;

use App\Models\Factor;
use App\Models\FactorItem;
use Illuminate\Http\Request;

class FactorItemController extends Controller
{
    public function addProduct(Request $request, Factor $factor)
    {
        if ($factor->installments->isNotEmpty()) {
            return response(['message' => 'برای این فاکتور اقساط تعریف شده است به همین دلیل نمی توانید به آن محصول جدید اضافه کنید.'], 403);
        }

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric'],
            'count' => ['required', 'integer', 'min:1'],
        ]);

        $input = $request->only(['title', 'code', 'price', 'count']);
        $input['factor_id'] = $factor->id;
        $input['total'] = $input['count'] * $input['price'];

        $factorItem = FactorItem::create($input);

        self::calculateFactor($factor->id);

        return response($factorItem, 201);
    }

    public function deleteProduct(FactorItem $factorItem)
    {
        if ($factorItem->factor->installments->isNotEmpty()) {
            return response(['message' => 'برای این فاکتور اقساط تعریف شده است به همین دلیل نمی توانید محصولات آنرا حذف کنید.'], 403);
        }

        $id = $factorItem->factor_id;
        $factorItem->delete();
        self::calculateFactor($id);
        return response(null, 204);
    }

    static public function calculateFactor($id)
    {
        $factor = Factor::find($id);

        $total = 0;
        foreach ($factor->items as $item) {
            $total += $item->total;
        }

        $factor->update([
            'total_price' => $total,
            'final_price' => $total - $factor->discount,
        ]);
    }
}
