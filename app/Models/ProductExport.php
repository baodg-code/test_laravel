<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductExport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'format',
        'file_name',
        'file_path',
        'error_message',
        'finished_at',
    ];

    protected $casts = [
        'finished_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
