<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Notification extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function notifyAdmins(string $title, string $message, string $type, ?string $referenceType = null, ?int $referenceId = null)
    {
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            self::create([
                'user_id' => $admin->id,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'is_read' => false,
            ]);
        }
    }

    /**
     * Send System Notification email with token replacement.
     */
    public static function sendSystemMail($user, string $templateKey, array $tokens = [])
    {
        try {
            if ($user && $user->email) {
                if (!isset($tokens['user_id']) && isset($user->id)) {
                    $tokens['user_id'] = $user->id;
                }
                \Illuminate\Support\Facades\Mail::to($user->email)->send(
                    new \App\Mail\SystemNotificationMail($templateKey, $tokens)
                );
                Log::info("System mail '{$templateKey}' sent to {$user->email}");
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send system mail to " . ($user->email ?? 'N/A') . ": " . $e->getMessage());
        }
    }
}