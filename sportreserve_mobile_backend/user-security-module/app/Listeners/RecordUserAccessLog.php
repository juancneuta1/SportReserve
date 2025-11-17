<?php

namespace App\Listeners;

use App\Events\UserLoginSucceeded;
use App\Mail\UserLoginNotification;
use App\Mail\UserSuspiciousLoginNotification;
use App\Models\UserAccessLog;
use App\Models\UserMobileSession;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RecordUserAccessLog
{
    public function handle(UserLoginSucceeded $event): void
    {
        $user = $event->user;
        $request = $event->request;

        $ip = $request->ip();
        $userAgent = (string) $request->userAgent();
        $device = $this->parseDevice($userAgent);
        $location = $this->resolveLocation($ip);

        $lastLog = $user->mobileAccessLogs()->latest('logged_in_at')->first();

        UserAccessLog::create([
            'user_id' => $user->id,
            'ip' => $ip,
            'location' => $location,
            'device' => $device,
            'user_agent' => $userAgent,
            'logged_in_at' => now(),
        ]);

        $sessionId = $request->header('X-Session-Id') ?? optional($request->session())->getId() ?? (string) Str::uuid();

        UserMobileSession::updateOrCreate(
            ['session_id' => $sessionId],
            [
                'user_id' => $user->id,
                'ip' => $ip,
                'device' => $device,
                'user_agent' => $userAgent,
                'last_activity_at' => now(),
            ]
        );

        Mail::to($user->email)->send(
            new UserLoginNotification($user, $ip, $location, $device, $userAgent, now())
        );

        if ($lastLog && $lastLog->ip !== $ip) {
            Mail::to($user->email)->send(
                new UserSuspiciousLoginNotification($user, $ip, $location, $device)
            );
        }
    }

    private function parseDevice(?string $userAgent): string
    {
        if (! $userAgent) {
            return 'Desconocido';
        }

        $segments = array_filter(explode(' ', $userAgent));

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

                return collect([
                    $data['city'] ?? null,
                    $data['regionName'] ?? null,
                    $data['country'] ?? null,
                ])->filter()->implode(', ');
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        return null;
    }
}
