<?php

namespace App\Http\Controllers\Bradesco;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContasReceber;
use App\Models\ParametroBanco;
use App\Http\Controllers\ClassGlobais\ClassGenerica;
 use App\Models\Beneficiario;
use App\Models\ControleMeuNumeros;
use Exception;
use stdClass;

class PrintBoletoBradesco extends Controller
{
    private $titulos;
    private $parametros;
    private $Token;
    private $tipo;
    private $meunumero;
    private $beneficiario;
    public function __construct(Request $request)
    {
        // Inicializando as variáveis necessárias
        $this->titulos = ContasReceber::find($request->id);
        $this->parametros = ParametroBanco::find($this->titulos->parametros_bancos_id);
        $this->beneficiario = Beneficiario::find($this->titulos->beneficiario_id);
       
    }

    public function create()
    {
 
    }
}
