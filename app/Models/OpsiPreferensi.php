<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpsiPreferensi extends Model
{
    use HasFactory;

    protected $table = 'opsi_preferensi';

    protected $fillable = ['tipe', 'nilai'];
}
