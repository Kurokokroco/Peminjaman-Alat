<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 'kategori';
    protected $primaryKey = 'id_kategori';
    protected $fillable = ['nama_kategori'];

    public function alat()
    {
        return $this->hasMany(Alat::class, 'id_kategori', 'id_kategori');
    }
}
