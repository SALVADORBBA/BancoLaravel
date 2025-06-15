<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetornoWebhook extends Model
{
    protected $table = 'retorno_webhook';

    protected $fillable = [
        'webhook_id',
        'dataregistro',
        'datavencimento',
        'valororiginal',
        'valorpagosacado',
        'numeroconvenio',
        'numerooperacao',
        'carteiraconvenio',
        'variacaocarteiraconvenio',
        'codigoestadobaixaoperacional',
        'dataliquidacao',
        'instituicaoliquidacao',
        'canalliquidacao',
        'codigomodalidadeboleto',
        'tipopessoaportador',
        'identidadeportador',
        'nomeportador',
        'formapagamento',
    ];

    public $timestamps = false;
}
