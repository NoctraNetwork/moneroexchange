<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Services\MoneroRpcService;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin.protect');
    }

    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function rpcConfig()
    {
        $settings = Setting::whereIn('key', [
            'monero_rpc_host',
            'monero_rpc_port',
            'monero_rpc_username',
            'monero_rpc_password',
            'monero_rpc_ssl',
            'monero_wallet_rpc_host',
            'monero_wallet_rpc_port',
            'monero_wallet_rpc_username',
            'monero_wallet_rpc_password',
            'monero_wallet_rpc_ssl',
        ])->pluck('value', 'key');

        return view('admin.rpc-config', compact('settings'));
    }

    public function updateRpcConfig(Request $request)
    {
        $request->validate([
            'monero_rpc_host' => 'required|string|max:255',
            'monero_rpc_port' => 'required|integer|min:1|max:65535',
            'monero_rpc_username' => 'required|string|max:255',
            'monero_rpc_password' => 'required|string|max:255',
            'monero_rpc_ssl' => 'boolean',
            'monero_wallet_rpc_host' => 'required|string|max:255',
            'monero_wallet_rpc_port' => 'required|integer|min:1|max:65535',
            'monero_wallet_rpc_username' => 'required|string|max:255',
            'monero_wallet_rpc_password' => 'required|string|max:255',
            'monero_wallet_rpc_ssl' => 'boolean',
        ]);

        $settings = [
            'monero_rpc_host',
            'monero_rpc_port',
            'monero_rpc_username',
            'monero_rpc_password',
            'monero_rpc_ssl',
            'monero_wallet_rpc_host',
            'monero_wallet_rpc_port',
            'monero_wallet_rpc_username',
            'monero_wallet_rpc_password',
            'monero_wallet_rpc_ssl',
        ];

        foreach ($settings as $key) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $request->input($key)]
            );
        }

        return redirect()->route('admin.rpc-config')
            ->with('success', 'RPC configuration updated successfully.');
    }

    public function testRpcConnection()
    {
        try {
            $moneroService = app(MoneroRpcService::class);
            $info = $moneroService->getInfo();
            
            return response()->json([
                'success' => true,
                'message' => 'RPC connection successful',
                'data' => $info
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'RPC connection failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function systemSettings()
    {
        $settings = Setting::whereIn('key', [
            'admin_fee_bps',
            'dispute_fee_atomic',
            'min_trade_amount_atomic',
            'max_trade_amount_atomic',
            'trade_timeout_minutes',
            'site_name',
            'site_description',
            'maintenance_mode',
        ])->pluck('value', 'key');

        return view('admin.system-settings', compact('settings'));
    }

    public function updateSystemSettings(Request $request)
    {
        $request->validate([
            'admin_fee_bps' => 'required|integer|min:0|max:10000',
            'dispute_fee_atomic' => 'required|integer|min:0',
            'min_trade_amount_atomic' => 'required|integer|min:1000000000',
            'max_trade_amount_atomic' => 'required|integer|min:1000000000',
            'trade_timeout_minutes' => 'required|integer|min:5|max:1440',
            'site_name' => 'required|string|max:255',
            'site_description' => 'required|string|max:1000',
            'maintenance_mode' => 'boolean',
        ]);

        $settings = [
            'admin_fee_bps',
            'dispute_fee_atomic',
            'min_trade_amount_atomic',
            'max_trade_amount_atomic',
            'trade_timeout_minutes',
            'site_name',
            'site_description',
            'maintenance_mode',
        ];

        foreach ($settings as $key) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $request->input($key)]
            );
        }

        return redirect()->route('admin.system-settings')
            ->with('success', 'System settings updated successfully.');
    }
}

