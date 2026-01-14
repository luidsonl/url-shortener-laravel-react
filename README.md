# URL Shortener - Laravel 12 & Vue.js

Sistema de encurtamento de URLs focado em performance e escalabilidade.

## Estrutura do Projeto / Project Structure

* [Backend (API)](./backend/README.md): Laravel 12, Redis, PostgreSQL.
* [Frontend](./frontend/README.md): Vue.js 3 (Em progresso / In progress).

---

## PT-BR
## Principais funcionalidades
* Performance: Redirecionamento sub-milissegundo com cache em Redis.
* Resiliência: Processamento de métricas e contagem de acessos via Jobs em segundo plano.
* Segurança: Autenticação via Sanctum/JWT, verificação de e-mail e recuperação de senha.
* Gestão: Controle de expiração de links e painel administrativo completo.


## EN
## Key Features
* Performance: Sub-millisecond redirection using Redis caching.
* Resilience: Metrics and access counting processed via background Jobs.
* Security: Sanctum/JWT authentication, email verification, and password recovery.
* Management: Link expiration control and comprehensive admin dashboard.