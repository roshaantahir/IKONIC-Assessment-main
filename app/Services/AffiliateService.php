<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, float $commissionRate): Affiliate
    {

        if ($commissionRate <= 0) {
            throw new InvalidArgumentException('Commission rate must be a positive number');
        }
        
        $affiliate = new Affiliate;
        $affiliate->user_id = $merchant->user_id;
        $affiliate->merchant_id = $merchant->id;
        $affiliate->comission_rate = $commissionRate;
        
        $affiliate->save();
        
        return $affiliate;
    }
}
