<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParametroBanco extends Model
{
    use HasFactory;

    protected $table = 'parametros_bancos';

    protected $fillable = [
        'key',
        'url1',
        'certificado',
        'senha',
        'client_id',
        'client_secret',
        'expires_in',
        'token',
        'data_token',
    ];
}
