<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Mithun\PhpZkteco\Libs\ZKTeco;

class ZKTecoService
{
    private string $ip;
    private int $port;
    private string $commKey;
    private ?ZKTeco $zk = null;
    private bool $isSimulated = false;

    public function __construct(string $ip = '192.168.1.201', int $port = 4370, string $commKey = '0')
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->commKey = $commKey;
        $this->isSimulated = (bool) env('ZK_TECO_SIMULATE', false);
    }

    public function connect(): bool
    {
        if ($this->isSimulated) {
            return true;
        }

        try {
            $this->zk = new ZKTeco($this->ip, $this->port);
            return $this->zk->connect();
        } catch (Exception $e) {
            Log::error("ZKTeco Connect Error: " . $e->getMessage());
            return false;
        }
    }

    public function testConnection(): array
    {
        try {
            if ($this->connect()) {
                $info = $this->getDeviceInfo();
                $this->disconnect();
                return [
                    'success' => true,
                    'message' => "Device connected successfully! Device Info: " . ($info['firmware'] ?? 'ZKTeco TCP Device'),
                    'info' => $info,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Unable to connect device at {$this->ip}:{$this->port}. Please verify device power and network settings.",
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Device Connection Error: " . $e->getMessage(),
            ];
        }
    }

    public function getDeviceInfo(): array
    {
        if ($this->isSimulated) {
            return [
                'ip' => $this->ip,
                'port' => $this->port,
                'status' => 'online',
                'firmware' => 'ZKTeco TCP (Simulated Device)',
            ];
        }

        if (!$this->zk && !$this->connect()) {
            return [];
        }

        return [
            'ip' => $this->ip,
            'port' => $this->port,
            'status' => 'online',
            'firmware' => $this->zk->version() ?? 'ZKTeco Device Protocol',
            'serialNumber' => $this->zk->serialNumber() ?? null,
            'deviceName' => $this->zk->deviceName() ?? null,
        ];
    }

    public function getUsers(): array
    {
        if (!$this->zk && !$this->connect()) {
            return [];
        }

        return $this->zk->getUsers() ?? [];
    }

    public function getAttendanceLogs(): array
    {
        if ($this->isSimulated) {
            $now = Carbon::now();
            return [
                [
                    'device_user_id' => '1001',
                    'timestamp' => $now->format('Y-m-d 08:30:00'),
                    'verify_type' => '1',
                    'punch_type' => 'check_in',
                ],
                [
                    'device_user_id' => '1002',
                    'timestamp' => $now->format('Y-m-d 08:35:12'),
                    'verify_type' => '1',
                    'punch_type' => 'check_in',
                ]
            ];
        }

        if (!$this->zk && !$this->connect()) {
            return [];
        }

        $rawLogs = $this->zk->getAttendances();
        $logs = [];

        if (is_array($rawLogs)) {
            foreach ($rawLogs as $log) {
                $punchType = ($log['state'] == 1) ? 'check_out' : 'check_in';
                $logs[] = [
                    'device_user_id' => (string)$log['user_id'],
                    'timestamp' => $log['record_time'],
                    'verify_type' => (string)$log['type'],
                    'punch_type' => $punchType,
                ];
            }
        }

        $this->disconnect();
        return $logs;
    }

    public function clearAttendanceLogs(): bool
    {
        if (!$this->zk && !$this->connect()) {
            return false;
        }

        $this->zk->clearAttendance();
        $this->disconnect();
        return true;
    }

    public function disconnect(): void
    {
        if ($this->zk) {
            $this->zk->disconnect();
            $this->zk = null;
        }
    }
}
