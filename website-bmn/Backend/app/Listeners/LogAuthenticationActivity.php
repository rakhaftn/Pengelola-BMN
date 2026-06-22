<?php

namespace App\Listeners;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class LogAuthenticationActivity
{
    public function handleLogin(Login $event): void
    {
        $this->write($event->user->id ?? null, 'login', 'Pengguna login ke sistem');
    }

    public function handleLogout(Logout $event): void
    {
        $this->write($event->user->id ?? null, 'logout', 'Pengguna logout dari sistem');
    }

    private function write(?int $userId, string $event, string $desc): void
    {
        try {
            AuditLog::create([
                'user_id'     => $userId,
                'event'       => $event,
                'description' => $desc,
                'ip_address'  => request()->ip(),
                'user_agent'  => substr((string) request()->userAgent(), 0, 500),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
