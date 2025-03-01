<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoletosMovimentacao extends Model {
    use HasFactory;

    protected $table = 'boletos_movimentacao';

    protected $fillable = [
        'contasreceber_id',
        'multa',
        'abatimento',
        'tipojuros',
        'diasprotesto',
        'validadeaposvencimento',
        'diasnegativacao',
        'tipodesconto',
        'descontoantecipacao',
        'juros',
        'dadosliquidacao_data',
        'dadosliquidacao_valor',
        'dadosliquidacao_multa',
        'dadosliquidacao_abatimento',
        'dadosliquidacao_juros',
        'dadosliquidacao_desconto',
        'datamovimento',
        'dataprevisaopagamento',
    ];

    public function boletoLiquidacoes() {
        return $this->hasMany(BoletoLiquidacao::class, 'boletos_movimentacao_id');
    }
}
