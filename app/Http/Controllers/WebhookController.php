<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Pass the necessary data to the process order method
     * 
     * @param  Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Extract the necessary data from the request
        $data = $request->validate([
            'order_id' => 'required|string',
            'merchant_domain' => 'required',
            'customer_email' => 'required|email',
            'discount_code' => 'nullable',
            'subtotal_price' =>'nullable'
        ]);
        
        $result = $this->orderService->processOrder($data);

        return response()->json(['success' => true, 'data' => $result]);
    }
}
