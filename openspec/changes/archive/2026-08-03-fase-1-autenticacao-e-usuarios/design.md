## Context

A Fase 1 foca no controle de identidade e acessos (IAM) da plataforma. O ecossistema inclui autenticação nativa (Sanctum), OAuth2 via Google Socialite, redefinição segura de senha com magic links assinados e a gestão administrativa de contas com auditoria de alteração de privilégios.

## Goals / Non-Goals

**Goals:**
- Implementação de endpoints REST stateless `/api/v1/auth/*` e `/api/v1/admin/users/*`.
- Camada de serviço separada (`AuthService`, `UserManagementService`) com abstração por repositório (`UserRepositoryInterface`).
- Proteção por Policies (`UserPolicy`) e Form Requests imutáveis (`RegisterUserRequest`, `LoginRequest`, etc.).
- Jobs e Mailables assíncronos (`SendWelcomeEmailJob`, `SendMagicLinkResetJob`).
- Mapeamento de log de auditoria de elevação de permissão.

**Non-Goals:**
- Gestão de conteúdo (artigos/comentários) — tratado nas Fases 2 e 3.
- Suporte a múltiplos provedores OAuth além do Google nesta fase.

## Decisions

- **Tokens Stateless:** Sanctum com tokens de acesso pessoal gerenciados por header `Authorization: Bearer <token>`.
- **Magic Links Assinados:** Utilização de `URL::temporarySignedRoute` com validade de 60 minutos e verificação de hash para evitar reutilização.
- **Auditoria de Permissões:** Tabela dedicada `permission_audit_logs` registrando `actor_id`, `target_id`, `action`, `old_values`, `new_values`, `created_at`.
- **Defesa em Profundidade:** Validação de entradas via Form Request, autorização na camada de rota/middleware (Sanctum/Spatie) e checagens explícitas na Policy do Laravel.

## Risks / Trade-offs

- [Socialite Mock em Ambientes de Teste] → Mitigação: Criar helpers com `Socialite::shouldReceive()` nos testes Pest para evitar chamadas de rede externas.
- [Concorrência em Registro Social vs Local] → Mitigação: Vincular contas pelo e-mail único do usuário e registrar a conta social em tabela `social_accounts`.
