# Plano de Construção Incremental - SisFrota Municipal

Este documento define o roteiro de desenvolvimento em fases incrementais para o sistema **SisFrota Municipal**, estruturado estritamente com base nos requisitos, regras de negócio e arquitetura estabelecidos no `docs/FSD.md` e `docs/DESIGN.md`.

---

## Visão Geral das Fases

```
Fase 1: Infraestrutura e Estrutura Base do Projeto
Fase 2: Banco de Dados, Persistência e Migrations
Fase 3: Autenticação, Sessão e Controle de Acesso (RBAC)
Fase 4: Cadastros Base (Secretarias, Categorias de Manutenção e Usuários)
Fase 5: Módulo de Veículos e Ficha Individual
Fase 6: Módulo de Abastecimentos e Validação Bloqueante de KM
Fase 7: Módulo de Manutenções e Gestão de Status
Fase 8: Dashboard Gerencial e Indicadores
Fase 9: Módulo de Relatórios e Exportações (CSV e PDF)
Fase 10: Auditoria, Logs de Sistema, Segurança e Ajustes Finais
```

---

## Detalhamento das Fases

### Fase 1 - Infraestrutura e Estrutura Base do Projeto
* **Objetivo:** Estabelecer a estrutura inicial de diretórios, roteador principal, arquivo de configuração PHP compilado, proteções de segurança `.htaccess`, constante `SYS_ENTRY` e cópia dos insumos visuais públicos.
* **Checklist de Tarefas:**
  - [x] Criar estrutura de pastas do projeto (`config/`, `app/`, `assets/`, `database/`, `logs/`).
  - [x] Criar arquivos `.htaccess` com `Require all denied` nas pastas internas (`config/`, `app/`, `database/`, `logs/`).
  - [x] Criar o arquivo `.htaccess` raiz para reescrita de URLs amigáveis e proteção.
  - [x] Criar o arquivo `config/config.php` (sem o uso de `.env`, definindo credenciais MySQL e timezone `America/Sao_Paulo`).
  - [x] Criar o ponto de entrada único `index.php` com constante `SYS_ENTRY`, timezone e tratamentos básicos.
  - [x] Copiar a imagem `docs/logo_Crateus.png` para `assets/img/logo.png`.
  - [x] Preparar pastas de bibliotecas locais em `assets/js/vendor/` e `assets/css/`.
* **Critérios de Pronto:**
  - Requisições enviadas a pastas protegidas retornam HTTP 403 / Acesso Negado.
  - Navegação para `index.php` carrega sem erros de sintaxe ou warnings de timezone.
  - Logo oficial copiado e acessível em `assets/img/logo.png`.
* **Arquivos e Pastas Alterados:** `index.php`, `.htaccess`, `config/`, `app/`, `assets/`, `database/`, `logs/`.
* **Observações de Dependência:** Nenhuma.

---

### Fase 2 - Banco de Dados, Persistência e Migrations
* **Objetivo:** Implementar a infraestrutura de banco de dados MySQL/MariaDB, a classe singleton de conexão PDO (`Database.php`), o executor CLI de migrations (`migrate.php`) e os scripts versionados de criação de tabelas e carga inicial (seed).
* **Checklist de Tarefas:**
  - [ ] Criar a classe singleton de conexão `app/models/Database.php` utilizando PDO e Prepared Statements.
  - [ ] Criar a estrutura e o executor seguro CLI `database/migrate.php` validando `SYS_ENTRY`.
  - [ ] Criar a migration `001_create_system_tables.sql` (tabelas: `migrations`, `users`, `secretariats`, `vehicles`, `maintenance_categories`, `fuelings`, `maintenances`, `system_logs`).
  - [ ] Criar a migration `002_seed_initial_data.sql` (usuário admin padrão `admin`/`admin123` com hash BCrypt, secretarias e categorias iniciais).
  - [ ] Testar a execução e idempotência das migrations via CLI (`php database/migrate.php`).
* **Critérios de Pronto:**
  - Conexão PDO executando em modo UTF-8 (`utf8mb4`).
  - Script de migration executado com sucesso via CLI criando todas as 8 tabelas e alimentando o usuário admin inicial.
  - Execução repetida do script de migration não causa erros nem duplica registros.
* **Arquivos e Pastas Alterados:** `app/models/Database.php`, `database/migrate.php`, `database/migrations/`.
* **Observações de Dependência:** Depende da Fase 1.

---

### Fase 3 - Autenticação, Sessão e Controle de Acesso (RBAC)
* **Objetivo:** Desenvolver o módulo de login/logout, controle de sessão segura HTTP Cookies (HTTPOnly, SameSite) e a matriz de autorização por perfil (RBAC: `ADMIN`, `OPERATOR`, `MANAGER`).
* **Checklist de Tarefas:**
  - [ ] Criar o Model `app/models/User.php` para consulta e autenticação de usuários via BCrypt.
  - [ ] Criar o `app/controllers/AuthController.php` com ações de login, logout e regeneração de ID de sessão (`session_regenerate_id(true)`).
  - [ ] Criar a View de Login em `app/views/auth/login.php` seguindo os padrões do `docs/DESIGN.md`.
  - [ ] Implementar verificação de RBAC no backend dos Controllers com resposta HTTP 403 para acessos não autorizados.
  - [ ] Criar o template base de layout (`app/views/layouts/header.php`, `sidebar.php`, `footer.php`) com renderização dinâmica de menus conforme o perfil logado.
* **Critérios de Pronto:**
  - Login efetuado com sucesso para a conta `admin`.
  - Tentativas com credenciais incorretas exibem mensagem genérica segura.
  - Sessão expirada ou acesso direto a rotas protegidas redireciona para `/login`.
  - Sidebar exibe apenas opções permitidas para o perfil logado.
* **Arquivos e Pastas Alterados:** `app/models/User.php`, `app/controllers/AuthController.php`, `app/views/auth/`, `app/views/layouts/`.
* **Observações de Dependência:** Depende da Fase 2.

---

### Fase 4 - Cadastros Base (Secretarias, Categorias de Manutenção e Usuários)
* **Objetivo:** Desenvolver as interfaces e lógicas de CRUD para secretarias municipais, categorias de manutenção mecânica e gestão de contas de acesso de servidores.
* **Checklist de Tarefas:**
  - [ ] Criar o Model `app/models/Secretariat.php` e Controller `app/controllers/SecretariatController.php`.
  - [ ] Criar a View de gestão de secretarias `app/views/settings/secretariats.php`.
  - [ ] Criar o Model `app/models/MaintenanceCategory.php` e Controller `app/controllers/MaintenanceCategoryController.php`.
  - [ ] Criar a View de categorias de manutenção `app/views/settings/categories.php`.
  - [ ] Criar o Controller `app/controllers/UserController.php` e Views de gestão de usuários `app/views/settings/users.php`.
  - [ ] Restringir acesso a estas telas exclusivamente ao perfil `ADMIN`.
* **Critérios de Pronto:**
  - Administrador consegue cadastrar, editar e inativar secretarias, categorias e usuários.
  - Operadores e Gestores recebem erro HTTP 403 se tentarem acessar as telas de configurações.
* **Arquivos e Pastas Alterados:** `app/models/`, `app/controllers/`, `app/views/settings/`.
* **Observações de Dependência:** Depende da Fase 3.

---

### Fase 5 - Módulo de Veículos e Ficha Individual
* **Objetivo:** Implementar o cadastro, listagem, inativação por Soft Delete e a Ficha Individual detalhada de cada veículo da frota municipal.
* **Checklist de Tarefas:**
  - [ ] Criar o helper de formatação `app/helpers/FormatHelper.php` para formatação de placas (antigas e Mercosul) e moeda.
  - [ ] Criar o Model `app/models/Vehicle.php` com regras de Soft Delete (`is_deleted`) e validação de placa única.
  - [ ] Criar o Controller `app/controllers/VehicleController.php`.
  - [ ] Criar a View de listagem de veículos `app/views/vehicles/index.php` com busca e filtros por secretaria/status.
  - [ ] Criar a View de formulário de veículo `app/views/vehicles/form.php` (criação para Admin/Operador, edição para Admin).
  - [ ] Criar a View de Ficha Individual do Veículo `app/views/vehicles/show.php` com abas para histórico de abastecimentos e manutenções.
* **Critérios de Pronto:**
  - Veículos cadastrados exibem placas formatadas corretamente pelo `FormatHelper`.
  - Inativação de veículo realiza Soft Delete (`is_deleted = 1`) sem remover registros históricos do banco.
  - Ficha do veículo exibe a quilometragem atual rodada e consumo acumulado.
* **Arquivos e Pastas Alterados:** `app/helpers/FormatHelper.php`, `app/models/Vehicle.php`, `app/controllers/VehicleController.php`, `app/views/vehicles/`.
* **Observações de Dependência:** Depende da Fase 4.

---

### Fase 6 - Módulo de Abastecimentos e Validação Bloqueante de KM
* **Objetivo:** Construir a rotina de lançamentos operacionais de abastecimento de combustível com a trava rígida de quilometragem e cálculo automático de consumo médio ($\text{KM/L}$).
* **Checklist de Tarefas:**
  - [ ] Criar o Model `app/models/Fueling.php`.
  - [ ] Criar a validação bloqueante no `Fueling` Model: rejeitar lançamentos onde a $\text{KM}_{\text{informada}} \le \text{KM}_{\text{cadastrada}}$.
  - [ ] Criar o cálculo automático de consumo médio: $\text{Consumo} = \frac{\text{KM}_{\text{atual}} - \text{KM}_{\text{anterior}}}{\text{Litros}_{\text{atual}}}$ a partir do 2º abastecimento.
  - [ ] Criar o Controller `app/controllers/FuelingController.php`.
  - [ ] Criar a View de listagem `app/views/fuelings/index.php` e formulário `app/views/fuelings/form.php`.
  - [ ] Atualizar automaticamente a `current_km` na tabela `vehicles` após inserção válida.
* **Critérios de Pronto:**
  - Lançamento com KM inferior ou igual é rejeitado no backend e exibe mensagem de erro clara em vermelho.
  - O 1º abastecimento grava `avg_kml` como `NULL`; a partir do 2º abastecimento, o valor é calculado e gravado corretamente.
  - A KM do veículo é atualizada no banco de dados a cada abastecimento válido.
* **Arquivos e Pastas Alterados:** `app/models/Fueling.php`, `app/controllers/FuelingController.php`, `app/views/fuelings/`.
* **Observações de Dependência:** Depende da Fase 5.

---

### Fase 7 - Módulo de Manutenções e Gestão de Status
* **Objetivo:** Registrar as ordens de serviço de manutenção mecânica (preventivas/corretivas) e acompanhar a situação operacional dos veículos nas oficinas.
* **Checklist de Tarefas:**
  - [ ] Criar o Model `app/models/Maintenance.php`.
  - [ ] Criar o Controller `app/controllers/MaintenanceController.php`.
  - [ ] Criar as Views `app/views/maintenances/index.php` e `app/views/maintenances/form.php`.
  - [ ] Implementar transição de status de manutenção (`SCHEDULED`, `IN_PROGRESS`, `PENDING`, `COMPLETED`) e atualização correspondente do status do veículo (`MAINTENANCE` / `ACTIVE`).
* **Critérios de Pronto:**
  - Lançamento de manutenção com status *Em Andamento* altera o status do veículo para `MAINTENANCE`, ocultando-o de seletores de novos abastecimentos.
  - Conclusão da manutenção restaura o status do veículo para `ACTIVE`.
* **Arquivos e Pastas Alterados:** `app/models/Maintenance.php`, `app/controllers/MaintenanceController.php`, `app/views/maintenances/`.
* **Observações de Dependência:** Depende da Fase 5.

---

### Fase 8 - Dashboard Gerencial e Indicadores
* **Objetivo:** Desenvolver o painel principal com os 4 cartões de métricas consolidadas do mês e os gráficos interativos de custos e manutenções.
* **Checklist de Tarefas:**
  - [ ] Criar o Controller `app/controllers/DashboardController.php`.
  - [ ] Implementar consultas agregadas para as 4 métricas: Gasto Total no Mês, Gastos com Combustível, KM Rodado e Veículos Indisponíveis.
  - [ ] Integrar a biblioteca Chart.js local em `assets/js/vendor/chart.min.js`.
  - [ ] Desenvolver o script JS de inicialização dos gráficos de evolução de gastos mensais e proporção de manutenções.
  - [ ] Criar a View `app/views/dashboard/index.php` com seletor de mês/ano de referência.
* **Critérios de Pronto:**
  - Dashboard carrega valores consolidados reais do mês selecionado.
  - Gráficos renderizam corretamente utilizando Chart.js local sem dependência de internet.
* **Arquivos e Pastas Alterados:** `app/controllers/DashboardController.php`, `app/views/dashboard/`, `assets/js/`.
* **Observações de Dependência:** Depende das Fases 6 e 7.

---

### Fase 9 - Módulo de Relatórios e Exportações (CSV e PDF)
* **Objetivo:** Construir o módulo de emissão de relatórios oficiais filtrados com opções de exportação em planilha CSV (UTF-8 BOM) e documento formatado PDF (FPDF).
* **Checklist de Tarefas:**
  - [ ] Integrar a biblioteca FPDF localmente no projeto.
  - [ ] Criar o Controller `app/controllers/ReportController.php`.
  - [ ] Criar a View de filtros de relatórios `app/views/reports/index.php` (por tipo de registro, intervalo de datas e secretaria).
  - [ ] Implementar exportador de CSV com cabeçalho UTF-8 BOM (`\xEF\xBB\xBF`) e separador ponto e vírgula (`;`).
  - [ ] Implementar gerador de PDF via FPDF com cabeçalho oficial contendo a logo da Prefeitura (`assets/img/logo.png`), tabela estilizada e rodapé de emissão.
* **Critérios de Pronto:**
  - Download de arquivo CSV abre no Microsoft Excel com acentuação correta.
  - Gerador de PDF compila relatório com brasão da Prefeitura de Crateús, tabela formatada e dados filtrados.
* **Arquivos e Pastas Alterados:** `app/controllers/ReportController.php`, `app/views/reports/`, biblioteca FPDF em dependências locais.
* **Observações de Dependência:** Depende das Fases 6 e 7.

---

### Fase 10 - Auditoria, Logs de Sistema, Segurança e Ajustes Finais
* **Objetivo:** Concluir a infraestrutura de logs de auditoria/segurança, rotina de contingência em arquivo `logs/system_error.log`, validação CSRF/XSS rigorosa e testes de aceitação integrados.
* **Checklist de Tarefas:**
  - [ ] Criar o Model `app/models/SystemLog.php` com mecanismo de contingência para o arquivo `logs/system_error.log` em caso de erro de banco de dados.
  - [ ] Registrar logs de auditoria para tentativas de login, edições administrativas e bloqueios de KM.
  - [ ] Revisar sanitização global contra XSS (`htmlspecialchars`) e tokens CSRF em formulários POST.
  - [ ] Executar verificação detalhada da matriz de critérios de aceitação do `docs/FSD.md`.
* **Critérios de Pronto:**
  - Falha simulada de banco de dados dispara gravação de erro no arquivo `logs/system_error.log`.
  - 100% dos critérios de aceitação do FSD (Seção 26) validados e aprovados.
* **Arquivos e Pastas Alterados:** `app/models/SystemLog.php`, `logs/`, revisão geral em Controllers e Views.
* **Observações de Dependência:** Depende de todas as fases anteriores.
