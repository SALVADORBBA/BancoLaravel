<?php

namespace App\Http\Controllers\ClassGlobais;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ITAU\TokenItau;
use App\Http\Controllers\ServicosDelicados\ControleMeuNumeroService;
use App\Models\Beneficiario;
use App\Models\Cliente;
use App\Models\CobrancaTitulo;
use App\Models\ParametrosBancos;
use Illuminate\Http\Request;
use stdClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ControllerMaster extends Controller
{
    /**
     * Arquivo: TokenItau.php
     * Autor: Rubens do Santos
     * Contato: salvadorbba@gmail.com
     * Data: data_de_criacao
     * Descrição: Descrição breve do propósito deste arquivo.
  
     * Método para criar um novo recurso de armazenamento.
     *
     * @param int $cobranca_id
     * @return \Illuminate\Http\Response
     */


    public  static function GetCreate($cobranca_id)
    {


        try {


            $Response_Titulo = CobrancaTitulo::find($cobranca_id);  // cobrança / titulo
            $Beneficiario = Beneficiario::find($Response_Titulo->beneficiario_id);
            $Cliente = Cliente::find($Response_Titulo->cliente_id);


            $Parametros = ParametrosBancos::select([
                'modelo_id',
                'client_secret',
                'client_id',
                'certificado',
                'senha',
                'client_id_bolecode',
                'client_secret_bolecode',
                'certificados_pix',
                'certificados_extra',
                'senha_certificado_pix',
                'senha_certificado_extra',
                'numerocontrato as id_beneficiario',
                'carteira',
                'id as parametros_bancos_id',
                'system_unit_id',
                'certificado_base64',
                'certificado_pix_base64'
            ])
                ->where('id', '=', $Response_Titulo->parametros_bancos_id)
                ->first();

            $obj = new stdClass();
            $obj->client_id = $Parametros->client_id;
            $obj->client_secret = $Parametros->client_secret;
            $obj->id_beneficiario = $Parametros->id_beneficiario;
            $obj->certificado = $Parametros->certificado;
            $obj->senha = $Parametros->senha;
            $obj->carteira = $Parametros->carteira;
            $obj->seunumero = $Response_Titulo->seunumero;
            $obj->parametros_bancos_id = $Parametros->parametros_bancos_id;
            $obj->system_unit_id = $Parametros->system_unit_id;
            $obj->certificado_base64 = $Parametros->certificado_base64;
            $obj->data_vencimento = $Response_Titulo->data_vencimento;
            $obj->modelo_id = $Parametros->modelo_id;



            $obj->boleto = new stdClass();
            $obj->boleto =  $Response_Titulo;
            $obj->cliente = new stdClass();
            $obj->cliente =  $Cliente;
            $obj->Bendeficiario = new stdClass();
            $obj->Bendeficiario =  $Beneficiario;
            $obj_seguimentado = new stdClass();
            $obj_seguimentado->cliente = $obj->cliente;
            $obj_seguimentado->Bendeficiario = $obj->Bendeficiario;
            $obj_seguimentado->boleto = $obj->boleto;


            /** 
             *responsavel em criar o arquivo pfx 
           
             */
            $pasta = 'certificado/pfx/' .  $Beneficiario->cnpj . '/' . $Response_Titulo->beneficiario_id . '/' . $Response_Titulo->parametros_bancos_id . '/modelo_' . $Parametros->modelo_id;
            $certificado_real = $pasta . '/certificado.pfx';

            if (is_dir($pasta)) {
            } else {
                mkdir($pasta, 0777, true);
            }

            $decodedCert = base64_decode($obj->certificado_base64);
            file_put_contents($certificado_real, $decodedCert);

            /** 
             *responsavel em criar o arquivo pfx 
           
             */

            ################# gera token ##############
            $token =  TokenItau::itau(
                $obj->client_id,
                $obj->client_secret,
                $certificado_real,
                $obj->senha
            );

            $ControleMeuNumeroService = new ControleMeuNumeroServices();
            $ultimoNumero = $ControleMeuNumeroService->verificarEAtualizarNumero_itau($Response_Titulo->parametros_bancos_id);
            $numero_agregado = str_pad($ultimoNumero, 8, '0', STR_PAD_LEFT);


            $Cobranca = new stdClass();
            $Cobranca->id = $Response_Titulo->id;
            $Cobranca->vencimento = $Response_Titulo->data_vencimento;
            $Cobranca->valor = $Response_Titulo->valor;
            $Cobranca->parametros_bancos_id = $Response_Titulo->parametros_bancos_id;
            $Cobranca->identificacaoboletoempresa = $Response_Titulo->identificacaoboletoempresa;
            $Cobranca->cobranca_id = $Response_Titulo->cobranca_id;



            $obj_seguimentado = new stdClass();
            $obj_seguimentado->cliente = $obj->cliente;
            $obj_seguimentado->Beneficiario = $obj->Bendeficiario;
            $obj_seguimentado->boleto = $obj->boleto;
            $obj_seguimentado->token = $token;
            $obj_seguimentado->numero_nosso_numero =   $numero_agregado;
            $obj_seguimentado->Parametros =  $Parametros;
            $obj_seguimentado->certificado =  $certificado_real;
            $obj_seguimentado->senha =   $obj->senha;
            $obj_seguimentado->client_id =   $obj->client_id;
            $obj_seguimentado->client_secret =   $obj->client_secret;

            return     $obj_seguimentado;
        } catch (\Exception $e) {
            Log::error('Erro ao processar GetCreate: ' . $e->getMessage());
            return response()->json([
                'Resposta' => [
                    'codigo' => 500,
                    'mensagem' => 'Ocorreu um erro no servidor.',
                ],
            ], 500);
        }
    }
}
