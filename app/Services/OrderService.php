<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {
    }

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // TODO: Complete this method
        $merchant = Merchant::where('domain', $data['merchant_domain'])->first();

        if (!$merchant) {
            throw new ModelNotFoundException("Merchant with domain {$data['merchant_domain']} not found.");
        }

        $existingOrder = Order::where('external_order_id', $data['order_id'])->first();
        if ($existingOrder) {
            return;
        }

        $affiliate = Affiliate::whereHas('user', function ($query) use ($data) {
            $query->where('email', $data['customer_email']);
        })->first();


        if (!$affiliate) {
            $affiliate = $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name'], 0.1);
        }
        Log::debug("Affiliate ID: {$merchant}, Commission Rate: {$affiliate->commission_rate}");

        $commissionOwed = $data['subtotal_price'] * $affiliate->commission_rate;

        Order::create([
            'merchant_id' => $merchant->id,
            'affiliate_id' => $affiliate->id,
            'subtotal' => $data['subtotal_price'],
            'commission_owed' => $commissionOwed,
            'external_order_id' => $data['order_id'],
        ]);
    }
}
