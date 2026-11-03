<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MasterItem extends Model
{
    use HasFactory;
    use SoftDeletes;


    protected $fillable = [
        'nama',
        'harga_beli',
        'laba',
        'supplier',
        'jenis',
        'foto_barang'
    ];

    /**
     * Kategori yang dimiliki master item ini (many to many).
     */
    public function categories()
    {
        return $this->belongsToMany(
            CategoryItem::class,
            'category_item_master_item',
            'master_item_id',
            'category_item_id'
        )->withTimestamps();
    }

    /**
     * Accessor yang otomatis ikut saat model di-serialize ke array/JSON.
     */
    protected $appends = [
        'foto_barang_url',
        'harga_jual',
    ];

    public function getHargaJualAttribute()
    {
        return (int) round($this->harga_beli + $this->harga_beli * $this->laba / 100);
    }

    public function getFotoBarangUrlAttribute()
    {
        if (!$this->foto_barang) {
            return null;
        }

        // Penjaga untuk data lama yang tersimpan sebagai path temp/absolut.
        if (!Storage::disk('public')->exists($this->foto_barang)) {
            return null;
        }

        return Storage::disk('public')->url($this->foto_barang);
    }
}
