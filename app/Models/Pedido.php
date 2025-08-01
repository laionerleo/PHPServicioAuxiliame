<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'PEDIDO';
    protected $primaryKey = 'Pedido';
    public $timestamps = false;

    protected $fillable = [
        'Usuario',
        'TipoPedido',
        'Latitud',
        'Longitud',
        'Detalles',
        'Estado',
        'FechaRegistro',
        'Usr',
        'UsrFecha',
        'UsrHora',
    ];
}
