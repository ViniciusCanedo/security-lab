## 1. Setup do Projeto e Dependências

- [x] 1.1 Inicializar aplicação Laravel 12.x e estruturar ambiente `.env`
- [x] 1.2 Instalar pacotes core: `laravel/sanctum`, `laravel/socialite`, `spatie/laravel-permission`
- [x] 1.3 Instalar pacotes de dev/qualidade: `laravel/pint`, `larastan/larastan`, `pestphp/pest`

## 2. Arquitetura e Diretórios Base

- [x] 2.1 Criar estrutura de diretórios `app/DTOs`, `app/Services`, `app/Repositories/Contracts`, `app/Repositories/Eloquent`, `app/Http/Resources`, `app/Http/Requests`, `app/Policies`, `app/Enums`
- [x] 2.2 Configurar Service Provider para Repositories (`RepositoryServiceProvider`)

## 3. Banco de Dados e Seeders RBAC

- [x] 3.1 Criar migrações base para tabela `users` com soft deletes e tabelas pivot do `laravel-permission`
- [x] 3.2 Implementar `RoleAndPermissionSeeder` criando papéis `ADMIN`, `PUBLISHER`, `COMMON` e matriz inicial de permissões

## 4. Filas e CI/CD

- [x] 4.1 Configurar filas dedicadas `emails` e `newsletter` no `config/queue.php`
- [x] 4.2 Configurar workflow do GitHub Actions / CI para Pint, Larastan e Pest
