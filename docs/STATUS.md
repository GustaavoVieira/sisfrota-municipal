# Estado do Projeto - SisFrota Municipal

**Última atualização:** 06/09/2026  
**Fase Atual:** Fase 9 Concluída  
**Próxima Fase:** Fase 10 - Auditoria, Logs de Sistema, Segurança e Ajustes Finais  

---

## Progresso por Fase

- [x] **Fase 1 - Infraestrutura e Estrutura Base do Projeto**
  - [x] Estrutura de diretórios criada (`config/`, `app/`, `assets/`, `database/`, `logs/`).
  - [x] Arquivos `.htaccess` de proteção aplicados nas pastas internas.
  - [x] Arquivo `.htaccess` raiz criado com mod_rewrite ativado.
  - [x] Configuração em código PHP `config/config.php` sem uso de `.env`.
  - [x] Ponto de entrada único `index.php` criado com constante `SYS_ENTRY` e fuso horário `America/Sao_Paulo`.
  - [x] Logotipo oficial copiado de `docs/logo_Crateus.png` para `assets/img/logo.png`.
  - [x] Log de contingência em arquivo `logs/system_error.log` inicializado.
  - [x] Repositório Git local inicializado e ramo principal definido como `main`.
  - [x] Arquivo `.gitignore` criado com regras para PHP, Apache, logs, dumps e OS.
  - [x] Arquivo `.gitattributes` criado para padronização de quebras de linha e arquivos binários.
  - [x] Arquivo `config/config.example.php` criado como modelo seguro sem segredos.
  - [x] Verificação de segurança realizada (nenhum segredo ou log sensível rastreado).
  - [x] Primeiro commit ("Estrutura inicial do projeto") realizado com sucesso.
  - [x] Configuração do repositório remoto no GitHub e sincronização realizada.

- [x] **Fase 2 - Banco de Dados, Persistência e Migrations**
  - [x] Criar classe singleton PDO `app/models/Database.php`.
  - [x] Criar executor CLI de migrations `database/migrate.php` protegido por `SYS_ENTRY`.
  - [x] Criar migration `001_create_system_tables.sql` (tabelas: `migrations`, `users`, `secretariats`, `vehicles`, `maintenance_categories`, `fuelings`, `maintenances`, `system_logs`).
  - [x] Criar migration `002_seed_initial_data.sql` (admin inicial, secretarias e categorias).
  - [x] Validar execução e idempotência do script CLI.

- [x] **Fase 3 - Autenticação, Sessão e Controle de Acesso (RBAC)**
  - [x] Criar Model `User.php` e `AuthController.php`.
  - [x] Criar View de Login `app/views/auth/login.php`.
  - [x] Implementar verificação de sessão e RBAC (HTTP 403) nos Controllers.
  - [x] Criar layouts base (`header.php`, `sidebar.php`, `footer.php`).
  - [x] Criar `AuthHelper.php` com sessão segura (HTTPOnly, SameSite, 30min), CSRF e RBAC.
  - [x] Criar `DashboardController.php` (stub) e `dashboard/index.php`.
  - [x] Criar tela de erro `app/views/errors/403.php`.
  - [x] Criar roteador central completo em `index.php` com stubs para Fases 4-9.
  - [x] Instalar Bootstrap 5.3.3 localmente (`assets/css/` e `assets/js/`).
  - [x] Criar `assets/css/custom.css` com sistema de design do DESIGN.md.
  - [x] Testar autenticação via CLI (4/4 testes passando).

- [x] **Fase 4 - Cadastros Base (Secretarias, Categorias de Manutenção e Usuários)**
  - [x] Criar Model `Secretariat.php`, `SecretariatController.php` e View `app/views/settings/secretariats.php`.
  - [x] Criar Model `MaintenanceCategory.php`, `MaintenanceCategoryController.php` e View `app/views/settings/categories.php`.
  - [x] Expandir Model `User.php`, criar `UserController.php` e View `app/views/settings/users.php`.
  - [x] Atualizar roteamento em `index.php` para `/settings/*` (secretariats, categories, users).
  - [x] Garantir validações de segurança (RBAC para ADMIN, tokens CSRF, sanitização contra XSS, hash BCrypt de senhas).
  - [x] Testar operações CRUD e integridade via script CLI.

- [x] **Fase 5 - Módulo de Veículos e Ficha Individual**
  - [x] Criar `FormatHelper.php` (formatação de placas tradicionais/Mercosul, moeda R$, números e datas).
  - [x] Criar Model `Vehicle.php` com suporte a Soft Delete (`is_deleted`), Hard Delete (com proteção de FK), unicidade de placas e histórico.
  - [x] Criar `VehicleController.php` e Views (`vehicles/index.php`, `vehicles/form.php` e `vehicles/show.php`).
  - [x] Atualizar roteamento central em `index.php` para o recurso `/vehicles` e suas ações.
  - [x] Garantir validações de segurança (RBAC para ADMIN, OPERATOR e MANAGER, tokens CSRF e XSS sanitização).
  - [x] Executar testes automatizados via CLI em `tests/test_fase5.php` (20/20 testes aprovados).

- [x] **Fase 6 - Módulo de Abastecimentos e Validação Bloqueante de KM**
  - [x] Criar Model `Fueling.php` com trava rígida de KM e cálculo de $\text{KM/L}$ a partir do 2º abastecimento.
  - [x] Criar `FuelingController.php` e Views (`fuelings/index.php` e `fuelings/form.php`).
  - [x] Atualização automática de `current_km` no veículo em cada abastecimento válido.
  - [x] Atualizar roteamento central em `index.php` para o recurso `/fuelings`.
  - [x] Executar testes automatizados via CLI em `tests/test_fase6.php` (15/15 testes aprovados).

- [x] **Fase 7 - Módulo de Manutenções e Gestão de Status**
  - [x] Criar Model `Maintenance.php` com CRUD completo, validações e sincronização de status do veículo.
  - [x] Adicionar métodos alias `findAll()`, `findAllActive()` e `findById()` em `MaintenanceCategory.php`.
  - [x] Criar `MaintenanceController.php` com ações `index`, `create`, `store`, `edit`, `update` e `delete`.
  - [x] Criar View de listagem `app/views/maintenances/index.php` com filtros por OS, veículo, categoria e status.
  - [x] Criar View de formulário `app/views/maintenances/form.php` (criação para Admin/Operador, edição para Admin/Operador).
  - [x] Implementar transição automática de status: OS em `IN_PROGRESS`/`PENDING` → veículo para `MAINTENANCE`; OS `COMPLETED` + sem pendências → veículo para `ACTIVE`.
  - [x] Atualizar roteamento central em `index.php` para o recurso `/maintenances` e suas ações.
  - [x] Atualizar `sidebar.php` para exibir "Manutenções" para perfis `ADMIN`, `OPERATOR` e `MANAGER`.
  - [x] Executar testes automatizados via CLI em `tests/test_fase7.php` (13/13 testes aprovados).

- [x] **Fase 8 - Dashboard Gerencial e Indicadores**
  - [x] Integrar a biblioteca Chart.js local minificada (`assets/js/vendor/chart.min.js`) para execução 100% offline.
  - [x] Adicionar helpers de meses em português no `FormatHelper.php` (`formatMonthName`, `getMonthShortName`).
  - [x] Adicionar suporte ao parâmetro `limit` nos métodos `findAll()` de `Fueling.php` e `Maintenance.php`.
  - [x] Implementar `DashboardController.php` completo com agregação das 4 métricas do mês (Gasto Total, Gastos com Combustível, KM Rodado e Veículos Indisponíveis), histórico de 6 meses e proporção de manutenções.
  - [x] Criar a View `app/views/dashboard/index.php` com seletor de mês de referência, 4 KPI cards, 2 gráficos Chart.js (Evolução de Custos e Situação das Manutenções) e tabelas de atividades recentes.
  - [x] Adicionar estilos responsivos e temas visuais do `docs/DESIGN.md` no `assets/css/custom.css`.
  - [x] Executar testes automatizados via CLI em `tests/test_fase8.php` (18/18 testes aprovados).

- [x] **Fase 9 - Módulo de Relatórios e Exportações (CSV e PDF)**
  - [x] Integrar a biblioteca FPDF 1.86 local compatível com PHP 8.x (`app/helpers/fpdf/fpdf.php`).
  - [x] Criar a classe `ReportPdfHelper.php` com cabeçalho institucional oficial (Brasão de Crateús, cores `#16a34a`, tabelas estilizadas, rodapé e paginação).
  - [x] Adicionar suporte a filtro de secretaria (`secretariat_id`) nos models `Fueling.php` e `Maintenance.php`.
  - [x] Implementar `ReportController.php` com ações de visualização (`index`), download de planilha CSV com UTF-8 BOM (`exportCsv`) e documento PDF oficial (`exportPdf`).
  - [x] Criar View `app/views/reports/index.php` com formulário de filtros dinâmicos, cards de resumo e tabelas de alta densidade para Abastecimentos, Manutenções e Consolidado.
  - [x] Atualizar roteamento central em `index.php` para as rotas `/reports`, `/reports/csv` e `/reports/pdf` com proteção RBAC (ADMIN e MANAGER).
  - [x] Executar testes automatizados via CLI em `tests/test_fase9.php` (7/7 testes aprovados).

- [x] **Fase 10 - Auditoria, Logs de Sistema, Segurança e Ajustes Finais**
  - [ ] Model `SystemLog.php` com fallback para `logs/system_error.log`.
  - [ ] Sanitização global XSS / CSRF.
  - [ ] Validação de 100% dos critérios de aceitação do FSD.

---

## Próximo Passo Recomendado

Abrir um novo chat para iniciar a **Fase 10 - Auditoria, Logs de Sistema, Segurança e Ajustes Finais**, implementando o Model `SystemLog.php` com mecanismo de contingência em arquivo em caso de falha de conexão MySQL, registrando logs de auditoria de segurança (tentativas de login, alterações administrativas, violações de KM), revisando sanitização global XSS/CSRF e executando a checagem final de 100% dos critérios de aceitação do `docs/FSD.md`.
