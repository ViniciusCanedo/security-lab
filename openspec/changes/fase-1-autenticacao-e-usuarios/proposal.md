## Why

Implementar o módulo completo de Autenticação e Gestão de Usuários (RF01 e RF02) conforme especificado no PRD. Este módulo permite o registro, login via credenciais locais ou Google OAuth, encerramento de sessão, consulta de perfil (`/me`), recuperação de senha via magic link assinado assíncrono e controle total dos usuários por administradores com auditoria de elevação de papéis e permissões avulsas.

## What Changes

- **RF01 — Autenticação e Contas**:
  - `POST /api/v1/auth/register`: Registro local de usuário com atribuição padrão do papel `COMMON` e disparo de e-mail de boas-vindas assíncrono.
  - `POST /api/v1/auth/login`: Autenticação local gerando token Sanctum.
  - `POST /api/v1/auth/logout`: Revogação do token ativo (middleware Sanctum).
  - `GET /api/v1/auth/google/redirect` & `GET /api/v1/auth/google/callback`: Login social Google via Laravel Socialite (vinculando por e-mail ou criando usuário `COMMON`).
  - `POST /api/v1/auth/password/forgot`: Envio assíncrono de magic link assinado (`URL::temporarySignedRoute`, expiração de 60 min).
  - `POST /api/v1/auth/password/reset`: Redefinição efetiva com token de uso único.
  - `GET /api/v1/me`: Retorna os dados do usuário logado e suas permissões efetivas.

- **RF02 — Gestão de Usuários (ADMIN)**:
  - `GET /api/v1/admin/users`: Listagem paginada com busca por nome/e-mail e filtro por papel.
  - `POST /api/v1/admin/users`: Adição/convite manual de usuário por ADMIN.
  - `DELETE /api/v1/admin/users/{id}`: Soft delete de usuário por ADMIN.
  - `PUT /api/v1/admin/users/{id}/role`: Elevação/rebaixamento de papel (`COMMON`, `PUBLISHER`, `ADMIN`) com registro de audit log (`who`, `when`, `from_role`, `to_role`).
  - `POST /api/v1/admin/users/{id}/permissions`: Concessão ou revogação de permissões avulsas.

## Capabilities

### New Capabilities
- `authentication-and-accounts`: Autenticação local, OAuth Google, magic link e perfil do usuário.
- `user-management`: Gestão de usuários, alteração de papéis, permissões avulsas e auditoria por ADMIN.

### Modified Capabilities

## Impact

- Novas rotas na versão `/api/v1` em `routes/api.php`.
- Criação de `AuthService`, `UserManagementService`, `UserRepositoryContract`, `UserRepositoryEloquent`.
- Criação de DTOs (`RegisterUserDTO`, `LoginDTO`, `ResetPasswordDTO`, `UpdateUserRoleDTO`, etc.).
- Form Requests específicos para cada ação com mensagens em português.
- Eventos e Jobs (`SendWelcomeEmailJob`, `SendMagicLinkResetJob`).
- Mapeamento de auditoria para elevação de permissões.
