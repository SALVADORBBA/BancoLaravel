# API de Comunicação com Bancos

Esta API permite a comunicação com serviços bancários para a geração de boletos. Atualmente, estamos iniciando a implementação com os bancos **Itaú**, **Santander** e **Sicredi**, mas o sistema está preparado para a expansão futura para outros bancos.

## Antes de Implementar

Antes de iniciar a implementação, o desenvolvedor deve acessar as áreas de desenvolvedores de cada banco para obter as credenciais de **sandbox** e **produção**:

- [Área de Desenvolvedores do Itaú](https://developer.itau.com.br/)
- [Área de Desenvolvedores do Santander](https://developer.santander.com.br/)
- [Área de Desenvolvedores do Sicredi](https://www.sicredi.com.br/site/developers/)



 
## Requisitos de Certificado Digital

- Itaú: Necessita gerar o certificado A1.
- [Documentação](https://devportal-portalassets-hom.cloud.itau.com.br/curl.mp4)
- [Video Tutorial](https://devportal.itau.com.br/certificado-dinamico)

- Santander: Pode utilizar o certificado A1 padrão, o mesmo usado para a emissão de documentos fiscais.
- Sicredi: O certificado A1 é necessário apenas para gerar as credenciais no portal do banco.


## Bancos Suportados

- **Itaú** (implementado)
- **Santander** (implementado)
- **Sicredi** (implementado)
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

## Contribua com este projeto 💙

Se este projeto foi útil para você e deseja apoiar o desenvolvimento, contribua com qualquer valor via **PIX**:

- 📱 **Chave PIX:** (71) 99675-8056
- 🏦 **Titular:** Rubens dos Santos
- [Nosso Canal no Youtube](https://www.youtube.com/@DEVELOPERAPI-BR)

## Download

Você pode baixar este projeto diretamente do GitHub:

[Download do Projeto](https://github.com/SALVADORBBA/BancoLaravel/archive/refs/heads/main.zip)

##

Toda contribuição é bem-vinda e ajuda a manter o projeto ativo! 🚀

## Licença

Este projeto está sob a licença MIT. Sinta-se à vontade para usar e modificar.
