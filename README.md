# API de Comunicação com Bancos

Esta API permite a comunicação com serviços bancários para a geração de boletos. Atualmente, estamos iniciando a implementação com os bancos **Itaú** e **Santander**, mas o sistema está preparado para a expansão futura para outros bancos.

## Antes de Implementar

Antes de iniciar a implementação, o desenvolvedor deve acessar as áreas de desenvolvedores de cada banco para obter as credenciais de **sandbox** e **produção**:

- [Área de Desenvolvedores do Itaú](https://developer.itau.com.br/)
- [Área de Desenvolvedores do Santander](https://developer.santander.com.br/)

## Bancos Suportados
- **Itaú** (implementado)
- **Santander** (implementado)
- Outros bancos (em breve)

## Rotas Disponíveis

```php
// Geração do Token de Acesso
Route::post('/GetToken', [CreateToken::class, 'create']);

// Criação de Boleto Bancário
Route::post('/boleto/create', [CreateBoleto::class, 'create']);

Esta API permite a comunicação com serviços bancários para a geração de boletos. Atualmente, estamos iniciando a implementação com os bancos **Itaú** e **Santander**, mas o sistema está preparado para a expansão futura para outros bancos.

## Bancos Suportados
- **Itaú** (implementado)
- **Santander** (implementado)
- Outros bancos (em breve)

## Rotas Disponíveis

```php
// Geração do Token de Acesso
Route::post('/GetToken', [CreateToken::class, 'create']);

// Criação de Boleto Bancário
Route::post('/boleto/create', [CreateBoleto::class, 'create']);
```

## Requisitos

- PHP 8+
- Laravel 9+
- Banco de Dados MySQL

## Instalação

```sh
git clone https://github.com/SALVADORBBA/BancoLaravel.git
cd BancoLaravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

## Autenticação

A geração de boletos requer um token de acesso ativo. Certifique-se de chamar `POST /GetToken` antes de criar boletos.

## Licença

Este projeto está sob a licença MIT. Sinta-se à vontade para usar e modificar.
