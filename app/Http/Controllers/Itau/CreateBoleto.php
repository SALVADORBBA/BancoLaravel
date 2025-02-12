<?php
namespace App\Http\Controllers\Itau;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Itau\CreateToken; // Adicione o namespace da classe CreateToken
use Illuminate\Http\Request;

class CreateBoleto extends Controller
{
    // Usando Dependency Injection para instanciar o CreateToken
    protected $createToken;

    public function __construct(CreateToken $createToken)
    {
        $this->createToken = $createToken;
    }

    public function create(Request $request)
    {
        // Agora você pode usar o método create da classe CreateToken
        $response = $this->createToken->create($request); 

        // Retorne a resposta ou faça o que for necessário
        return $response;
    }
}
