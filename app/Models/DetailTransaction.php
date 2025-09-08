<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailTransaction extends Model
{
    use HasFactory;

    protected $table = 'detail_transactions';
    
    protected $fillable = [
        'id_transaksi',
        'id_sparepart',
        'jumlah',
    ];

    // Relationship with transaction
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'id_transaksi');
    }

    // Relationship with sparepart
    public function sparepart()
    {
        return $this->belongsTo(Sparepart::class, 'id_sparepart');
    }
}