<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoPostulacion extends Model
{
    use HasFactory;

    protected $table = 'PEDIDOPOSTULACION';
    protected $primaryKey = null; // Composite primary key
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'Pedido',
        'Serial',
        'Usuario',
        'NombreMecanico',
        'Telefono',
        'TiempoEstimado',
        'Precio',
        'Estado',
        'FechaRegistro',
        'Usr',
        'UsrFecha',
        'UsrHora',
    ];
}
