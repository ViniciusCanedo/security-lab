## 1. DTOs, Form Requests e Contratos do Repositorio de Usuarios

- [ ] 1.1 Criar DTOs imutáveis (`RegisterUserDTO`, `LoginDTO`, `ResetPasswordDTO`, `UpdateUserRoleDTO`)
- [ ] 1.2 Criar Form Requests (`RegisterUserRequest`, `LoginRequest`, `ForgotPasswordRequest`, `ResetPasswordRequest`, `UpdateUserRoleRequest`)
- [ ] 1.3 Criar contrato `UserRepositoryInterface` e implementação `UserRepositoryEloquent`

## 2. Servicos de Autenticacao e E-mails Assincronos

- [ ] 2.1 Criar `AuthService` lidando com registro, emissão de tokens Sanctum, revogação e login Google Socialite
- [ ] 2.2 Implementar Jobs assíncronos e Mailables (`SendWelcomeEmailJob`, `SendMagicLinkResetJob`)
- [ ] 2.3 Implementar fluxo de verificação e invalidação do magic link de redefinição de senha

## 3. Servico de Gestao de Usuarios e Auditoria (ADMIN)

- [ ] 3.1 Criar `UserManagementService` com listagem paginada, filtros, criação por admin e soft delete
- [ ] 3.2 Implementar alteração de papéis e conceder/revogar permissões avulsas com log em `permission_audit_logs`
- [ ] 3.3 Criar `UserPolicy` aplicando restrições por permissão Spatie

## 4. Controllers REST e Resources

- [ ] 4.1 Criar `AuthController` (`/api/v1/auth/*` e `/api/v1/me`) utilizando `UserResource`
- [ ] 4.2 Criar `AdminUserController` (`/api/v1/admin/users/*`)
- [ ] 4.3 Escrever suíte completa de testes TDD com Pest (happy path, validação, 401/403, Socialite mock, Mail/Queue fakes)
