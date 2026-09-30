<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsSetting extends Model
{
    protected $fillable = [
        'is_active',
        'gateway_provider',
        'api_key',
        'sender_id',
        'custom_api_url',
        'entry_message_template',
        'exit_message_template',
    ];
}
