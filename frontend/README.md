# Frontend - URL Shortener

Interface moderna e responsiva construída com Vue 3 para o sistema de encurtamento de URLs.

## Tech Stack

- **Framework**: [Vue 3](https://vuejs.org/) (Composition API)
- **Build Tool**: [Vite](https://vitejs.dev/)
- **Linguagem**: [TypeScript](https://www.typescriptlang.org/)
- **Roteamento**: [Vue Router](https://router.vuejs.org/)
- **HTTP Client**: [Axios](https://axios-http.com/)

---

## PT-BR

### Desenvolvimento Local

#### Pré-requisitos
- Node.js (v18+)
- npm ou yarn

#### Instalação
```bash
# Instalar dependências
npm install

# Iniciar servidor de desenvolvimento
npm run dev
```

### 🐋 Rodando com Docker
O frontend está configurado para rodar via Docker Compose na raiz do projeto.

```bash
# A partir da raiz do projeto
docker compose up -d --build web
```

### Estrutura de Pastas
- `src/views`: Páginas principais (Login, Dashboard, Profile, etc.)
- `src/components`: Componentes reutilizáveis
- `src/services`: Integração com a API
- `src/router`: Configuração de rotas

---

## EN

### Local Development

#### Prerequisites
- Node.js (v18+)
- npm or yarn

#### Installation
```bash
# Install dependencies
npm install

# Start development server
npm run dev
```

### Running with Docker
The frontend is configured to run via Docker Compose in the project root.

```bash
# From the project root
docker compose up -d --build web
```

### Project Structure
- `src/views`: Main pages (Login, Dashboard, Profile, etc.)
- `src/components`: Reusable components
- `src/services`: API integration
- `src/router`: Routing configuration
