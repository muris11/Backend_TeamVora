<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSetting extends Model
{
    protected $fillable = ['logo_url', 'primary_color', 'footer_text'];
}
