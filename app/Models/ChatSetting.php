<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );
    }

    public static function isWithinOfficeHours(): bool
    {
        if (static::get('chat_hours_enabled') !== '1') {
            return true; // Always open
        }

        $now = now();
        $dayOfWeek = $now->dayOfWeekIso; // 1=Monday, 7=Sunday
        $currentTime = $now->format('H:i');

        $allowedDays = array_map('trim', explode(',', static::get('chat_hours_days', '1,2,3,4,5')));

        if (!in_array((string) $dayOfWeek, $allowedDays)) {
            return false;
        }

        $start = static::get('chat_hours_start', '09:00');
        $end = static::get('chat_hours_end', '18:00');

        return $currentTime >= $start && $currentTime <= $end;
    }
}