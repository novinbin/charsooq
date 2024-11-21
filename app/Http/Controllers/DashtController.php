<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashtController extends Controller
{
    public function login(Request $request)
    {
        $request->validate(['guid' => 'required']);
        try {
            dasht()->login($request->guid);
        } catch (\Exception $exception) {
            return response($exception->getMessage(), 400);
        }
        return response()->noContent();
    }

    public function getProducts(Request $request)
    {
        try {
            $response = dasht()->getProducts(
                $request->query('limit'),
                $request->query('offset'),
                $request->query('orderBy')
            );
            return response($response, 200);
        } catch (\Exception $exception) {
            return response($exception->getMessage(), 400);
        }
    }

    public function getInvoices(Request $request)
    {
        try {
            $response = dasht()->getInvoices($request->query());
            return response($response, 200);
        } catch (\Exception $exception) {
            return response($exception->getMessage(), 400);
        }
    }
}
