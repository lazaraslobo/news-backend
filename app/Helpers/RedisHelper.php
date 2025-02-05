<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Redis;

class RedisHelper
{
    public static function get($key)
    {
        $value = Redis::get($key);
        return $value ? json_decode($value, true) : null;
    }

    public static function set(string $key, $value = [], $ttl = null)
    {
        $encodedValue = json_encode($value);
        if ($ttl) {
            Redis::setex($key, $ttl, $encodedValue);
        } else {
            Redis::set($key, $encodedValue);
        }
    }

    public static function delete($key)
    {
        return Redis::del($key) > 0;
    }


    public static function update($key, array $newValues)
    {
        $existingValue = self::get($key);
        if ($existingValue && is_array($existingValue)) {
            $updatedValue = array_merge($existingValue, $newValues);
            self::set($key, $updatedValue);
        }
    }
}
