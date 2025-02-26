<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewBuscaApi extends Model
{
    // Defina o nome da view
    protected $table = 'view_busca_api';

    // Indica que a view não tem timestamps
    public $timestamps = false;

    // Se necessário, você pode listar os campos da view
    protected $fillable = [
        'id', 
        'apelido', 
        'nome', 
        'email', 
        'whatsapp', 
        'cep', 
        'documento', 
        'nossonumero', 
        'seunumero', 
        'valor', 
        'data_vencimento', 
        'linhadigitavel', 
        'codigobarras', 
        'qrcode'
    ];
}
