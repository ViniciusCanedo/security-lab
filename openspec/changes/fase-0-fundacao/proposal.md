## Why

Estabelecer a fundação técnica da API de Blog com Laravel 12.x conforme definido no PRD e AGENTS.md. Esta fase garante que todas as dependências, configurações de ambiente, pipeline de CI, estrutura de diretórios em camadas (Controller -> Service -> Repository -> Model / DTO / Resource), filas assíncronas, migrações de banco de dados e seeders de controle de acesso (RBAC via spatie/laravel-permission) estejam devidamente configurados com 100% de TDD e análise estática pronta.

## What Changes

- Setup inicial do framework Laravel 12.x e configuração dos arquivos de ambiente (`.env.example`, `.env`).
- Instalação e publicação de configurações dos pacotes base: `laravel/sanctum`, `laravel/socialite`, `spatie/laravel-permission`, `laravel/pint`, `larastan/larastan`, e suíte de testes (PHPUnit/Pest).
- Criação do layout de diretórios exigido pela arquitetura: `app/DTOs`, `app/Services`, `app/Repositories/Contracts`, `app/Repositories/Eloquent`, `app/Http/Resources`, `app/Http/Requests`, `app/Policies`, `app/Enums`.
- Configuração de filas dedicadas: `emails` e `newsletter`, com drivers apropriados (`database` em dev, `redis` em produção).
- Configuração do pipeline de CI/CD para automação de testes (Pest), linting (Pint) e análise estática (Larastan Nível 5+).
- Migrações base: tabelas `users`, `roles`, `permissions`, e tabelas pivot (`model_has_roles`, `model_has_permissions`, `role_has_permissions`).
- Seeders estruturados para os papéis `ADMIN`, `PUBLISHER` e `COMMON`, mapeando as permissões granulares iniciais do sistema.

## Capabilities

### New Capabilities
- `project-foundation`: Configuração base da infraestrutura Laravel, arquitetura em camadas, filas e RBAC base com seeders.

### Modified Capabilities

## Impact

- Estrutura completa de diretórios do backend Laravel.
- Banco de dados inicial com suporte a RBAC e Soft Deletes.
- Pipeline de integração contínua (CI) e ferramentas de qualidade ativas no repositório.
