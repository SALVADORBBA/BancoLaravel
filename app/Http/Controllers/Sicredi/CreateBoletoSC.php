<?php

namespace App\Http\Controllers\Sicredi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContasReceber;
use App\Models\ParametroBanco;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ClassGlobais\ClassGenerica;
use App\Http\Controllers\ControleMeuNumeroController;
use App\Models\Beneficiario;
use App\Models\ControleMeuNumeros;
  
use Exception;
use stdClass;

class CreateBoletoSC extends Controller
{
    // Usando Dependency Injection para instanciar o CreateToken
    private $titulos;
    private $parametros;
    private $pessoas;
    private $Token;
    private $tipo;
 
    private $beneficiario;

    public function __construct(Request $request)
    {
        $this->titulos = ContasReceber::find($request->id);
         $this->parametros = ParametroBanco::find($this->titulos->parametros_bancos_id);
        // $this->pessoas = $this->titulos->pessoa;
         $this->Token = CreateTokensSC::create( $this->parametros);
        // $this->tipo = $request->tipo;
        // $this->beneficiario = Beneficiario::find($request->beneficiario_id);
 
    }




    public function create()
     {
     return  $this->Token;
    }
}
