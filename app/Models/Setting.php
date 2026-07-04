<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group'];

    public static function getByGroup(string $group)
    {
        return static::where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }

    public static function setGroup(string $group, array $settings)
    {
        foreach ($settings as $key => $value) {
            static::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group]
            );
        }
    }
}
