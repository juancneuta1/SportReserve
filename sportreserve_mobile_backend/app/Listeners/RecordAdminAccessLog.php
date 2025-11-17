<?php

namespace App\Listeners;

use App\Events\AdminLoginSucceeded;
use App\Mail\AdminLoginNotification;
use App\Mail\SuspiciousLoginNotification;
use App\Models\AccessLog;
use App\Models\UserSession;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class RecordAdminAccessLog
{
    public function handle(AdminLoginSucceeded $event): void
    {
        $user = $event->user;
        $request = $event->request;

        $ip = $request->ip();
        $userAgent = (string) $request->userAgent();
        $device = $this->parseDevice($userAgent);
        $location = $this->resolveLocation($ip);

        $lastLog = $user->accessLogs()->latest('logged_in_at')->first();

        AccessLog::create([
            'user_id' => $user->id,
            'ip' => $ip,
            'location' => $location,
            'device' => $device,
            'user_agent' => $userAgent,
            'logged_in_at' => now(),
        ]);

        UserSession::updateOrCreate(
            [
                'session_id' => session()->getId(),
            ],
            [
                'user_id' => $user->id,
                'ip' => $ip,
                'device' => $device,
                'user_agent' => $userAgent,
                'last_activity_at' => now(),
            ]
        );

        Mail::to($user->email)->send(
            new AdminLoginNotification($user, $ip, $userAgent)
        );

        if ($lastLog && $lastLog->ip !== $ip) {
            Mail::to($user->email)->send(new SuspiciousLoginNotification($user, $ip, $location, $device));
        }
    }

    private function parseDevice(?string $userAgent): string
    {
        if (! $userAgent) {
            return 'Desconocido';
        }

        $segments = explode(' ', $userAgent);

        return implode(' ', array_slice($segments, 0, 4));
    }

    private function resolveLocation(?string $ip): ?string
    {
        if (! $ip) {
            return null;
        }

        try {
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}", [
                'fields' => 'country,regionName,city',
                'lang' => 'es',
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return collect([$data['city'] ?? null, $data['regionName'] ?? null, $data['country'] ?? null])
                    ->filter()
                    ->implode(', ');
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return null;
    }
}
