<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Admin\Admin;
use App\Models\TransactionCharge;
use App\Constants\PaymentGatewayConst;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminApiController extends Controller
{
    /**
     * Get dashboard statistics
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        // Today's date range
        $todayStart = Carbon::today();
        $todayEnd = Carbon::tomorrow();

        // Today's transaction amount
        $todayAmount = Transaction::where('status', 1)
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->sum('request_amount');

        // Today's orders count
        $orders = Transaction::whereBetween('created_at', [$todayStart, $todayEnd])
            ->count();

        // New users today
        $newUsers = User::whereBetween('created_at', [$todayStart, $todayEnd])
            ->count();

        // Refunds count (transactions with status 4 - rejected/refunded)
        $refunds = Transaction::where('status', 4)
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->count();

        return response()->json([
            'todayAmount' => floatval($todayAmount),
            'orders' => intval($orders),
            'newUsers' => intval($newUsers),
            'refunds' => intval($refunds)
        ]);
    }

    /**
     * Get transactions list
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function transactions(Request $request)
    {
        $query = Transaction::with(['user', 'creator_wallet'])
            ->latest();

        // Apply filters if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $transactions = $query->take(100)->get();

        $data = $transactions->map(function ($transaction) {
            $statusMap = [
                0 => '待处理',
                1 => '成功',
                2 => '待支付',
                3 => '处理中',
                4 => '已退款'
            ];

            return [
                'orderId' => $transaction->trx_id ?? 'N/A',
                'user' => $transaction->user->username ?? 'N/A',
                'amount' => floatval($transaction->request_amount),
                'status' => $statusMap[$transaction->status] ?? '未知',
                'time' => $transaction->created_at->format('Y-m-d H:i:s')
            ];
        });

        return response()->json($data);
    }

    /**
     * Get users list
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function users(Request $request)
    {
        $query = User::latest();

        // Apply filters if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->take(100)->get();

        $data = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->username ?? $user->firstname . ' ' . $user->lastname,
                'email' => $user->email,
                'role' => 'user', // All users from User model are users
                'status' => $user->status == 1 ? 'active' : 'inactive',
                'created_at' => $user->created_at->format('Y-m-d H:i:s')
            ];
        });

        return response()->json($data);
    }

    /**
     * Get or update settings
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function settings(Request $request)
    {
        if ($request->isMethod('get')) {
            // Get settings
            $settings = [
                'siteName' => config('app.name'),
                'callbackUrl' => config('app.url') . '/api/callback'
            ];

            return response()->json($settings);
        }

        if ($request->isMethod('post')) {
            // Update settings
            $validated = $request->validate([
                'siteName' => 'nullable|string|max:255',
                'callbackUrl' => 'nullable|url'
            ]);

            // Note: This is a basic implementation that returns success without persisting.
            // In production, you should save these settings to a database table
            // or update the .env file using a proper settings management system.
            // For now, this endpoint accepts the data and validates it but does not persist it.
            return response()->json([
                'success' => true,
                'message' => '设置已保存 (请注意: 当前实现不持久化设置)'
            ]);
        }
    }
}
