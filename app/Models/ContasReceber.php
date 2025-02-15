<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContasReceber extends Model
{
    use HasFactory;

    protected $table = 'contasreceber';

    protected $fillable = [
        'pessoa_id',
        'nossonumero',
        'seunumero',
        'parametros_bancos_id',
        'valor',
        'data_vencimento',
        'status',
        'qrcode',
        'linhadigitavel',
        'codigobarras',
        'etapa_processo_boleto',
        'txid',
        'pdfboletobase64'
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
