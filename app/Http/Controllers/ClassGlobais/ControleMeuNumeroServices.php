<?php



namespace App\Http\Controllers\ClassGlobais;

use App\Http\Controllers\Controller;
use App\Models\ControleMeuNumero;
use Illuminate\Http\Request;

class ControleMeuNumeroServices extends Controller
{
    /**
     * Arquivo: ControleMeuNumeroServices.php
     * Autor: Rubens do Santos
     * Contato: salvadorbba@gmail.com
     * Data: data_de_criacao
     * Descrição: Descrição breve do propósito deste arquivo.
 
     * Verifica e atualiza o número de controle com base nos parâmetros fornecidos.
     *
     * @param int $BancosId ID do parâmetro de banco
     * @param int $systemUnitId ID da unidade do sistema
     * @return string Número de controle formatado com zeros à esquerda
     * https://medium.com/@arikardnoir/documentando-uma-api-rest-laravel-usando-swagger-2daa24b752e0
     */


    public function verificarEAtualizarNumero_itau($BancosId)
    {
        // Verifica se existe um registro com status "livre"
        $registro = ControleMeuNumero::where('parametros_bancos_id', $BancosId)
            ->where('status', 'livre')
            ->orderBy('ultimo_numero', 'asc')
            ->first();

        if (isset($registro)) {
            return str_pad($registro->ultimo_numero, 8, '0', STR_PAD_LEFT);
        } else {

            $ultimoRegistro = ControleMeuNumero::where('parametros_bancos_id', $BancosId)

                ->orderBy('id', 'desc')
                ->first();
            $novoRegistro = new ControleMeuNumero();
            $novoRegistro->parametros_bancos_id = $BancosId;
            // $novoRegistro->system_unit_id = $systemUnitId;
            $novoRegistro->ultimo_numero = $ultimoRegistro ? str_pad($ultimoRegistro->ultimo_numero, 9, '0', STR_PAD_LEFT) + 1 : 1;
            $novoRegistro->status = 'livre';
            $novoRegistro->save();
            return (string) $novoRegistro->ultimo_numero;
        }
    }
}
