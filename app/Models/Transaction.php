<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_transaksi',
        'nama_pemohon',
        'id_user',
        'status', // 'pending', 'approved', 'rejected'
    ];

    // Relationship with user
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // Relationship with detail transactions
    public function details()
    {
        return $this->hasMany(DetailTransaction::class, 'id_transaksi');
    }
}