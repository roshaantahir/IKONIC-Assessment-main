<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        

        $from = $request->input('from');
        $to = $request->input('to');

        $orders = Order::whereBetween('created_at', [$from, $to])->get();
        
       
        $count = $orders->count();
        $commission_owed = $orders->filter(function ($order) {
            return $order->affiliate_id && $order->commission_paid == 0;
        })->sum('commission_amount');
        $revenue = $orders->sum('subtotal');
        
        
        return response()->json([
            'count' => $count,
            'commission_owed' => $commission_owed,
            'revenue' => $revenue,
        ]);
    }
}
