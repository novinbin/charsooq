<?php

namespace App\Helpers\DashtAPI;

use App\Models\Config;
use Illuminate\Support\Facades\Http;

class Dasht
{
    private const BASE_URL = "5.202.27.41:8080/api";
    private $token;
    private $headers = [
        "Content-Type" => "application/json",
        "Accept" => "application/json",
        "GenerationVersion" => 102
    ];

    public function __construct()
    {
        $config = Config::where('key', 'dasht_access_token')->get()->first();
        if (!$config) {
            $config = Config::create(['key' => 'dasht_access_token', 'value' => collect(['token' => ''])]);
        }
        $this->token = $config->value->get('token');
        $this->headers['Authorization'] = 'Bearer ' . $this->token;
    }

    public function login($guid)
    {
        $response = Http::withHeaders($this->headers)->post(self::BASE_URL . '/users/login', [
            'Guid' => $guid,
        ]);
        if ($response->successful()) {
            $config = Config::where('key', 'dasht_access_token')->get()->first();
            $config->value->put('token', $response->json('AccessToken'));
            $config->save();
            $refresh = Config::where('key', 'dasht_refresh_token')->get()->first();
            if (!$refresh) {
                $refresh = Config::create(['key' => 'dasht_refresh_token', 'value' => collect(['token' => ''])]);
            }
            $refresh->value->put('token', $response->json('RefreshToken'));
            $refresh->save();
        } else {
            throw new \Exception($response->body());
        }
    }

    private function refreshToken()
    {
        $config = Config::where('key', 'dasht_refresh_token')->get()->first();
        if (!$config) {
            $config = Config::create(['key' => 'dasht_refresh_token', 'value' => collect(['token' => ''])]);
        }

        $response = Http::withHeaders($this->headers)
            ->post(self::BASE_URL.'/Users/Refresh', [
                'RefreshToken' => $config->value->get('token'),
            ]);

        if ($response->successful()) {
            $access_token = Config::where('key', 'dasht_access_token')->get()->first();
            $access_token->value->put('token', $response->json("AccessToken"));
            $access_token->save();
            $this->token = $access_token->value->get('token');
            $config->value->put('token', $response->json('RefreshToken'));
            $config->save();
            $this->headers['Authorization'] = 'Bearer ' . $this->token;
        } else {
            throw new \Exception($response->body());
        }
    }

//    public function getInvoices0()
//    {
//        $response = Http::withHeaders($this->headers)->get(self::BASE_URL . '/SaleInvoices?' . http_build_query(['limit' => 5]));
//        if ($response->successful()) {
//            return $response->json();
//        } else {
//            if ($response->json('type') == 1 && $response->status() == 403) {
//                try {
//                    $this->refreshToken();
//                    $response = Http::withHeaders($this->headers)->get(self::BASE_URL.'/SaleInvoices');
//                    if ($response->successful()) {
//                        return $response->json();
//                    } else {
//                        throw new \Exception($response->body());
//                    }
//                } catch (\Exception $e) {
//                    throw new \Exception($e->getMessage());
//                }
//            } else {
//                throw new \Exception($response->body());
//            }
//        }
//    }

    public function getProducts($limit = 10, $offset = 0, $orderBy = "Title")
    {
        $query = http_build_query([
            'limit' => $limit,
            'offset' => $offset,
            'orderBy' => $orderBy,
        ]);
        $response = Http::withHeaders($this->headers)->get(self::BASE_URL . '/items?' . $query);
        if ($response->successful()) {
            return $response->json();
        } else {
            try {
                $this->refreshToken();
                $response = Http::withHeaders($this->headers)->get(self::BASE_URL.'/Items?' . $query);
                if ($response->successful()) {
                    return $response->json();
                } else {
                    throw new \Exception($response->body());
                }
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
    }

    public function getProduct($productID)
    {
        $response = Http::withHeaders($this->headers)->get(self::BASE_URL . '/Items/' . $productID);
        if ($response->successful()) {
            return $response->json();
        } else {
            try {
                $this->refreshToken();
                $response = Http::withHeaders($this->headers)->get(self::BASE_URL . '/Items/' . $productID);
                if ($response->successful()) {
                    return $response->json();
                } else {
                    throw new \Exception($response->body());
                }
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
    }

    public function getCustomer($customerID)
    {
        $response = Http::withHeaders($this->headers)->get(self::BASE_URL . '/Customers/' . $customerID);
        if ($response->successful()) {
            return $response->json();
        } else {
            try {
                $this->refreshToken();
                $response = Http::withHeaders($this->headers)->get(self::BASE_URL . '/Customers/' . $customerID);
                if ($response->successful()) {
                    return $response->json();
                } else {
                    throw new \Exception($response->body());
                }
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
    }

    public function getInvoices($queries = [])
    {
        $queries['orderByDesc'] = 'Date';

//      overriding the perpage amount. it must be removed.
        $queries['limit'] = 100;
        
        
        $query = http_build_query($queries);
        $response = Http::withHeaders($this->headers)->get(self::BASE_URL . '/SaleInvoices?' . $query);
        if ($response->successful()) {
            return $response->json();
        } else {
            try {
                $this->refreshToken();
                $response = Http::withHeaders($this->headers)->get(self::BASE_URL . '/SaleInvoices?' . $query);
                if ($response->successful()) {
                    return $response->json();
                } else {
                    throw new \Exception($response->body());
                }
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
    }

    public function getInvoice($invoiceID)
    {
        $response = Http::withHeaders($this->headers)->get(self::BASE_URL . '/SaleInvoices/' . $invoiceID);
        if ($response->successful()) {
            return $response->json();
        } else {
            try {
                $this->refreshToken();
                $response = Http::withHeaders($this->headers)->get(self::BASE_URL . '/SaleInvoices/' . $invoiceID);
                if ($response->successful()) {
                    return $response->json();
                } else {
                    throw new \Exception($response->body());
                }
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
    }
}
