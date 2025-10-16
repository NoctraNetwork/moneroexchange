<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MoneroRpcService
{
    private Client $client;
    private string $daemonUrl;
    private string $walletUrl;
    private array $auth;

    public function __construct()
    {
        $this->daemonUrl = config('monero.daemon_url');
        $this->walletUrl = config('monero.wallet_rpc_url');
        
        $this->auth = [
            config('monero.wallet_rpc_user'),
            config('monero.wallet_rpc_pass')
        ];

        $this->client = new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);
    }

    /**
     * Set daemon URL
     */
    public function setDaemon(string $url): void
    {
        $this->daemonUrl = $url;
    }

    /**
     * Get daemon height
     */
    public function getDaemonHeight(): ?int
    {
        try {
            $response = $this->makeRequest('get_height', [], $this->daemonUrl);
            return $response['height'] ?? null;
        } catch (\Exception $e) {
            Log::error('Failed to get daemon height', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get wallet height
     */
    public function getWalletHeight(): ?int
    {
        try {
            $response = $this->makeRequest('get_height', [], $this->walletUrl);
            return $response['height'] ?? null;
        } catch (\Exception $e) {
            Log::error('Failed to get wallet height', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get wallet balance
     */
    public function getBalance(): ?array
    {
        try {
            $response = $this->makeRequest('get_balance', [], $this->walletUrl);
            return [
                'balance' => $response['balance'] ?? 0,
                'unlocked_balance' => $response['unlocked_balance'] ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get wallet balance', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get wallet address
     */
    public function getAddress(): ?string
    {
        try {
            $response = $this->makeRequest('get_address', [], $this->walletUrl);
            return $response['address'] ?? null;
        } catch (\Exception $e) {
            Log::error('Failed to get wallet address', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Create subaddress
     */
    public function createSubaddress(int $accountIndex = 0): ?array
    {
        try {
            $response = $this->makeRequest('create_address', [
                'account_index' => $accountIndex,
            ], $this->walletUrl);
            
            return [
                'address' => $response['address'] ?? null,
                'address_index' => $response['address_index'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create subaddress', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get transfers
     */
    public function getTransfers(array $options = []): ?array
    {
        try {
            $response = $this->makeRequest('get_transfers', $options, $this->walletUrl);
            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to get transfers', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Transfer XMR
     */
    public function transfer(array $destinations, int $priority = 1): ?array
    {
        try {
            $response = $this->makeRequest('transfer', [
                'destinations' => $destinations,
                'priority' => $priority,
                'get_tx_key' => true,
                'get_tx_hex' => true,
            ], $this->walletUrl);
            
            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to transfer XMR', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Sweep single (transfer all from specific address)
     */
    public function sweepSingle(string $address, int $priority = 1): ?array
    {
        try {
            $response = $this->makeRequest('sweep_single', [
                'address' => $address,
                'priority' => $priority,
                'get_tx_key' => true,
                'get_tx_hex' => true,
            ], $this->walletUrl);
            
            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to sweep single', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Store wallet
     */
    public function store(): bool
    {
        try {
            $this->makeRequest('store', [], $this->walletUrl);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to store wallet', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get transaction details
     */
    public function getTransaction(string $txHash): ?array
    {
        try {
            $response = $this->makeRequest('get_transfer_by_txid', [
                'txid' => $txHash,
            ], $this->walletUrl);
            
            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to get transaction', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Check if daemon is synced
     */
    public function isDaemonSynced(): bool
    {
        $daemonHeight = $this->getDaemonHeight();
        $walletHeight = $this->getWalletHeight();
        
        if (!$daemonHeight || !$walletHeight) {
            return false;
        }
        
        // Consider synced if within 3 blocks
        return abs($daemonHeight - $walletHeight) <= 3;
    }

    /**
     * Get sync status
     */
    public function getSyncStatus(): array
    {
        $daemonHeight = $this->getDaemonHeight();
        $walletHeight = $this->getWalletHeight();
        
        return [
            'daemon_height' => $daemonHeight,
            'wallet_height' => $walletHeight,
            'is_synced' => $this->isDaemonSynced(),
            'blocks_behind' => $daemonHeight ? ($daemonHeight - $walletHeight) : null,
        ];
    }

    /**
     * Make RPC request
     */
    private function makeRequest(string $method, array $params = [], ?string $url = null): array
    {
        $url = $url ?? $this->walletUrl;
        
        $payload = [
            'jsonrpc' => '2.0',
            'id' => '0',
            'method' => $method,
            'params' => $params,
        ];

        try {
            $response = $this->client->post($url, [
                'json' => $payload,
                'auth' => $this->auth,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if (isset($data['error'])) {
                throw new \Exception('RPC Error: ' . $data['error']['message']);
            }
            
            return $data['result'] ?? [];
        } catch (RequestException $e) {
            Log::error('Monero RPC request failed', [
                'method' => $method,
                'error' => $e->getMessage(),
                'response' => $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null,
            ]);
            throw $e;
        }
    }

    /**
     * Get cached daemon height
     */
    public function getCachedDaemonHeight(): ?int
    {
        return Cache::remember('monero_daemon_height', 30, function () {
            return $this->getDaemonHeight();
        });
    }

    /**
     * Get cached wallet height
     */
    public function getCachedWalletHeight(): ?int
    {
        return Cache::remember('monero_wallet_height', 30, function () {
            return $this->getWalletHeight();
        });
    }
}

