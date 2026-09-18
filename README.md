# Sistema de Controle de Chamados Internos

Aplicação web para registrar e acompanhar solicitações internas de suporte, com prioridade, status, responsável e distribuição automática por carga de trabalho.

Laravel 12 · Inertia.js 2 · Vue 3 · Tailwind CSS 4 · SQLite · Pest

---

## Instalação — passo a passo

Dentro da pasta do projeto (`cd controle-chamados`), siga **nesta ordem, um comando de cada vez**:

**Passo 1 — instalar as dependências do PHP**
```bash
composer install
```
Não deve terminar com "Problem 1" ou "could not be resolved". Se aparecer, volte no `REQUISITOS.txt` — normalmente falta uma extensão.

**Passo 2 — instalar as dependências do JavaScript**
```bash
npm install
```

**Passo 3 — configurar o ambiente**
```bash
cp .env.example .env
php artisan key:generate
```

**Passo 4 — criar o banco de dados**
```bash
touch database/database.sqlite
```

**Passo 5 — criar as tabelas e popular com dados de exemplo**
```bash
php artisan migrate --seed
```
Esse comando precisa mostrar as migrations `create_responsaveis_table` e `create_chamados_table` sendo criadas. Se der erro `could not find driver`, a extensão `php-sqlite3` não está instalada — volte ao `REQUISITOS.txt`.

**Passo 6 — confirmar que está tudo certo**
```bash
php artisan test
```
Deve terminar com todos os testes em verde (`PASS`). Só siga para o próximo passo se isso funcionar — é a garantia de que banco, PHP e dependências estão corretos antes de abrir no navegador.

**Passo 7 — subir a aplicação**

Abra **dois terminais**, os dois dentro da pasta `controle-chamados`, e deixe ambos rodando ao mesmo tempo:

```bash
# Terminal 1
php artisan serve
```

```bash
# Terminal 2
npm run dev
```

**Passo 8 — acessar**

Abra no navegador: **http://localhost:8000**

(Não abra a porta `:5173` diretamente — ela é só o servidor de assets do Vite, não a aplicação.)

---

## Problemas comuns

| Erro que aparece | O que fazer |
| --- | --- |
| `could not find driver` (ao rodar migrate ou test) | `sudo apt install php-sqlite3` e rode o comando de novo |
| `Your requirements could not be resolved... ext-xml` | `sudo apt install php-xml` e rode `composer install` de novo |
| `require(.../vendor/autoload.php): Failed to open` | O `composer install` não terminou com sucesso — rode-o de novo e leia o erro que ele mostrar |
| `npm ERR! enoent ... package.json` | Você não está dentro da pasta `controle-chamados`. Rode `cd controle-chamados` antes |
| Página carrega sem nenhum estilo (CSS) | O `npm run dev` não está rodando junto com o `php artisan serve` — confira o Terminal 2 |
| Erro 500 na página / `no such table: chamados` | As migrations não rodaram. Rode `php artisan migrate --seed` (Passo 5) |
| `No application encryption key has been specified` | Rode `php artisan key:generate` |

Se um erro não estiver nessa tabela, rode `tail -n 80 storage/logs/laravel.log` e leia a mensagem logo após `[previous exception]` — ela costuma dizer exatamente o que falta.

### Comandos úteis do dia a dia

| Para quê | Comando |
| --- | --- |
| Recriar o banco do zero (apaga tudo e semeia de novo) | `php artisan migrate:fresh --seed` |
| Rodar só os testes da distribuição automática | `php artisan test --filter=DistribuicaoAutomatica` |
| Gerar os assets para produção | `npm run build` |

---

## O que o sistema faz

As solicitações internas chegavam por WhatsApp, e-mail ou presencialmente e eram anotadas manualmente — difícil acompanhar e fácil sobrecarregar uma pessoa só. O sistema centraliza tudo em um lugar:

- abrir, editar, visualizar e excluir chamados;
- listagem com busca, filtros por status/prioridade/responsável, ordenação e paginação;
- escolher o responsável manualmente **ou** deixar o sistema distribuir automaticamente;
- redistribuir um chamado já existente;
- painel com indicadores e a carga atual de cada responsável.

### Telas

| Rota | O que é |
| --- | --- |
| `/chamados` | Listagem e acompanhamento (tela inicial) |
| `/chamados/create` | Abertura de chamado |
| `/chamados/{id}` | Detalhe, com redistribuição e exclusão |
| `/chamados/{id}/edit` | Edição |
| `/painel` | Indicadores e distribuição por responsável |

---

## Decisões de negócio

### Quais status contam como "em aberto"

| Status | Em aberto? |
| --- | --- |
| Aberto | sim |
| Em andamento | sim |
| Resolvido | não |
| Fechado | não |

A definição fica em um único lugar — `App\Enums\StatusChamado::emAbertoCases()` — e é usada pelo service de distribuição, pelos scopes do model e pelo painel. Mudar a regra é mudar esse método.

### Como funciona a distribuição automática

Ao abrir um chamado, o usuário escolhe um responsável ou marca "Distribuir automaticamente". No modo automático, `App\Services\DistribuicaoAutomatica::proximoResponsavel()`:

1. considera apenas responsáveis **ativos**;
2. conta, para cada um, os chamados **aberto** ou **em andamento**;
3. escolhe quem tiver a **menor** contagem.

A contagem é feita pelo banco (`withCount` filtrado por status), então reflete sempre o estado atual — não existe contador denormalizado para dessincronizar. A criação roda dentro de uma transação, então escolher o responsável e gravar o chamado é atômico. Sem nenhum responsável ativo, o service lança `RuntimeException` em vez de criar um chamado órfão em silêncio.

### Como empates são resolvidos

Carga igual entre dois ou mais responsáveis: vence o **menor `id`**, ou seja, o cadastro mais antigo. É determinístico, não depende de fuso horário nem da ordem de inserção dos chamados, e está expresso na própria consulta: `orderBy('chamados_em_aberto_count')->orderBy('id')`. Coberto pelo teste *desempata pelo menor id quando a carga é igual*.

### Outras decisões

- **Atribuição automática só na abertura.** Na edição o responsável é sempre explícito; distribuir automaticamente um chamado existente virou uma ação própria na tela de detalhe, para que salvar uma edição nunca troque o responsável sem o usuário pedir.
- **Responsáveis têm flag `ativo`.** Inativos continuam no histórico dos chamados antigos, mas não recebem novas atribuições nem aparecem nos formulários — evita excluir cadastros.
- **Ordenação por prioridade** usa a urgência real (alta > média > baixa), não a ordem alfabética do valor gravado.
- **Sem CRUD de responsáveis** e **sem autenticação**, conforme o enunciado. Em produção, autenticação seria a primeira coisa a adicionar.

---

## Stack e arquitetura

| Camada | Tecnologia |
| --- | --- |
| Backend | PHP 8.2+ / Laravel 12 |
| Frontend | Vue 3 (Composition API, `<script setup>`) |
| Integração | Inertia.js 2 |
| Estilos | Tailwind CSS 4 |
| Banco | SQLite |
| Build | Vite 6 |
| Testes | Pest 3 (sobre PHPUnit) |

**Por que Laravel + Inertia + Vue.** É a stack citada pela empresa e, para um sistema deste porte, também a mais simples: o Inertia dispensa construir e versionar uma API REST só para alimentar o próprio frontend. Os controllers devolvem props direto para os componentes Vue, o roteamento continua no Laravel, validação e CSRF continuam sendo os do framework — e a interface ainda é uma SPA. API separada + SPA exigiria autenticação por token, camada de cliente HTTP e rotas duplicadas, sem ganho nenhum aqui.

**Por que SQLite.** O avaliador clona e roda sem instalar servidor de banco. O projeto usa só recursos comuns a qualquer banco relacional, então migrar para MySQL ou PostgreSQL é trocar variáveis no `.env`.

**Por que esta arquitetura.** A aplicação é pequena, então evitei repositórios, DTOs de entrada e camadas que não pagariam o próprio custo. A separação é a do próprio Laravel, levada a sério:

- **Enums PHP** (`Prioridade`, `StatusChamado`) concentram valores válidos, rótulos e a definição de "em aberto". Nenhum lugar do sistema escreve `'em_andamento'` como string solta.
- **Form Requests** cuidam de toda a validação. Os controllers nunca leem entrada não validada.
- **`DistribuicaoAutomatica`** isola a regra mais importante do desafio. Não conhece HTTP nem interface — por isso é testável direto, sem passar por rota.
- **Controllers finos:** montam a consulta, delegam a regra, renderizam a página.
- **Scopes no model** (`emAberto`, `busca`, `comCargaEmAberto`) evitam repetir a mesma query em controller, service e painel.
- **Resources** padronizam o formato enviado ao frontend, deixando as páginas Vue sem lógica de formatação.
- **Componentes Vue reutilizáveis:** o mesmo `ChamadoForm` serve para criar e editar; badges, paginação e estado vazio são compartilhados.

Nada de Docker, filas, cache ou pacotes extras — nada disso foi pedido e cada um adicionaria atrito na avaliação.

### Estrutura

```
app/
├── Enums/              Prioridade, StatusChamado (valores, rótulos, regra de "em aberto")
├── Http/
│   ├── Controllers/    ChamadoController, DashboardController
│   ├── Middleware/     HandleInertiaRequests (props compartilhadas)
│   ├── Requests/       StoreChamadoRequest, UpdateChamadoRequest
│   └── Resources/      ChamadoResource, ResponsavelResource
├── Models/             Chamado, Responsavel (relações e scopes)
└── Services/           DistribuicaoAutomatica (menor carga + empate)

database/
├── factories/          ChamadoFactory, ResponsavelFactory
├── migrations/         responsaveis, chamados
└── seeders/            ResponsavelSeeder, ChamadoSeeder

resources/js/
├── Components/         ChamadoForm, StatusBadge, PrioridadeBadge,
│                       Paginacao, EstadoVazio, MensagemFlash, CampoFormulario
├── Layouts/            AppLayout.vue
├── Pages/              Dashboard.vue, Chamados/{Index,Create,Edit,Show}.vue
└── utils/              debounce.js

tests/
├── Feature/            CriacaoDeChamado, ValidacaoDeChamado, GestaoDeChamado
└── Unit/                DistribuicaoAutomatica
```

### Banco de dados

**`responsaveis`** — `id`, `nome`, `email` (único), `ativo`, timestamps.
**`chamados`** — `id`, `titulo`, `descricao`, `prioridade`, `status`, `responsavel_id`, `aberto_em`, timestamps.

`chamados.responsavel_id` é foreign key para `responsaveis.id` com `nullOnDelete` — um chamado histórico não desaparece se um responsável for removido. Índices em `status`, `prioridade` e no par `(responsavel_id, status)`, este último atendendo exatamente à consulta da distribuição automática.

---

## Testes

| Arquivo | O que verifica |
| --- | --- |
| `tests/Unit/DistribuicaoAutomaticaTest.php` | menor carga vence; resolvidos/fechados não contam; empate pelo menor id; inativos ignorados; erro explícito sem responsáveis |
| `tests/Feature/CriacaoDeChamadoTest.php` | criação com atribuição manual; `aberto_em` automático; criação com atribuição automática |
| `tests/Feature/ValidacaoDeChamadoTest.php` | campos obrigatórios; prioridade/status inválidos; responsável obrigatório no modo manual; responsável inexistente ou inativo |
| `tests/Feature/GestaoDeChamadoTest.php` | listagem; filtro por status; visualização; edição; redistribuição; exclusão |

---

## Segurança

- Toda entrada passa por Form Requests; a validação do frontend é só conveniência.
- Consultas usam Eloquent/query builder — nada de SQL concatenado com entrada do usuário. O único `orderByRaw` do projeto monta a cláusula a partir do enum e de uma lista branca de direção.
- O parâmetro de ordenação é validado contra uma lista branca antes de chegar ao banco.
- Proteção CSRF ativa em todas as rotas `web` (padrão do Laravel, usado pelo Inertia).
- O Vue escapa a interpolação por padrão; o único `v-html` é nos rótulos de paginação gerados pelo próprio Laravel.
- IDs resolvidos por route model binding, que devolve 404 para registros inexistentes.
- Nenhuma credencial no código: tudo vem do `.env`, que está no `.gitignore`. O `.env.example` não tem segredos.

---

## Referências

- [Laravel 12](https://laravel.com/docs/12.x) — Eloquent, migrations, validation, testing, route model binding
- [Inertia.js 2](https://inertiajs.com/) — adapters Laravel e Vue 3, `useForm`, paginação
- [Vue 3](https://vuejs.org/guide/introduction.html) — Composition API, `<script setup>`
- [Tailwind CSS 4](https://tailwindcss.com/docs) — plugin Vite, utilitários
- [Pest 3](https://pestphp.com/docs/installation) e [Laravel Testing](https://laravel.com/docs/12.x/testing)
- [Vite](https://vite.dev/guide/) e [laravel-vite-plugin](https://github.com/laravel/vite-plugin)
- [PHP Enums](https://www.php.net/manual/en/language.enumerations.php)

---

## Commits sugeridos

1. `chore: estrutura inicial do projeto Laravel + Inertia + Vue`
2. `feat: enums de prioridade e status`
3. `feat: migrations, models e relacionamento chamado/responsável`
4. `feat: seeders e factories`
5. `feat: regra de distribuição automática por menor carga`
6. `feat: CRUD de chamados com validação`
7. `feat: listagem com busca, filtros, ordenação e paginação`
8. `feat: interface Vue e componentes reutilizáveis`
9. `feat: painel de indicadores e carga por responsável`
10. `test: cobertura das regras de negócio e do CRUD`
11. `docs: README com instalação e decisões de negócio`
# equipe_codificar
