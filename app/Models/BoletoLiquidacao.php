<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoletoLiquidacao extends Model {
    use HasFactory;

    protected $table = 'boleto_liquidacao';

    protected $fillable = [
        'boletos_movimentacao_id',
        'data_liquidacao',
        'valor',
        'multa',
        'abatimento',
        'juros',
        'desconto',
    ];

    public function boletosMovimentacao() {
        return $this->belongsTo(BoletosMovimentacao::class, 'boletos_movimentacao_id');
    }
}
