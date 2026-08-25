# Estado do Projeto - SisFrota Municipal

**Última atualização:** 25/08/2026  
**Fase Atual:** Fase 1 Concluída  
**Próxima Fase:** Fase 2 - Banco de dados, Persistência e Migrations  

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

- [ ] **Fase 2 - Banco de Dados, Persistência e Migrations**
  - [ ] Criar classe singleton PDO `app/models/Database.php`.
  - [ ] Criar executor CLI de migrations `database/migrate.php` protegido por `SYS_ENTRY`.
  - [ ] Criar migration `001_create_system_tables.sql` (tabelas: `migrations`, `users`, `secretariats`, `vehicles`, `maintenance_categories`, `fuelings`, `maintenances`, `system_logs`).
  - [ ] Criar migration `002_seed_initial_data.sql` (admin inicial, secretarias e categorias).
  - [ ] Validar execução e idempotência do script CLI.

- [ ] **Fase 3 - Autenticação, Sessão e Controle de Acesso (RBAC)**
  - [ ] Criar Model `User.php` e `AuthController.php`.
  - [ ] Criar View de Login `app/views/auth/login.php`.
  - [ ] Implementar verificação de sessão e RBAC (HTTP 403) nos Controllers.
  - [ ] Criar layouts base (`header.php`, `sidebar.php`, `footer.php`).

- [ ] **Fase 4 - Cadastros Base (Secretarias, Categorias de Manutenção e Usuários)**
  - [ ] Criar CRUD de Secretarias.
  - [ ] Criar CRUD de Categorias de Manutenção.
  - [ ] Criar CRUD de Usuários.

- [ ] **Fase 5 - Módulo de Veículos e Ficha Individual**
  - [ ] Criar `FormatHelper.php` (formatação de placas e moedas).
  - [ ] Criar Model `Vehicle.php` com Soft Delete (`is_deleted`).
  - [ ] Criar `VehicleController.php` e Views (listagem, formulário e ficha individual).

- [ ] **Fase 6 - Módulo de Abastecimentos e Validação Bloqueante de KM**
  - [ ] Criar Model `Fueling.php` com trava de KM e cálculo de $\text{KM/L}$.
  - [ ] Criar `FuelingController.php` e Views.
  - [ ] Atualização automática de `current_km` no veículo.

- [ ] **Fase 7 - Módulo de Manutenções e Gestão de Status**
  - [ ] Criar Model `Maintenance.php` e `MaintenanceController.php`.
  - [ ] Implementar transição de status de veículos para `MAINTENANCE` / `ACTIVE`.

- [ ] **Fase 8 - Dashboard Gerencial e Indicadores**
  - [ ] Criar `DashboardController.php` com métricas consolidadas.
  - [ ] Renderização de gráficos com Chart.js local.

- [ ] **Fase 9 - Módulo de Relatórios e Exportações (CSV e PDF)**
  - [ ] Filtros por tipo de registro, intervalo de datas e secretaria.
  - [ ] Exportação em CSV com UTF-8 BOM.
  - [ ] Exportação em PDF com biblioteca FPDF local.

- [ ] **Fase 10 - Auditoria, Logs de Sistema, Segurança e Ajustes Finais**
  - [ ] Model `SystemLog.php` com fallback para `logs/system_error.log`.
  - [ ] Sanitização global XSS / CSRF.
  - [ ] Validação de 100% dos critérios de aceitação do FSD.

---

## Próximo Passo Recomendado

Abrir um novo chat para iniciar a **Fase 2 - Banco de Dados, Persistência e Migrations**, criando a classe `Database.php`, a infraestrutura de migrations e a carga inicial de dados (seed) via CLI.
