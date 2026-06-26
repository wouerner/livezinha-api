# Livezinha API (Backend) 🖥️

Este é o backend da plataforma **Livezinha**, uma API RESTful robusta desenvolvida em **Laravel 13.x** e **PHP 8.3+**. Ela gerencia o banco de dados MySQL, autenticação de administrador com Sanctum, processamento de filas de eventos e disponibiliza endpoints públicos e protegidos para a SPA frontend.

---

## 🛠️ Requisitos de Sistema

- **PHP 8.3+** (opcional para execução local, obrigatório caso execute sem Docker)
- **Composer**
- **Docker** & **Docker Compose** (recomendado via Laravel Sail)

---

## 🚀 Configuração e Instalação

1. Vá para o diretório do backend:
   ```bash
   cd backend
   ```

2. Copie o arquivo `.env.example` e crie o seu `.env`:
   ```bash
   cp .env.example .env
   ```

3. Instale as dependências com Composer:
   ```bash
   composer install
   ```

4. Suba o ambiente Docker Sail:
   ```bash
   ./vendor/bin/sail up -d
   ```

5. Execute a migração e população do banco de dados (sementeira):
   ```bash
   ./vendor/bin/sail artisan migrate:fresh --seed
   ```

6. Para rodar o ambiente de desenvolvimento completo (servidor web local, escuta de filas, logs em tempo real e assets do Laravel):
   ```bash
   composer dev
   ```

---

## ⚙️ Comandos do Composer e Artisan

| Comando | Descrição |
|---|---|
| `composer setup` | Executa a instalação completa de dependências, migrações e sementes do projeto. |
| `composer dev` | Inicia simultaneamente: `artisan serve` (servidor), `queue:listen` (filas), `pail` (logs) e `npm run dev` (Vite Laravel). |
| `composer test` | Limpa as configurações em cache e executa a suíte de testes automatizados (`artisan test`). |
| `php artisan test --filter=NomeDoTeste` | Executa um teste específico baseado no nome do método ou classe. |
| `vendor/bin/phpunit tests/Feature/ExemploTest.php` | Roda diretamente um arquivo de teste específico usando o PHPUnit. |
| `./vendor/bin/pint` | Executa o Laravel Pint para formatar e padronizar o código conforme as regras PSR-12. |
| `php artisan migrate` | Executa as novas migrações no banco de dados. |
| `php artisan migrate:fresh --seed` | Limpa totalmente o banco de dados, recria as tabelas e adiciona dados de teste. |

---

## 🗄️ Modelos e Banco de Dados

O banco de dados de desenvolvimento utiliza **MySQL** (configurado através do Docker Sail). Os testes automatizados utilizam **SQLite em memória** para garantir máxima velocidade.

### Entidades do Banco de Dados

1. **`LiveStream` (Transmissão)**
   - Representa uma live agendada ou ativa.
   - Possui relacionamento `hasMany(Question)`.
   - **Campos**: `id`, `title`, `streamer_name`, `live_url`, `scheduled_at`, `status`, `created_at`, `updated_at`.

2. **`Question` (Pergunta)**
   - Representa uma pergunta enviada por um espectador para uma transmissão específica.
   - Possui relacionamento `belongsTo(LiveStream)`.
   - **Campos**: `id`, `live_stream_id`, `name`, `tiktok_handle`, `question_text`, `passcode`, `status`, `is_tagged`, `votes_count`, `created_at`, `updated_at`.
   - *Nota*: O campo `passcode` é gerado automaticamente como uma combinação única em português de substantivo-adjetivo (ex: `gato-azul`), permitindo que o espectador altere ou exclua sua pergunta posteriormente sem precisar criar uma conta de acesso.

3. **`Note` (Nota)**
   - Entidade isolada para anotações rápidas do streamer durante a live.
   - **Campos**: `id`, `title`, `content`, `created_at`, `updated_at`.

---

## 📡 API Endpoints (`routes/api.php`)

Todos os endpoints da API possuem o prefixo automático `/api` e respondem estritamente em formato JSON.

### Endpoints Públicos (Sem Autenticação)
- `GET /api/ping` — Verificação de saúde da conexão (retorna status 'connected').
- `GET /api/lives` — Lista todas as lives disponíveis.
- `GET /api/lives/active` — Retorna os dados da transmissão atualmente ativa.
- `GET /api/lives/active/question` — Retorna a pergunta que está atualmente em exibição no overlay (usado pelo OBS).
- `GET /api/lives/{liveStream}/questions/public` — Lista as perguntas públicas de uma determinada live.
- `POST /api/questions` — Permite que espectadores enviem perguntas para uma live.
- `POST /api/questions/{question}/vote` — Permite que espectadores votem em perguntas enviadas.

### Endpoints de Administração (Protegidos por Sanctum Auth)
Requerem o cabeçalho `Authorization: Bearer <token>` obtido no login.
- `POST /api/login` — Autenticação do administrador (com limitador de tentativas).
- `POST /api/logout` — Revogação do token e encerramento de sessão.
- `GET /api/user` — Retorna as informações do administrador autenticado.
- **CRUD de Lives**:
  - `POST /api/lives` — Criar nova live.
  - `GET /api/lives/{liveStream}` — Visualizar detalhes de uma live.
  - `PUT /api/lives/{liveStream}` — Atualizar uma live.
  - `DELETE /api/lives/{liveStream}` — Deletar uma live.
- **CRUD de Perguntas**:
  - `GET /api/questions` — Listar perguntas (para moderação).
  - `GET /api/questions/{question}` — Visualizar detalhes da pergunta.
  - `PUT /api/questions/{question}` — Modificar status/dados da pergunta (ex: marcar como respondida, destacar ou marcar como ativa).
  - `DELETE /api/questions/{question}` — Remover pergunta.

---

## 🧪 Testes Automatizados

- Os testes de unidade estendem `PHPUnit\Framework\TestCase` (sem inicializar o framework Laravel).
- Os testes de funcionalidade (Feature) estendem `Tests\TestCase`.
- Para rodar toda a suíte de testes em ambiente limpo:
  ```bash
  composer test
  ```
