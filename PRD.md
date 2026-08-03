# PRD.md — API de Blog com Laravel

## 1. Visão Geral

Construir uma API RESTful para um blog com sistema de permissionamento granular baseado em papéis
(`ADMIN`, `PUBLISHER`, `COMMON`), suporte a curtidas, comentários e respostas de comentários,
publicação/edição/arquivamento/exclusão de artigos, gestão de usuários, newsletter com envio
assíncrono de e-mails, autenticação local (usuário/senha) e social (Google), e redefinição de
senha via magic link. A arquitetura segue MVC do Laravel estendido com Service Layer, DTOs e
Repositories, com todo o desenvolvimento guiado por TDD (ver `CONSTITUTION.md` para as regras
arquiteturais inegociáveis).

## 2. Objetivos

- Fornecer uma API segura, testável e de fácil manutenção para consumo por um frontend (SPA/mobile) ainda não definido.
- Garantir que o controle de acesso seja granular o suficiente para permitir evolução futura de permissões sem refatoração estrutural.
- Automatizar comunicação com usuários (newsletter, notificações transacionais) sem impacto de performance na API.

## 3. Personas / Papéis (Roles)

| Papel | Descrição |
|---|---|
| **COMMON** | Usuário final do blog. Lê artigos publicados, curte, comenta e responde comentários. |
| **PUBLISHER** | Criador de conteúdo. Tudo que COMMON faz, mais criar, editar e arquivar seus próprios artigos, e gerenciar campanhas de newsletter. |
| **ADMIN** | Administrador da plataforma. Tudo que PUBLISHER faz (em qualquer artigo, não só os próprios), mais excluir artigos definitivamente, adicionar/remover usuários e elevar permissões de outros usuários. |

## 4. Requisitos Funcionais

### RF01 — Autenticação e Contas
- RF01.1 Registro de usuário com nome, e-mail e senha (papel padrão: `COMMON`).
- RF01.2 Login com e-mail e senha, retornando token Sanctum.
- RF01.3 Logout (revogação do token atual).
- RF01.4 Login social via Google (Socialite) — cria conta automaticamente no primeiro acesso e vincula por e-mail em acessos seguintes.
- RF01.5 Solicitação de redefinição de senha: usuário informa e-mail, sistema envia (assíncrono) um magic link assinado e com expiração.
- RF01.6 Redefinição efetiva de senha através do link (token de uso único, validado e invalidado após uso).
- RF01.7 Endpoint de "usuário autenticado" (`/me`) retornando dados do usuário e suas permissões efetivas.

### RF02 — Gestão de Usuários (ADMIN)
- RF02.1 Listar usuários (com filtros por papel, status, busca por nome/e-mail).
- RF02.2 Adicionar usuário manualmente (convite ou criação direta) por ADMIN.
- RF02.3 Remover (soft delete) usuário.
- RF02.4 Elevar ou rebaixar papel de um usuário (`COMMON → PUBLISHER → ADMIN` e vice-versa), com auditoria da alteração (quem, quando, de/para).
- RF02.5 Conceder ou revogar permissões avulsas a um usuário específico, além do conjunto padrão do seu papel.

### RF03 — Artigos
- RF03.1 PUBLISHER/ADMIN criam artigo (título, conteúdo, resumo, imagem de capa, tags/categorias, status inicial `rascunho`).
- RF03.2 PUBLISHER edita artigo próprio; ADMIN edita qualquer artigo.
- RF03.3 PUBLISHER arquiva artigo próprio; ADMIN arquiva qualquer artigo (artigo arquivado sai da listagem pública, mas não é excluído).
- RF03.4 Apenas ADMIN exclui artigo definitivamente (soft delete + rotina de expurgo futura, se necessário).
- RF03.5 Listagem pública paginada de artigos publicados (com filtros por tag/categoria/autor/data).
- RF03.6 Visualização de um artigo específico (contagem de visualizações opcional).
- RF03.7 Transições de status válidas: `rascunho → publicado → arquivado`, e `publicado → arquivado` (não é possível comentar/curtir artigo arquivado ou rascunho).

### RF04 — Curtidas
- RF04.1 Usuário autenticado (qualquer papel) curte um artigo publicado (uma curtida por usuário por artigo — toggle).
- RF04.2 Remover curtida (descurtir).
- RF04.3 Contagem de curtidas exposta no Resource do artigo.

### RF05 — Comentários e Respostas
- RF05.1 Usuário autenticado comenta em artigo publicado.
- RF05.2 Usuário autenticado responde a um comentário existente (thread de 1 nível ou N níveis — definir no design técnico; recomendação inicial: 1 nível de resposta para simplicidade).
- RF05.3 Autor do comentário pode editar/excluir seu próprio comentário.
- RF05.4 ADMIN pode excluir/moderar qualquer comentário (spam, ofensivo etc.).
- RF05.5 Listagem paginada de comentários de um artigo, com respostas aninhadas no Resource.

### RF06 — Newsletter
- RF06.1 Visitante/usuário se inscreve na newsletter informando e-mail (endpoint público, com confirmação por e-mail — double opt-in recomendado).
- RF06.2 Usuário cancela inscrição (unsubscribe) via link único no rodapé do e-mail.
- RF06.3 PUBLISHER/ADMIN criam uma campanha de newsletter (assunto, conteúdo, opcionalmente vinculada a um artigo).
- RF06.4 Disparo assíncrono da campanha para todos os inscritos confirmados, em lotes (jobs em fila), com registro de status de envio por destinatário.
- RF06.5 PUBLISHER/ADMIN visualizam status/relatório básico de uma campanha (enviados, falhos, pendentes).

### RF07 — Notificações Transacionais (assíncronas)
- RF07.1 E-mail de boas-vindas ao registrar.
- RF07.2 E-mail de confirmação de inscrição na newsletter.
- RF07.3 E-mail de magic link de redefinição de senha.
- RF07.4 (Opcional/fase futura) E-mail de notificação ao autor quando seu artigo recebe um novo comentário.

## 5. Requisitos Não Funcionais

| ID | Requisito |
|---|---|
| RNF01 | Autenticação stateless via Sanctum; sem sessão de servidor para a API. |
| RNF02 | Rate limiting em endpoints sensíveis (login, registro, reset de senha, criação de comentário) para mitigar abuso/spam. |
| RNF03 | Todas as respostas em JSON padronizado (ver `CONSTITUTION.md`, seção 7). |
| RNF04 | Envio de e-mail nunca bloqueia a thread da requisição HTTP (100% assíncrono via Jobs). |
| RNF05 | Testes automatizados cobrindo happy path, validação, autorização e regra de negócio para toda funcionalidade (TDD). |
| RNF06 | Logs estruturados de ações sensíveis (exclusão de artigo, elevação de permissão, remoção de usuário). |
| RNF07 | Paginação obrigatória em toda listagem (artigos, comentários, usuários). |
| RNF08 | Soft deletes em `User`, `Article`, `Comment` para auditoria e possibilidade de restauração. |
| RNF09 | Versionamento de API (`/api/v1`) para permitir evolução sem quebrar clientes existentes. |
| RNF10 | Segredos (client secret do Google, credenciais SMTP) apenas via variáveis de ambiente, nunca versionados. |

## 6. Fora de Escopo (nesta versão)

- Frontend/painel administrativo (a API é headless).
- Upload/CDN de imagens além do armazenamento básico (pode usar disco local/S3 configurável, sem otimização avançada).
- Múltiplos idiomas (i18n) de conteúdo do blog.
- Login social com provedores além do Google (Facebook, GitHub etc.) — arquitetura deve permitir adicionar depois, mas não é entregue agora.
- Editor WYSIWYG (a API recebe conteúdo já formatado, ex.: Markdown ou HTML, do cliente).

## 7. Modelo de Dados (visão de alto nível)

**Entidades principais:** `User`, `Role`/`Permission` (via spatie/laravel-permission), `Article`, `Comment` (auto-relacionamento `parent_id` para respostas), `Like`, `NewsletterSubscriber`, `NewsletterCampaign`, `NewsletterSend` (log de envio por destinatário), `PasswordResetToken` (ou uso da tabela padrão do Laravel adaptada para magic link), `SocialAccount` (vínculo `user_id` ↔ `provider` + `provider_id`), `PermissionAuditLog`.

## 8. Critérios de Aceite Gerais

- Um usuário `COMMON` que tenta criar um artigo recebe `403` com mensagem clara.
- Um usuário não autenticado que tenta comentar recebe `401`.
- Um `PUBLISHER` não consegue editar artigo de outro `PUBLISHER`, apenas `ADMIN` consegue.
- Excluir um usuário não apaga seus artigos (regra de negócio a definir: reatribuir a "usuário removido" ou manter autor apagado logicamente — decidir na Fase 2).
- Toda ação de e-mail dispara um Job, verificável via `Queue::fake()` nos testes, nunca envio síncrono.
- Login social cria o usuário com papel `COMMON` por padrão e nunca permite auto-elevação de permissão via provedor social.

---

## 9. Fases de Desenvolvimento (Roadmap)

> Cada fase segue TDD: testes escritos e falhando antes da implementação de cada item.
> Nenhuma fase começa sem a anterior estar com testes verdes e CI passando.

### **Fase 0 — Fundação do Projeto**
- Setup do projeto Laravel, configuração de ambiente (.env, banco, Redis).
- Instalação e configuração: Sanctum, Socialite, spatie/laravel-permission, Pint, Larastan, PHPUnit/Pest.
- Estrutura de diretórios definida em `CONSTITUTION.md` (DTOs, Services, Repositories, Contracts).
- Configuração de filas (`emails`, `newsletter`) e driver (database local / Redis produção).
- Pipeline de CI (lint, testes, análise estática).
- Migrations base: `users`, `roles`, `permissions`, tabelas pivot.
- Seeders de papéis e permissões (`ADMIN`, `PUBLISHER`, `COMMON` e sua matriz de permissões).

### **Fase 1 — Autenticação e Usuários**
- RF01 completo: registro, login, logout, `/me`.
- RF01.4: login social com Google via Socialite.
- RF01.5/RF01.6: fluxo de magic link de redefinição de senha (Job de envio + rota assinada + endpoint de reset).
- RF02 completo: CRUD de usuários por ADMIN, elevação/rebaixamento de papel, permissões avulsas, auditoria.
- Policies: `UserPolicy`.
- Testes: happy path, validação, 401/403, mock de Socialite, `Mail::fake()`/`Queue::fake()` para os e-mails do fluxo.

**Critério de saída da fase:** um usuário consegue se registrar, logar por senha ou Google, resetar senha via link, e um ADMIN consegue gerenciar outros usuários — tudo coberto por testes.

### **Fase 2 — Artigos**
- RF03 completo: criação, edição, arquivamento, exclusão, listagem pública, visualização individual.
- `ArticlePolicy`, `ArticleService`, `ArticleRepository` (+ interface), DTOs (`CreateArticleDTO`, `UpdateArticleDTO`), `ArticleResource`.
- Enum `ArticleStatus` (`draft`, `published`, `archived`).
- Regras de transição de status e quem pode executá-las.
- Testes: matriz completa de permissões por papel (COMMON bloqueado, PUBLISHER limitado ao próprio, ADMIN irrestrito).

**Critério de saída da fase:** matriz de permissões de artigos (seção 3.1 do `CONSTITUTION.md`) 100% coberta por testes de autorização.

### **Fase 3 — Engajamento (Curtidas e Comentários)**
- RF04 completo: curtir/descurtir com toggle e contagem.
- RF05 completo: comentar, responder, editar/excluir próprio comentário, moderação por ADMIN.
- `CommentPolicy`, `LikeService`/`CommentService`, Resources com respostas aninhadas.
- Regra: não permitir interação em artigo `draft` ou `archived`.
- Testes: concorrência de curtida (toggle idempotente), profundidade de respostas, moderação por ADMIN vs. dono.

### **Fase 4 — Newsletter e E-mails Assíncronos**
- RF06 completo: inscrição (com confirmação), cancelamento, criação de campanha, disparo em lote via Jobs, relatório de status.
- RF07 completo: e-mails transacionais restantes (boas-vindas, confirmação de inscrição).
- `NewsletterService`, `NewsletterRepository`, Jobs (`SendNewsletterCampaignJob`, `SendTransactionalEmailJob`), Mailables dedicados.
- Testes: `Queue::fake()`/`Bus::fake()` para lotes, verificação de que falha de um destinatário não derruba o lote inteiro (retry/backoff).

### **Fase 5 — Hardening e Observabilidade**
- Rate limiting revisado em todos os endpoints sensíveis.
- Logs de auditoria completos (exclusão de artigo, elevação de permissão, remoção de usuário).
- Revisão de exposição de dados nos Resources (campos sensíveis nunca vazam, ex.: e-mail de terceiros).
- Documentação OpenAPI/Swagger gerada e publicada.
- Testes de carga básicos em endpoints de listagem paginada.
- Revisão de cobertura de testes (mínimo 80% em Services/Controllers, conforme `CONSTITUTION.md`).

### **Fase 6 — Extensões Futuras (backlog, fora do escopo inicial)**
- Notificação ao autor sobre novos comentários (RF07.4).
- Múltiplos provedores sociais (Facebook, GitHub).
- Sistema de tags/categorias mais robusto com busca full-text.
- Métricas de leitura/analytics de artigos.
- Webhooks para eventos do blog (novo artigo publicado, nova curtida etc.).

---

## 10. Dependências Externas

| Dependência | Uso |
|---|---|
| Google OAuth (Console de Desenvolvedor) | Login social |
| Provedor SMTP (ex.: Mailgun, SES, Postmark) | Envio de e-mails transacionais e newsletter |
| Redis | Fila de jobs em produção |
| S3 ou disco local | Armazenamento de imagens de capa de artigos |

## 11. Riscos e Mitigações

| Risco | Mitigação |
|---|---|
| Volume alto de newsletter derrubando performance da fila | Envio em chunks + fila dedicada `newsletter` separada de `emails` transacionais |
| Complexidade de permissões granulares virar gargalo de manutenção | Uso de `spatie/laravel-permission` + seeders versionados + testes de matriz de permissão |
| Vazamento de dados sensíveis via Resources mal configurados | Revisão obrigatória de Resource em code review (checklist na Fase 5) |
| Abuso de criação de comentários/curtidas (spam/bot) | Rate limiting + validação de autenticação em todas as rotas de engajamento |

---

Este PRD deve ser revisado ao final de cada fase; ajustes de escopo dentro de uma fase precisam ser refletidos aqui antes do início da fase seguinte.