<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class PgpService
{
    private string $gnupgPath;
    private string $tempDir;

    public function __construct()
    {
        $this->gnupgPath = config('pgp.gnupg_path', '/usr/bin/gpg');
        $this->tempDir = config('pgp.temp_dir', sys_get_temp_dir());
    }

    /**
     * Verify PGP signature
     */
    public function verifySignature(string $message, string $signature, string $publicKey): bool
    {
        try {
            $tempMessageFile = $this->createTempFile($message);
            $tempSigFile = $this->createTempFile($signature);
            $tempKeyFile = $this->createTempFile($publicKey);

            // Import the public key
            $importResult = Process::run([
                $this->gnupgPath,
                '--import',
                $tempKeyFile
            ]);

            if ($importResult->failed()) {
                Log::error('Failed to import PGP key', ['output' => $importResult->output()]);
                return false;
            }

            // Verify the signature
            $verifyResult = Process::run([
                $this->gnupgPath,
                '--verify',
                $tempSigFile,
                $tempMessageFile
            ]);

            $this->cleanupTempFiles([$tempMessageFile, $tempSigFile, $tempKeyFile]);

            return $verifyResult->successful();
        } catch (\Exception $e) {
            Log::error('PGP verification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Extract fingerprint from public key
     */
    public function extractFingerprint(string $publicKey): ?string
    {
        try {
            $tempKeyFile = $this->createTempFile($publicKey);

            $result = Process::run([
                $this->gnupgPath,
                '--with-fingerprint',
                '--show-keys',
                $tempKeyFile
            ]);

            $this->cleanupTempFiles([$tempKeyFile]);

            if ($result->failed()) {
                return null;
            }

            // Parse fingerprint from output
            $output = $result->output();
            if (preg_match('/Key fingerprint = ([A-F0-9\s]+)/', $output, $matches)) {
                return str_replace(' ', '', $matches[1]);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to extract PGP fingerprint', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Encrypt message with public key
     */
    public function encryptMessage(string $message, string $publicKey): ?string
    {
        try {
            $tempMessageFile = $this->createTempFile($message);
            $tempKeyFile = $this->createTempFile($publicKey);
            $tempOutputFile = $this->createTempFile('');

            // Import the public key
            $importResult = Process::run([
                $this->gnupgPath,
                '--import',
                $tempKeyFile
            ]);

            if ($importResult->failed()) {
                Log::error('Failed to import PGP key for encryption', ['output' => $importResult->output()]);
                return null;
            }

            // Encrypt the message
            $encryptResult = Process::run([
                $this->gnupgPath,
                '--armor',
                '--encrypt',
                '--recipient', $this->extractFingerprint($publicKey),
                '--output', $tempOutputFile,
                $tempMessageFile
            ]);

            if ($encryptResult->failed()) {
                Log::error('Failed to encrypt message', ['output' => $encryptResult->output()]);
                return null;
            }

            $encrypted = file_get_contents($tempOutputFile);
            $this->cleanupTempFiles([$tempMessageFile, $tempKeyFile, $tempOutputFile]);

            return $encrypted;
        } catch (\Exception $e) {
            Log::error('PGP encryption failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Decrypt message with private key
     */
    public function decryptMessage(string $encryptedMessage, string $privateKey, string $passphrase = ''): ?string
    {
        try {
            $tempEncryptedFile = $this->createTempFile($encryptedMessage);
            $tempKeyFile = $this->createTempFile($privateKey);
            $tempOutputFile = $this->createTempFile('');

            // Import the private key
            $importResult = Process::run([
                $this->gnupgPath,
                '--import',
                $tempKeyFile
            ]);

            if ($importResult->failed()) {
                Log::error('Failed to import PGP private key', ['output' => $importResult->output()]);
                return null;
            }

            // Decrypt the message
            $decryptResult = Process::run([
                $this->gnupgPath,
                '--decrypt',
                '--output', $tempOutputFile,
                $tempEncryptedFile
            ], input: $passphrase);

            if ($decryptResult->failed()) {
                Log::error('Failed to decrypt message', ['output' => $decryptResult->output()]);
                return null;
            }

            $decrypted = file_get_contents($tempOutputFile);
            $this->cleanupTempFiles([$tempEncryptedFile, $tempKeyFile, $tempOutputFile]);

            return $decrypted;
        } catch (\Exception $e) {
            Log::error('PGP decryption failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Generate a random token for PGP verification
     */
    public function generateVerificationToken(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Validate PGP public key format
     */
    public function validatePublicKey(string $publicKey): bool
    {
        try {
            $tempKeyFile = $this->createTempFile($publicKey);

            $result = Process::run([
                $this->gnupgPath,
                '--import',
                '--dry-run',
                $tempKeyFile
            ]);

            $this->cleanupTempFiles([$tempKeyFile]);

            return $result->successful();
        } catch (\Exception $e) {
            Log::error('PGP key validation failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Create temporary file
     */
    private function createTempFile(string $content): string
    {
        $tempFile = tempnam($this->tempDir, 'pgp_');
        file_put_contents($tempFile, $content);
        return $tempFile;
    }

    /**
     * Clean up temporary files
     */
    private function cleanupTempFiles(array $files): void
    {
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Check if GnuPG is available
     */
    public function isAvailable(): bool
    {
        try {
            $result = Process::run([$this->gnupgPath, '--version']);
            return $result->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get GnuPG version
     */
    public function getVersion(): ?string
    {
        try {
            $result = Process::run([$this->gnupgPath, '--version']);
            if ($result->successful()) {
                $output = $result->output();
                if (preg_match('/gpg \(GnuPG\) ([0-9.]+)/', $output, $matches)) {
                    return $matches[1];
                }
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}

