<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficialReport extends Model
{
    protected $fillable = ['title', 'division', 'frequency', 'starts_on', 'ends_on', 'content'];

    protected function casts(): array
    {
        return ['content' => 'array', 'starts_on' => 'date', 'ends_on' => 'date'];
    }
}
