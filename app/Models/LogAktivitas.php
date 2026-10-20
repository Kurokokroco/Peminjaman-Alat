<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';

    protected $primaryKey = 'id_log';

    public $timestamps = false;

    protected $fillable = ['id_user', 'aktivitas'];

    public static function catat(string $aktivitas): void
    {
        self::create([
            'id_user' => Auth::id(),
            'aktivitas' => $aktivitas,
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
