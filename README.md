# API de Comunicação com o Itaú

Esta API permite a comunicação com os serviços do Itaú para geração de boletos bancários. Ela possui duas rotas principais:

## Rotas Disponíveis

### 1. Geração do Token de Acesso

- **Rota:** `POST /GetToken`
- **Controlador:** `CreateToken`
- **Método:** `create`
- **Descrição:**
  - Gera um token de acesso válido por 5 minutos.
  - O token é salvo no banco de dados para uso posterior.
  - Caso o token esteja expirado, um novo token é gerado automaticamente.

### 2. Criação de Boleto Bancário

- **Rota:** `POST /boleto/create`
- **Controlador:** `CreateBoleto`
- **Método:** `create`
- **Descrição:**
  - Utiliza o token gerado previamente para autenticação.
  - Envia os dados necessários para criar um boleto no sistema do Itaú.

## Requisitos

- PHP 8+
- Laravel 9+
- Banco de Dados MySQL

## Instalação

1. Clone o repositório:
   ```sh
   git clone https://github.com/seuusuario/seurepositorio.git
   cd seurepositorio
   ```
2. Instale as dependências:
   ```sh
   composer install
   ```
3. Configure o ambiente:
   ```sh
   cp .env.example .env
   php artisan key:generate
   ```
4. Configure as credenciais do Itaú no arquivo `.env`.
5. Execute as migrações:
   ```sh
   php artisan migrate
   ```
6. Inicie o servidor:
   ```sh
   php artisan serve
   ```

## Autoração

A geração de boletos requer um token de acesso ativo. Certifique-se de chamar `POST /GetToken` antes de criar boletos.

## Licença

Este projeto está sob a licença MIT. Sinta-se à vontade para usar e modificar.

