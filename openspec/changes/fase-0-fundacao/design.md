## Context

A API de Blog exige alto nível de segurança, manutenibilidade e desacoplamento de camadas, conforme ditado pelas regras inegociáveis do `AGENTS.md`. A Fase 0 estabelece as fundações tecnológicas, infraestrutura de testes, pipelines CI, modelo RBAC inicial e a divisão estrita de responsabilidade de código.

## Goals / Non-Goals

**Goals:**
- Configuração completa do Laravel 12 com PHP 8.3+.
- Estruturação completa das pastas da aplicação (`DTOs`, `Services`, `Repositories/Contracts`, `Repositories/Eloquent`, `Http/Resources`, `Http/Requests`, `Policies`, `Enums`).
- Instalação e publicação dos pacotes Sanctum, Socialite, Spatie Laravel-Permission, Pint, Larastan e Pest.
- Configuração de filas dedicadas (`emails`, `newsletter`).
- Pipeline CI para testes automatizados e análise estática.
- Migrações base (`users`, `roles`, `permissions`) e Seeders para a matriz inicial de permissões.

**Non-Goals:**
- Implementação das rotas REST da API e regras de negócio da Fase 1 em diante.
- Integração em ambiente de nuvem ou deploy final.

## Decisions

- **Framework & PHP:** Laravel 12.x sobre PHP 8.3+, garantindo suporte a recursos modernos como DTOs imutáveis com `readonly`.
- **RBAC via spatie/laravel-permission:** Em vez de condições `if ($user->role === 'admin')`, a checagem será baseada em permissões finas mapeadas para roles (`ADMIN`, `PUBLISHER`, `COMMON`).
- **Arquitetura Repository-Service-DTO:**
  ```
  Route -> Middleware -> Form Request -> Controller -> Service -> Repository (Interface) -> Model
                                                             -> DTO -> JSON Resource
  ```
  Toda troca de dados interna utiliza DTOs fortemente tipados. Repositories dependem de contratos/interfaces (`app/Repositories/Contracts`).

## Risks / Trade-offs

- [Complexidade Inicial de Camadas] → Mitigação: Mapear rigidamente diretórios e templates no início do projeto em `AGENTS.md` e validar com Larastan/Pest.
- [Dependência de Filas em Testes] → Mitigação: Usar driver `sync` ou `database` nos ambientes de desenvolvimento/teste e `Queue::fake()` nos testes automatizados.
