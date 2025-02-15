<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pessoa extends Model
{
    use HasFactory;

    protected $table = 'pessoa';

    protected $fillable = [
        'documento',
        'insc_estadual',
        'nome',
        'nome_fantasia',
        'email',
        'whatsapp',
        'pessoa_atacado_id',
        'forca_venda_id',
        'email_fx',
        'fone_fx',
        'cep',
        'rua',
        'bairro',
        'numero',
        'complemento',
   
        'ibge',
        'cuf'
    ];

    public function contasReceber()
    {
        return $this->hasMany(ContasReceber::class, 'pessoa_id');
    }
}
