<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sparepart extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name_sparepart',
        'minimal_stok',
        'stok'
    ];
}