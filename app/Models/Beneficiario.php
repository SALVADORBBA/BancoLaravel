<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficiario extends Model
{
    use HasFactory;

    // Define the table name explicitly if needed (usually Laravel will infer it)
    protected $table = 'beneficiarios';

    // Define which attributes can be mass-assigned (e.g., for create or update operations)
    protected $fillable = [
        'nome',
        'tipo_pessoa',
        'documento',
         'insc_estadual',
        'endereco',
        'cidade',
        'estado',
        'cep',
        'telefone',
        'email',
        'numero',
        'complemento',
        'bairro',
        'cmun',
        'cuf',
    ];

    // If needed, specify any attributes that should be cast to a specific data type
    protected $casts = [
        'cmun' => 'integer',
        'cuf' => 'integer',
    ];
}
