<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public const DEFAULTS = [
        'amount_per_point' => 90,
        'points_per_reduction' => 10,
        'reduction_value' => 1000,
        'points_validity_months' => 12,
        'referral_reward_points' => 5,
        'opening_time' => '08:00',
        'closing_time' => '20:00',
        'policies' => '',
    ];
}