<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        // Validate the input data
        $validator = Validator::make($data, [
            'domain' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'api_key' => ['required', 'string', 'unique:users,password'],
        ]);

        // Validate domain
        if (!filter_var($data['domain'], FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid domain URL');
        }

        // Throw validation exception if validation fails
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Hash the API key
        $hashedApiKey = bcrypt($data['api_key']);

        // Create a new user record in the database using Eloquent
        $user = new User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = $hashedApiKey;
        $user->type = User::TYPE_MERCHANT;
        $user->save();

        // Create a new merchant record in the database using the same Eloquent instance
        $merchant = new Merchant;
        $merchant->user_id = $user->id;
        $merchant->display_name = $data['name'];
        $merchant->domain = $data['domain'];
        $merchant->save();

        // Return the newly created merchant record
        return $merchant;
    }


    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, Merchant $merchant, array $data)
    {
           // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address');
        }

        // Validate API key
        if (empty($data['api_key'])) {
            throw new InvalidArgumentException('API key cannot be empty');
        }

        // Validate domain
        if (!filter_var($data['domain'], FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid domain URL');
        }

        // Update user details
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->api_key = $data['api_key'];
        $user->domain = $data['domain'];

        $user->save();


        $merchant->user_id = $user->id;
        $merchant->display_name = $data['name'];
        $merchant->domain = $data['domain'];

        $merchant->save();

    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
            // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $user = User::where('email',$email)->first();

        return $user ? $user->merchant : null;

    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        $unpaidOrders = $affiliate->orders()->where('paid', false)->get();
        
        foreach ($unpaidOrders as $order) {
            dispatch(new PayoutOrderJob($order));
        }
    }
}
