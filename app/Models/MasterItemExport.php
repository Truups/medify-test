<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MasterItemExport extends Model
{
    use HasFactory;

    protected $table = 'master_item_exports';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'status',
        'filters',
        'file_name',
        'file_path',
        'error_message',
        'completed_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'completed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function (MasterItemExport $export) {
            if (!$export->id) {
                $export->id = (string) Str::uuid();
            }
        });
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }
}
