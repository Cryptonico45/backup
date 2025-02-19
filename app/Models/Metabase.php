<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Metabase extends Model
{
    use HasFactory;

    protected $table = 'metabase';
    protected $fillable = ['kategori', 'link_metabase', 'keterangan'];
}
