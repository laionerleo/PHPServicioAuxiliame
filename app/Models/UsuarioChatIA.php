<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioChatIA extends Model
{
    use HasFactory;

    protected $table = 'USUARIOCHATIA';
    protected $primaryKey = null; // Composite primary key
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'Usuario',
        'Serial',
        'Mensaje',
        'Origen',
        'Fecha',
        'Hora',
    ];
}
