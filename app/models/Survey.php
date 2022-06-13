<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Survery extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'surverys';

    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'idOrder', 'scoreTable', 'scoreRestaurant', 'scoreWaiter', 'scoreChef', 'comments'
    ];

    public static function crearEncuesta($codigo_mesa, $punt_mesa, $punt_restaurante, $punt_mozo, $punt_cocinero, $comentarios)
    {
        $encuesta = new Survery([
            'codigo_mesa' => $codigo_mesa,
            'punt_mesa' => $punt_mesa,
            'punt_restaurante' => $punt_restaurante, 
            'punt_mozo' => $punt_mozo, 
            'punt_cocinero' => $punt_cocinero,
            'comentarios' => $comentarios,
        ]);

        $encuesta->save();
    }
}
