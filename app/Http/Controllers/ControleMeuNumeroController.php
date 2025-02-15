<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ControleMeuNumeros;
use stdClass;

class ControleMeuNumeroController extends Controller
{
    public static function create($parametros_bancos_id)
    
 {
       

        $meunumeroSTD = new stdClass();

        // Verifica se existe um registro livre
        $livreRegistro = ControleMeuNumeros::where('parametros_bancos_id', $parametros_bancos_id)
               ->where('status', 'livre')
            ->first();

        if ($livreRegistro) {
            $meunumeroSTD->id = $livreRegistro->id;
            $meunumeroSTD->numero = str_pad($livreRegistro->ultimo_numero, 7, '0', STR_PAD_LEFT);
        } else {
            // Criar um novo registro
            $novoRegistro = new ControleMeuNumeros();
            $novoRegistro->parametros_bancos_id = $parametros_bancos_id;
 
            $ultimoRegistro = ControleMeuNumeros::where('parametros_bancos_id', $parametros_bancos_id)
         
                ->orderBy('id', 'desc')
                ->first();

            $novoRegistro->ultimo_numero = $ultimoRegistro ? $ultimoRegistro->ultimo_numero + 1 : 1;
            $novoRegistro->status = 'livre';

            try {
                $novoRegistro->save();
                $meunumeroSTD->id = $novoRegistro->id;
                $meunumeroSTD->numero = str_pad($novoRegistro->ultimo_numero, 7, '0', STR_PAD_LEFT);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Erro ao salvar: ' . $e->getMessage()], 500);
            }
        }

        return $meunumeroSTD;
    }
}
