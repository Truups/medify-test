<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'kode',
        'nama'
    ];

    /**
     * Master item yang memiliki kategori ini (many to many).
     */
    public function items()
    {
        return $this->belongsToMany(
            MasterItem::class,
            'category_item_master_item',
            'category_item_id',
            'master_item_id'
        )->withTimestamps();
    }
}
