<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControleMeuNumeros extends Model
{
    use HasFactory;

    protected $table = 'controle_meu_numeros'; // Nome da tabela

    protected $fillable = [
        'parametros_bancos_id',
        'ultimo_numero',
        'numero_anterior',
        'created_at',
        'updated_at',
        'status',
        'banco_id',
        'system_unit_id'
    ];

    // Desabilitar timestamps se necessário (caso não esteja usando 'created_at' e 'updated_at' automaticamente)
    public $timestamps = true;
}
