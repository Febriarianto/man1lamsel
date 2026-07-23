<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'type'];

    public static function value(string $key, mixed $default = null): mixed
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function mediaUrl(?string $path, ?string $default = null): ?string
    {
        if (! $path) {
            return $default;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (str_starts_with($path, 'images/') || str_starts_with($path, 'assets/')) {
            return asset($path);
        }

        return Storage::url($path);
    }

    public static function normalizeHex(?string $value, string $default = '#0877C9'): string
    {
        $value = strtoupper(trim((string) $value));

        if (preg_match('/^#[0-9A-F]{6}$/', $value)) {
            return $value;
        }

        if (preg_match('/^#[0-9A-F]{3}$/', $value)) {
            return '#'.implode('', array_map(static fn (string $char): string => $char.$char, str_split(substr($value, 1))));
        }

        return strtoupper($default);
    }

    public static function hexToRgb(?string $value, string $default = '#0877C9'): string
    {
        $hex = ltrim(static::normalizeHex($value, $default), '#');

        return implode(', ', [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ]);
    }

    public static function contrastColor(?string $value, string $light = '#FFFFFF', string $dark = '#111827'): string
    {
        $hex = ltrim(static::normalizeHex($value), '#');
        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));
        $luminance = (($red * 299) + ($green * 587) + ($blue * 114)) / 1000;

        return $luminance >= 150 ? $dark : $light;
    }
}
