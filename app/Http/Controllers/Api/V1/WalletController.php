<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\WalletDepositRequest;
use App\Models\Discount;
use App\Models\DiscountUse;
use App\Models\User;
use App\Services\Payment\Contracts\PaymentInterface;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|exists:users,phone',
        ]);
        $user = User::where('phone', $request->phone)->first()->load('wallet');
        return response()->json([
            'balance' => $user->wallet->balance,
        ]);
    }

    public function deposit(WalletDepositRequest $request, PaymentInterface $payment)
    {
        $validated = $request->validated();
        $phone = $validated['phone'];
        $user = User::firstOrCreate(['phone' => $phone]);
        $wallet = $user->wallet()->firstOrCreate();
        $discount = Discount::where('code', $request->discount_code)->first();
        if (isset($validated['discount_code'])) {
            if ($discount->isActive() && $discount->checkUseLimitNotFull() && $user->checkUserHasNotLimitDiscount($discount)) {
                $discount->use()->create(['user_id' => $user->id]);
                if ($discount->type === 'gift') {
                    $user->wallet()->update([
                        'balance' => $wallet->balance + $discount->value
                    ]);
                    return $wallet->balance;
                } else {
                    if ($discount->type === 'percentage')
                        $amount = $validated['amount'] - $validated['amount'] * $discount->value / 100;
                    else
                        $amount = $validated['amount'] - $discount->value;
                    $startPayLink = $payment->request($amount, \route('wallet.deposit', [
                        'user_id' => $user->id,
                        'amount' => $amount,
                    ]), __('Wallet deposit request for user:' . $user->phone));
                    return response()->json([
                        'paymentlink' => $startPayLink
                    ]);
                }
            }
            return response()->json([
                'message' => 'Invalid discount code!'
            ]);
        } else {
            $discount->use()->create(['user_id' => $user->id]);
            $amount = $validated['amount'];
            $startPayLink = $payment->request($amount, \route('wallet.deposit', [
                'user_id' => $user->id,
                'amount' => $amount,
            ]), __('Wallet deposit request for user:' . $user->phone));
            return response()->json([
                'paymentlink' => $startPayLink
            ]);
        }
    }

    public function verifyDeposit(Request $request, PaymentInterface $payment, $user_id, $amount)
    {
        $authority = $request->Authority;
        $payment = $payment->verify($amount, $authority);
        $user = User::find($user_id);
        $wallet = $user->wallet;
        $paymentHasNotErrors = empty($payment->errors);
        if ($paymentHasNotErrors && $payment->data->code == 100) {
            $user->wallet->transactions()->create([
                'amount' => $amount,
                'type' => 'deposit',
            ]);
            $wallet->update(['balance' => $wallet->balance + $amount]);
        }
        $status = $paymentHasNotErrors ? strtolower($payment->data->message) : 'error';
        return view('payment.' . $status);
    }

    public function analyze()
    {
        return response()->json([
            DiscountUse::with(['discount', 'user'])->get()
        ]);
    }
}
