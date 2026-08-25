# DOCUMENTO DE ESPECIFICAÇÃO FUNCIONAL (FSD)

---

## 1. Visão Geral

* **Nome do Sistema:** SisFrota Municipal (Sistema de Gestão de Frota da Prefeitura)
* **Objetivo Principal:** Proporcionar à Prefeitura Municipal o controle centralizado, auditável e transparente sobre a frota de veículos públicos, contemplando a gestão de abastecimentos de combustível, medição automatizada de quilometragem (KM), cálculo automático de consumo médio ($\text{KM/L}$), acompanhamento de manutenções preventivas/corretivas e emissão de relatórios gerenciais e oficiais em formatos CSV e PDF.
* **Resumo do Funcionamento:** O sistema opera como uma plataforma web corporativa centralizada. Os servidores municipais operacionais registram as operações diárias de abastecimento e ordens de serviço de oficina; os gestores e diretores municipais acompanham indicadores consolidados e gráficos de custos em tempo real através de um Dashboard gerencial; e os administradores controlam o acesso dos usuários, gerenciam categorias de manutenção e executam auditorias ou correções operacionais quando necessário.
* **Público Usuário:** 
  1. *Operadores / Funcionários Municipais:* Servidores encarregados da entrada diária dos lançamentos operacionais de campo.
  2. *Gestores / Diretores Municipais:* Secretários e diretores de departamento focados na análise de consumo, acompanhamento de custos e prestação de contas.
  3. *Administradores do Sistema:* Equipe técnica ou responsável pela governança de dados, gestão de contas de acesso e parâmetros globais.
* **Contexto de Uso:** Uso em ambiente corporativo interno da administração pública municipal. O acesso é realizado via navegador web em estações de trabalho desktop, notebooks ou dispositivos móveis de uso institucional.
* **Observações Relevantes para Implementação:** O sistema deve garantir integridade matemática nos cálculos de consumo, trava rígida contra inconsistências ou retrocessos de quilometragem nos abastecimentos, histórico auditável de alterações e uma interface amigável alinhada aos padrões visuais e funcionais da administração pública.

---

## 2. Documentos do Projeto para Implementação

Para a implementação completa do sistema, a IA codificadora e os desenvolvedores deverão utilizar exclusivamente os seguintes documentos oficiais:

* `docs/FSD.md` (este Documento de Especificação Funcional, que consolida todas as definições de escopo, arquitetura, banco de dados, regras de negócio e fluxos);
* `docs/DESIGN.md` (Documento de Especificação Visual e Sistema de Design do projeto).

> [!IMPORTANT]
> Este FSD é autossuficiente e consolidado. Ele contém todas as definições funcionais e decisões técnicas necessárias para a codificação da aplicação. Não é necessário consultar documentos anteriores de requisitos ou registros de conversas de alinhamento.

---

## 3. Stack Definida

* **Linguagem Backend:** PHP (versão 8.0 ou superior).
* **Banco de Dados Relacional:** MySQL (versão 8.0) ou MariaDB (versão 10.4 ou superior).
* **Tecnologias de Interface (Frontend):** 
  * HTML5 semântico;
  * CSS3 customizado estruturado com base nas variáveis do `docs/DESIGN.md`;
  * JavaScript puro (Vanilla JS - ES6+), sem a necessidade de frameworks pesados de frontend;
  * Framework Bootstrap (versão 5.x), armazenado localmente no projeto.
* **Dependências de Terceiros e Bibliotecas:**
  * Todas as bibliotecas de interface (Bootstrap, CSS, JS) e de geração de documentos (PDF/CSV) devem ser mantidas localmente dentro do projeto para permitir execução offline no ambiente do servidor municipal, sem dependência de CDNs externos.
  * **Geração de PDF:** Utilização da biblioteca FPDF mantida localmente dentro do projeto.
  * **Gráficos do Dashboard:** Utilização da biblioteca Chart.js (versão minificada mantida em `assets/js/vendor/chart.min.js`).
  * **Logotipo Institucional:** A imagem `logo_Crateus.png` (presente em `docs/`) será copiada para `assets/img/logo.png` durante a fase de construção e usada na tela de login, na sidebar e nos relatórios PDF.
* **Padrão Arquitetural:** Padrão MVC (Model-View-Controller).
* **Restrições Técnicas:** 
  * Proibição de uso de arquivo `.env` para credenciais (deve-se utilizar arquivo de configuração PHP compilado no servidor);
  * Ausência de dependência de APIs externas de terceiros;
  * Arquivos estáticos e recursos web servidos localmente.

---

## 4. Ambientes do Projeto

* **Ambiente de Desenvolvimento Local:**
  * Servidor web Apache com PHP 8.x e servidor de banco de dados MySQL/MariaDB executado via ambiente integrado XAMPP em SO Windows.
  * O projeto será desenvolvido dentro do diretório do repositório local e testado via navegação web local (ex: `http://localhost/nome-do-projeto/`).
* **Ambiente de Homologação / Testes:**
  * Os testes funcionais, de integridade e de aceitação serão realizados diretamente no ambiente local antes da preparação para publicação.
* **Ambiente de Produção:**
  * Hospedagem inicial configurada para servidor web compatível com PHP e MySQL gerenciado (como Vercel com banco em nuvem gerenciado ou hospedagens compartilhadas como Hostnet), prevendo futura migração para a infraestrutura física de servidores da Prefeitura Municipal.
* **Observações sobre Deploy:**
  * O deploy consistirá na cópia dos arquivos do repositório, configuração do arquivo de conexão PHP local e execução controlada das migrations de banco de dados no ambiente de destino.

---

## 5. Arquitetura do Sistema

A arquitetura do sistema segue a organização baseada no padrão **MVC (Model-View-Controller)**, garantindo a separação de responsabilidades entre a persistência de dados, a lógica de negócio e a camada de visualização.

### Referência de Diretório do Projeto

A referência estrutural raiz para a aplicação é tratada neste documento como:

`[Diretório do Projeto - Repositório]`

Este diretório representa a pasta versionada da aplicação. Ele poderá ser hospedado dentro do diretório público de qualquer servidor web, por exemplo:
* No XAMPP: `htdocs/nome-do-projeto/`
* Na Hostnet: `www/nome-do-projeto/` ou `public_html/nome-do-projeto/`
* Em subpastas de servidores corporativos da Prefeitura.

A aplicação não assume que ocupa isoladamente a raiz pública da hospedagem, permitindo convivência com outros sistemas no mesmo servidor.

### Estrutura de Diretórios Sugerida

```
[Diretório do Projeto - Repositório]/
├── index.php                      # Arquivo de entrada principal, bootstrap e roteador da aplicação
├── .htaccess                      # Proteção de diretórios internos e reescrita de URLs no Apache
├── config/                        # Configurações sensíveis e de banco de dados
│   ├── config.php                 # Arquivo de configuração em código PHP (sem uso de .env)
│   └── .htaccess                  # Bloqueio de acesso direto via navegação web
├── app/                           # Código-fonte da aplicação (MVC)
│   ├── .htaccess                  # Bloqueio de acesso direto via navegação web
│   ├── controllers/               # Controllers da aplicação (Camada de Controle)
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── VehicleController.php
│   │   ├── FuelingController.php
│   │   ├── MaintenanceController.php
│   │   ├── ReportController.php
│   │   └── UserController.php
│   ├── models/                    # Models da aplicação (Camada de Dados e Lógica de Negócio)
│   │   ├── Database.php
│   │   ├── User.php
│   │   ├── Secretariat.php
│   │   ├── Vehicle.php
│   │   ├── MaintenanceCategory.php
│   │   ├── Fueling.php
│   │   ├── Maintenance.php
│   │   └── SystemLog.php
│   ├── helpers/                   # Utilitários e helpers de formatação (ex: placas, datas)
│   │   └── FormatHelper.php
│   └── views/                     # Views da aplicação (Camada de Interface)
│       ├── layouts/               # Template principal (Header, Sidebar, Footer)
│       ├── auth/                  # Tela de Login
│       ├── dashboard/             # Painel Principal
│       ├── vehicles/              # Formulários, listagens e Ficha do Veículo
│       ├── fuelings/              # Listagem e formulário de Abastecimentos
│       ├── maintenances/          # Listagem e formulário de Manutenções
│       ├── reports/               # Formulário e visualização de Relatórios
│       └── settings/              # Usuários, Categorias e Secretarias
├── assets/                        # Arquivos públicos estáticos (CSS, JS, Imagens, Fontes)
│   ├── css/                       # Bootstrap local e estilos customizados
│   ├── js/                        # Scripts JS da interface e manipuladores de gráficos
│   └── img/                       # Logotipos e ícones do sistema
├── database/                      # Scripts e infraestrutura de banco de dados
│   ├── .htaccess                  # Bloqueio de acesso direto via navegação web
│   ├── migrations/                # Scripts versionados de criação e atualização de tabelas
│   └── migrate.php                # Script controlado para execução de migrations via CLI/Admin
└── logs/                          # Registros em arquivo de erros e eventos de contingência
    ├── .htaccess                  # Bloqueio de acesso direto via navegação web
    └── system_error.log           # Log de erros em arquivo (fallback do banco)
```

### Funcionamento do Bootstrap (`index.php`) e Fuso Horário

O arquivo `index.php` atua como ponto de entrada único para todas as requisições web. Suas atribuições obrigatórias no bootstrap são:
1. **Definição da Constante de Segurança:** Define `define('SYS_ENTRY', true);` impedindo acesso direto aos arquivos incluídos.
2. **Carregamento das Configurações:** Requer o arquivo `config/config.php`.
3. **Configuração de Timezone:** Aplica obrigatoriamente a instrução `date_default_timezone_set($config['app']['timezone']);` (definida como `'America/Sao_Paulo'`), garantindo precisão nos registros de data/hora nos ambientes local e de produção.
4. **Despacho de Rota:** Invoca o Controller e a ação correspondentes à URL solicitada.

### Funcionamento dos Papéis Arquiteturais (MVC)

* **Model:** Encapsula a lógica de dados, execução de consultas SQL preparadas (PDO), validações rígidas de persistência (como verificação da KM digitada no abastecimento em relação ao histórico) e regras de auditoria e soft delete.
* **View:** Responsável exclusivamente pela renderização do HTML5 semântico, aplicação de estilos CSS do `docs/DESIGN.md`, montagem das tabelas de alta densidade e preenchimento de componentes Bootstrap. Não executa regras de negócio nem consultas SQL diretas.
* **Controller:** Recebe as requisições HTTP enviadas a partir do `index.php`, verifica o perfil do usuário logado através do mecanismo RBAC, aciona os métodos adequados do Model para manipulação ou consulta de dados e determina qual View será renderizada ou para qual rota o usuário será redirecionado.

### Proteção Contra Acesso Direto a Diretórios Internos

1. **Proteção via Servidor Web (.htaccess / Apache):** As pastas `config/`, `app/`, `database/` e `logs/` devem conter arquivos `.htaccess` com a diretiva `Require all denied` (ou `Deny from all`), impedindo a leitura ou download direto de arquivos `.php`, `.log` ou `.sql` via URL no navegador.
2. **Proteção em Nível de Código PHP:** Todo arquivo de código situado nas pastas internas deve validar a existência de uma constante global de inicialização definida no `index.php` (ex: `defined('SYS_ENTRY') or die('Acesso direto não permitido.');`).
3. **Arquivo de Configuração em Código PHP:** Credenciais de banco de dados e parâmetros do sistema ficam armazenados em `config/config.php`, retornando um array de configurações PHP. Este arquivo é carregado exclusivamente via `require_once` pelos scripts internos do backend, eliminando riscos de exposição em texto puro inerentes a arquivos `.env`.

---

## 6. Escopo Funcional da Primeira Versão

### Módulo 1: Autenticação e Controle de Acesso
* **Objetivo:** Garantir a identificação individual e a autorização de uso dos servidores municipais.
* **Usuários Envolvidos:** Administrador, Operador, Gestor.
* **Ações Permitidas:** Login com Nome de Usuário (*username*) e Senha; encerramento de sessão (Logout); alteração de senhas pelo Administrador.
* **Resultado Esperado:** Usuário autenticado e direcionado ao Dashboard com o menu lateral adaptado ao seu perfil de permissões.

### Módulo 2: Cadastros Base e Configurações Globais
* **Objetivo:** Manter a estrutura de secretarias, categorias de serviço e a frota de veículos atualizados.
* **Usuários Envolvidos:** Administrador (gestão total), Operador (cadastro de veículos).
* **Ações Permitidas:** Cadastrar e editar veículos (Placa antigo/Mercosul, Modelo, Secretaria, Combustível Padrão, Status); inativar veículos (Soft Delete); cadastrar e editar categorias de manutenção (ex: Troca de Óleo, Freios, Pneus); cadastrar e editar secretarias municipais; cadastrar e gerenciar contas de usuários.
* **Resultado Esperado:** Base cadastral consistente e pronta para receber lançamentos operacionais.

### Módulo 3: Lançamento de Abastecimentos
* **Objetivo:** Registrar individualmente cada evento de abastecimento da frota.
* **Usuários Envolvidos:** Operador, Administrador.
* **Ações Permitidas:** Registrar veículo, data/hora, KM atual no momento do abastecimento, litros, valor total (R$), tipo de combustível e nome do posto.
* **Resultado Esperado:** Gravado o abastecimento, atualizada a KM atual do veículo no cadastro (com validação bloqueante de KM maior que a anterior) e calculado automaticamente o consumo médio em $\text{KM/L}$ a partir do segundo abastecimento.

### Módulo 4: Lançamento de Manutenções
* **Objetivo:** Registrar e acompanhar os serviços de manutenção preventiva e corretiva em oficinas.
* **Usuários Envolvidos:** Operador, Administrador.
* **Ações Permitidas:** Registrar veículo, categoria do serviço, oficina contratada, número da Ordem de Serviço (OS), valor total (R$) e status do atendimento (*Agendada*, *Em Andamento*, *Pendente*, *Concluída*).
* **Resultado Esperado:** Serviço cadastrado no histórico do veículo, alimentação do gráfico de manutenções e atualização do status do veículo quando necessário.

### Módulo 5: Dashboard e Acompanhamento Gerencial
* **Objetivo:** Apresentar uma visão panorâmica da operação e dos custos da frota municipal.
* **Usuários Envolvidos:** Gestor, Administrador, Operador.
* **Ações Permitidas:** Visualização de 4 cartões de métricas do mês vigente; consulta a gráficos comparativos mensais de despesas e proporção de manutenções pendentes/concluídas.
* **Resultado Esperado:** Decisão rápida e monitoramento em tempo real dos gastos com combustível e oficina.

### Módulo 6: Ficha Individual do Veículo
* **Objetivo:** Centralizar todo o histórico operacional e financeiro de um bem específico.
* **Usuários Envolvidos:** Todos os perfis.
* **Ações Permitidas:** Consultar dados cadastrais, KM atual rodada, consumo médio acumulado ($\text{KM/L}$) e duas abas detalhadas com a listagem cronológica de abastecimentos e manutenções.
* **Resultado Esperado:** Transparência completa e auditabilidade individualizada do histórico do veículo.

### Módulo 7: Módulo de Relatórios e Exportação
* **Objetivo:** Compilar e emitir documentos oficiais de prestação de contas.
* **Usuários Envolvidos:** Gestor, Administrador.
* **Ações Permitidas:** Selecionar tipo de registro (Abastecimentos, Manutenções ou Consolidado), aplicar intervalo de datas e filtrar por secretaria municipal. Exportar dados em planilha **CSV** (com UTF-8 BOM) ou documento formatado **PDF**.
* **Resultado Esperado:** Downloads rápidos de arquivos formatados para análises em planilha ou impressão oficial.

---

## 7. Fora de Escopo

Os seguintes recursos **não farão parte da primeira versão (V1)** do sistema:

1. **Upload de Imagens, Comprovantes ou Notas Fiscais:** Ausência de armazenamento de arquivos anexos para manter o fluxo de cadastramento simplificado e rápido.
2. **Alertas e Notificações Automáticas via E-mail/SMS:** Não haverá disparo de e-mails de aviso de revisões ou trocas de óleo. O acompanhamento será visual no Dashboard e na lista de manutenções.
3. **Cadastro e Vínculo de Motoristas/Condutores:** Não haverá registro do servidor condutor do veículo em cada abastecimento ou serviço.
4. **Módulo de Checklist e Vistoria Prévia:** Sem fichas digitais de inspeção diária na garagem.
5. **Rastreamento por GPS e Telemetria em Tempo Real:** Sem integração com dispositivos de satélite ou mapeamento.
6. **Integração com APIs de Postos ou Bombas de Combustível:** Entrada de dados 100% realizada pelos operadores no sistema.
7. **Gestão de Multas de Trânsito e Pontuação na CNH:** Foco exclusivo no controle financeiro e manutenção.
8. **Arquitetura Multi-Tenant / Múltiplas Prefeituras:** O sistema atenderá estritamente a uma única estrutura municipal por instância instalada.

---

## 8. Perfis de Usuário e Permissões

O sistema adota o modelo de Controle de Acesso Baseado em Perfis (RBAC - *Role-Based Access Control*), estruturado em 3 níveis:

1. **Administrador (`ADMIN`):**
   * *Descrição:* Servidor responsável pela gestão do sistema, controle de acessos e auditoria.
   * *Permissões:* Acesso total e irrestrito a todos os módulos, telas, relatórios e configurações do sistema. Permissão exclusiva para cadastrar/editar usuários, gerenciar categorias de manutenção e realizar edições ou correções em lançamentos passados de KM e históricos.
2. **Operador / Funcionário (`OPERATOR`):**
   * *Descrição:* Servidor responsável por alimentar o sistema com as operações do dia a dia.
   * *Permissões:* Permissão para cadastrar novos veículos, lançar novos abastecimentos e cadastrar novas manutenções. Acesso de leitura às listagens gerais e fichas de veículos.
   * *Restrições:* Não pode editar ou excluir lançamentos passados, não altera dados de usuários, não acessa o menu de configurações técnicas e não altera histórico de KM.
3. **Gestor / Diretor (`MANAGER`):**
   * *Descrição:* Secretário, diretor ou auditor interno focado em análise estratégica e prestação de contas.
   * *Permissões:* Acesso exclusivo de leitura (*Read-Only*) ao Dashboard gerencial, fichas individuais dos veículos, listagens gerais e módulo de relatórios exportáveis (CSV e PDF).
   * *Restrições:* Não visualiza botões de inclusão, edição ou exclusão em nenhum formulário ou tabela do sistema.

### Matriz de Permissões (RBAC)

| Módulo / Funcionalidade | Administrador (`ADMIN`) | Operador (`OPERATOR`) | Gestor (`MANAGER`) |
| :--- | :---: | :---: | :---: |
| Autenticação (Login / Logout) | Sim | Sim | Sim |
| Visualizar Dashboard e Gráficos | Sim | Sim | Sim |
| Consultar Ficha do Veículo | Sim | Sim | Sim |
| Cadastrar Novo Veículo | Sim | Sim | Não |
| Editar Dados do Veículo | Sim | Não | Não |
| Inativar Veículo (Soft Delete) | Sim | Não | Não |
| Lançar Novo Abastecimento | Sim | Sim | Não |
| Lançar Nova Manutenção | Sim | Sim | Não |
| Atualizar Status de Manutenção em Andamento | Sim | Sim | Não |
| Editar/Corrigir Abastecimento Passado | Sim | Não | Não |
| Editar/Corrigir Manutenção Passada | Sim | Não | Não |
| Exportar Relatórios (CSV e PDF) | Sim | Não | Sim |
| Gerenciar Usuários (Criar / Editar / Senha) | Sim | Não | Não |
| Gerenciar Categorias de Manutenção | Sim | Não | Não |
| Gerenciar Secretarias (Criar / Editar) | Sim | Não | Não |
| Visualizar Logs do Sistema e Auditoria | Sim | Não | Não |

---

## 9. Recursos Estruturais do Sistema

* **Autenticação Segura:** Autenticação local baseada em *username* e *senha* com armazenamento de hash seguro (`password_hash` com algoritmo BCrypt/PASSWORD_DEFAULT em PHP) e controle de sessão via HTTP Cookies seguros e HTTPOnly.
* **RBAC (Controle de Acesso):** Verificação de perfil em nível de Controller antes da execução de qualquer ação ou renderização de View, disparando resposta HTTP 403 / Alerta em caso de acesso não autorizado.
* **Auditoria de Dados:** Armazenamento automático de metadados em todas as tabelas transacionais e cadastrais contendo: `created_at` (data/hora de criação), `created_by` (ID do usuário criador), `updated_at` (data/hora da última alteração) e `updated_by` (ID do usuário alterador).
* **Exclusão Lógica (Soft Delete):** Aplicação de soft delete na tabela de veículos (`is_deleted` / `deleted_at`) garantindo a integridade dos dados históricos de abastecimentos e relatórios.
* **Log de Erros com Contingência:** Gravação centralizada de erros de execução e falhas técnicas em banco de dados (`system_logs`) com contingência automática para arquivo de texto em disco (`logs/system_error.log`) caso ocorra indisponibilidade na conexão MySQL.
* **Log de Segurança:** Gravação de auditoria de eventos sensíveis (tentativas falhas de login, bloqueios por validação de KM inferior, correções administrativas de lançamentos).
* **Configurações Globais:** Interface administrativa para cadastro de categorias de serviços mecânicos, secretarias e gerenciamento das credenciais de acesso.
* **Gerador de Relatórios Exportáveis:** Módulos de compilação de dados nativos para download imediato em formato **CSV** (com UTF-8 BOM) e renderização de layouts de impressão em **PDF**.
* **Uploads / Anexos / APIs Externas:** Declarados oficialmente como **NÃO APLICÁVEIS** nesta primeira versão.

---

## 10. Entidades do Sistema

### 1. Usuário (`users`)
* **Finalidade:** Armazenar as credenciais e dados dos servidores autorizados a acessar o sistema.
* **Informações Principais:** ID, Nome de Usuário (*username*), Nome Completo, Senha Hasheada, Perfil (`ADMIN`, `OPERATOR`, `MANAGER`), Status (`ACTIVE`, `INACTIVE`), Metadados de Auditoria.
* **Regras:** Nome de usuário único na base; sem obrigatoriedade de e-mail; exclusão física desativada se houver vínculos de auditoria.

### 2. Secretaria / Setor (`secretariats`)
* **Finalidade:** Classificar a qual secretaria ou departamento municipal o veículo pertence.
* **Informações Principais:** ID, Nome da Secretaria (ex: *Secretaria de Saúde*, *Secretaria de Obras*, *Educação*), Sigla, Metadados de Auditoria.
* **Regras:** Vínculo opcional ao cadastrar um veículo. Usado como filtro nos relatórios.

### 3. Veículo (`vehicles`)
* **Finalidade:** Representar cada bem automotor integrante da frota municipal.
* **Informações Principais:** ID, Placa (padrão antigo `ABC-1234` ou Mercosul `ABC1D23`), Modelo, Marca, Ano, ID da Secretaria, Tipo de Combustível Padrão (`GASOLINE`, `ETHANOL`, `DIESEL`, `FLEX`), KM Atual Registrada, Status (`ACTIVE`, `MAINTENANCE`, `UNAVAILABLE`), Flag Soft Delete (`is_deleted`), Metadados de Auditoria.
* **Regras:** Placa única na base de ativos; se inativado (`is_deleted = 1`), é ocultado dos selects de novos lançamentos, mas preservado no banco para relatórios.

### 4. Categoria de Manutenção (`maintenance_categories`)
* **Finalidade:** Agrupar os tipos de serviços mecânicos executados nas oficinas.
* **Informações Principais:** ID, Nome da Categoria (ex: *Troca de Óleo*, *Sistema de Freios*, *Substituição de Pneus*, *Revisão Elétrica*, *Funilaria*), Descrição, Status (`ACTIVE`), Metadados de Auditoria.
* **Regras:** Gerenciada exclusivamente pelo Administrador na tela de configurações.

### 5. Abastecimento (`fuelings`)
* **Finalidade:** Registrar a compra e o consumo de combustível de um veículo.
* **Informações Principais:** ID, ID do Veículo, Data/Hora do Abastecimento, KM no Momento, Quantidade de Litros, Valor Total em Reais (R$), Valor Calculado por Litro (R$), Tipo de Combustível Utilizado, Nome do Posto Fornecedor, Consumo Médio Calculado ($\text{KM/L}$), Metadados de Auditoria.
* **Regras:** O campo KM deve ser estritamente maior que a última KM registrada para o veículo. Se for o 2º abastecimento ou posterior do veículo, calcula automaticamente o consumo médio.

### 6. Manutenção (`maintenances`)
* **Finalidade:** Registrar as despesas e serviços mecânicos realizados em oficinas contratadas.
* **Informações Principais:** ID, ID do Veículo, ID da Categoria, Data do Serviço, Nome da Oficina Contratada, Número da Ordem de Serviço (OS), Valor Total (R$), Status do Atendimento (`SCHEDULED`, `IN_PROGRESS`, `PENDING`, `COMPLETED`), Observações/Descrição do Serviço, Metadados de Auditoria.
* **Regras:** Permite associar o custo à oficina e atualizar a situação operacional do veículo. Não altera a quilometragem do veículo.

### 7. Log do Sistema (`system_logs`)
* **Finalidade:** Registrar ocorrências de erros técnicos e eventos de auditoria de segurança.
* **Informações Principais:** ID, Tipo de Evento (`ERROR`, `SECURITY_AUDIT`), Nível de Severidade (`INFO`, `WARNING`, `CRITICAL`), ID do Usuário envolvido (se houver), Descrição da Ocorrência, Endereço IP do Usuário, Rota/URL acessada, Stack Trace / Dados Técnicos, Data/Hora.
* **Regras:** Consulta restrita ao Administrador. Gravação com contingência em arquivo em caso de erro na base de dados.

---

## 11. Modelo de Dados Proposto

### Diagrama Entidade-Relacionamento (Conceitual)

```
+-------------------+       +-------------------+       +-------------------+
|    secretariats   |       |       users       |       |maint_categories   |
+-------------------+       +-------------------+       +-------------------+
| PK  id            |       | PK  id            |       | PK  id            |
|     name          |       |     username      |       |     name          |
+---------+---------+       |     role          |       +---------+---------+
          |                 +---------+---------+                 |
          | 1                         | 1                         | 1
          |                           |                           |
          | 0..N                      | 0..N                      | 0..N
+---------v---------+       +---------v---------+       +---------v---------+
|     vehicles      |       |     fuelings      |       |    maintenances   |
+-------------------+       +-------------------+       +-------------------+
| PK  id            |       | PK  id            |       | PK  id            |
| FK  secretariat_id|       | FK  vehicle_id    |       | FK  vehicle_id    |
|     plate         |       | FK  created_by    |       | FK  category_id   |
|     current_km    |       |     fueling_date  |       | FK  created_by    |
|     is_deleted    |       |     km            |       |     service_date  |
+---------+---------+       |     liters        |       |     status        |
          |                 |     total_price   |       |     cost          |
          |                 |     avg_kml       |       +-------------------+
          |                 +-------------------+
          | 1..N
          +-------------------------------------------------------+
```

### Especificação Rígida de Tabelas e Campos

#### Tabela: `users`
* `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
* `username` VARCHAR(50) NOT NULL UNIQUE
* `password_hash` VARCHAR(255) NOT NULL
* `full_name` VARCHAR(100) NOT NULL
* `role` ENUM('ADMIN', 'OPERATOR', 'MANAGER') NOT NULL DEFAULT 'OPERATOR'
* `status` ENUM('ACTIVE', 'INACTIVE') NOT NULL DEFAULT 'ACTIVE'
* `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
* `created_by` BIGINT UNSIGNED NULL
* `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
* `updated_by` BIGINT UNSIGNED NULL
* *Índices:* `idx_users_username` (`username`), `idx_users_role_status` (`role`, `status`)

#### Tabela: `secretariats`
* `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
* `name` VARCHAR(100) NOT NULL UNIQUE
* `acronym` VARCHAR(20) NULL
* `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
* `created_by` BIGINT UNSIGNED NULL
* `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
* `updated_by` BIGINT UNSIGNED NULL
* *Índices:* `idx_secretariats_name` (`name`)

#### Tabela: `vehicles`
* `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
* `plate` VARCHAR(10) NOT NULL UNIQUE
* `model` VARCHAR(80) NOT NULL
* `brand` VARCHAR(50) NULL
* `year` INT UNSIGNED NULL
* `secretariat_id` BIGINT UNSIGNED NULL
* `fuel_type` ENUM('GASOLINE', 'ETHANOL', 'DIESEL', 'FLEX') NOT NULL DEFAULT 'FLEX'
* `current_km` INT UNSIGNED NOT NULL DEFAULT 0
* `status` ENUM('ACTIVE', 'MAINTENANCE', 'UNAVAILABLE') NOT NULL DEFAULT 'ACTIVE'
* `is_deleted` TINYINT(1) NOT NULL DEFAULT 0
* `deleted_at` DATETIME NULL
* `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
* `created_by` BIGINT UNSIGNED NULL
* `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
* `updated_by` BIGINT UNSIGNED NULL
* *Chaves Estrangeiras:* `fk_vehicles_secretariat` (`secretariat_id` REFERENCES `secretariats`(`id`) ON DELETE SET NULL)
* *Índices:* `idx_vehicles_plate` (`plate`), `idx_vehicles_status_deleted` (`status`, `is_deleted`), `idx_vehicles_secretariat` (`secretariat_id`)

#### Tabela: `maintenance_categories`
* `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
* `name` VARCHAR(80) NOT NULL UNIQUE
* `description` VARCHAR(255) NULL
* `status` ENUM('ACTIVE', 'INACTIVE') NOT NULL DEFAULT 'ACTIVE'
* `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
* `created_by` BIGINT UNSIGNED NULL
* `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
* `updated_by` BIGINT UNSIGNED NULL
* *Índices:* `idx_maint_cat_status` (`status`)

#### Tabela: `fuelings`
* `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
* `vehicle_id` BIGINT UNSIGNED NOT NULL
* `fueling_date` DATETIME NOT NULL
* `km` INT UNSIGNED NOT NULL
* `liters` DECIMAL(10, 2) NOT NULL
* `total_price` DECIMAL(10, 2) NOT NULL
* `unit_price` DECIMAL(10, 2) GENERATED ALWAYS AS (`total_price` / `liters`) STORED
* `fuel_type` ENUM('GASOLINE', 'ETHANOL', 'DIESEL', 'FLEX') NOT NULL
* `station_name` VARCHAR(100) NOT NULL
* `avg_kml` DECIMAL(8, 2) NULL COMMENT 'Calculado automaticamente a partir do 2º abastecimento'
* `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
* `created_by` BIGINT UNSIGNED NOT NULL
* `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
* `updated_by` BIGINT UNSIGNED NULL
* *Chaves Estrangeiras:*
  * `fk_fuelings_vehicle` (`vehicle_id` REFERENCES `vehicles`(`id`) ON DELETE RESTRICT)
  * `fk_fuelings_creator` (`created_by` REFERENCES `users`(`id`))
* *Índices:* `idx_fuelings_vehicle_date` (`vehicle_id`, `fueling_date`), `idx_fuelings_date` (`fueling_date`)

#### Tabela: `maintenances`
* `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
* `vehicle_id` BIGINT UNSIGNED NOT NULL
* `category_id` BIGINT UNSIGNED NOT NULL
* `service_date` DATE NOT NULL
* `workshop_name` VARCHAR(100) NOT NULL
* `os_number` VARCHAR(50) NOT NULL
* `cost` DECIMAL(10, 2) NOT NULL
* `status` ENUM('SCHEDULED', 'IN_PROGRESS', 'PENDING', 'COMPLETED') NOT NULL DEFAULT 'PENDING'
* `notes` TEXT NULL
* `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
* `created_by` BIGINT UNSIGNED NOT NULL
* `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
* `updated_by` BIGINT UNSIGNED NULL
* *Chaves Estrangeiras:*
  * `fk_maintenances_vehicle` (`vehicle_id` REFERENCES `vehicles`(`id`) ON DELETE RESTRICT)
  * `fk_maintenances_category` (`category_id` REFERENCES `maintenance_categories`(`id`))
  * `fk_maintenances_creator` (`created_by` REFERENCES `users`(`id`))
* *Índices:* `idx_maint_vehicle_date` (`vehicle_id`, `service_date`), `idx_maint_status` (`status`), `idx_maint_service_date` (`service_date`), `idx_maint_category` (`category_id`)

#### Tabela: `system_logs`
* `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
* `event_type` ENUM('ERROR', 'SECURITY_AUDIT') NOT NULL
* `severity` ENUM('INFO', 'WARNING', 'CRITICAL') NOT NULL DEFAULT 'INFO'
* `user_id` BIGINT UNSIGNED NULL
* `ip_address` VARCHAR(45) NULL
* `request_uri` VARCHAR(255) NULL
* `message` TEXT NOT NULL
* `details` LONGTEXT NULL
* `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
* *Índices:* `idx_logs_type_created` (`event_type`, `created_at`), `idx_logs_user` (`user_id`)

#### Tabela de Controle de Migrations: `migrations`
* `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
* `migration` VARCHAR(255) NOT NULL
* `executed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP

---

### Estratégia de Migrations e Execução CLI

1. **Localização dos Scripts:** Os arquivos de migration ficam situados na pasta protegida `database/migrations/` (ex: `001_create_users_table.sql`, `002_create_vehicles_table.sql`).
2. **Evitando Execução Duplicada:** O script executor de migrations (`database/migrate.php`) consulta a tabela `migrations`. Apenas arquivos com nomes que ainda não constem nessa tabela são executados em ordem alfabética/numérica.
3. **Mecanismo de Execução Seguro via CLI:** O script `database/migrate.php` deve ser executado via linha de comando no ambiente do servidor (ex: `php database/migrate.php`). No topo do script, ele deve obrigatoriamente definir `define('SYS_ENTRY', true);` antes de incluir `config/config.php`, garantindo a validação de segurança. As migrations **NÃO** devem ser expostas por URLs públicas sem autenticação.
4. **Carga Inicial de Dados (Seed):** A migration de dados iniciais criará o usuário Administrador de fábrica (`username`: `admin` / senha inicial: `admin123` gravada como hash BCrypt), além das secretarias e categorias iniciais do município.

---

## 12. Módulos e Telas

Todas as telas do sistema devem obrigatoriamente seguir a paleta visual e os padrões de interface descritos em `docs/DESIGN.md`:
* **Cores:** Municipal Green (`--primary-color: #16a34a` para elementos primários e `--primary-dark: #006b2c` para estados hover/active), Caution Yellow (`#facc15`), Slate Background (`#f8fafc`), Dark Charcoal Text (`#0f172a`), Outlines (`#e2e8f0`);
* **Tipografia:** Família **Inter** (títulos, corpo e rótulos) e fonte **Monospaced** (`data-mono`) para números, placas, valores em R$ e datas;
* **Helper de Formatação de Placas:** As placas são armazenadas sem traços (ex: `ABC1234`), mas a camada de View deve utilizar o helper `FormatHelper::formatPlate($plate)` para formatar a exibição no padrão tradicional (`ABC-1234`) ou Mercosul (`ABC1D23`).
* **Componentes:** Sidebar lateral fixa (260px no desktop), cards com bordas sutis e fundo branco, tabelas de alta densidade com cabeçalho em cinza claro e zebra striping, status chips em formato pílula.

### Tela 1: Login (`/login`)
* **Objetivo:** Autenticação dos usuários no sistema.
* **Usuários:** Todos os perfis (não autenticados).
* **Campos:** Nome de Usuário (`username`), Senha (`password`).
* **Botões:** "Entrar no Sistema" (Botão Primário Verde `#16a34a`).
* **Mensagens/Estados:** Exibe alerta vermelho ("Usuário ou senha incorretos") em credenciais inválidas. Desabilita o botão e mostra indicador de carregamento durante o envio.
* **Estilo Visual:** Card centralizado na tela sobre fundo `#f8fafc`, logotipo da Prefeitura no topo.

### Tela 2: Dashboard Principal (`/dashboard`)
* **Objetivo:** Apresentação dos indicadores consolidados do mês vigente.
* **Usuários:** Gestor, Administrador, Operador.
* **Elementos Exibidos:**
  * *Cards de Destaque no Topo:*
    1. Gasto Total no Mês (R$) - *Somatório de combustível + manutenções*;
    2. Gastos com Combustível no Mês (R$);
    3. KM Rodado no Mês (KM acumulado);
    4. Veículos Indisponíveis (Quantidade com status *Manutenção* ou *Indisponível*).
  * *Gráficos Gerenciais:*
    1. Gráfico de Evolução de Gastos Mensais (Barra/Linha comparativa dos últimos meses);
    2. Gráfico de Proporção de Manutenções (Rosca/Pizza: *Concluídas* vs *Pendentes/Em Andamento*).
* **Filtros e Botões:** Seletor rápido de Mês/Ano de referência.

### Tela 3: Listagem de Veículos (`/vehicles`)
* **Objetivo:** Exibir a lista da frota municipal e permitir acesso ao cadastramento e fichas.
* **Usuários:** Todos os perfis (com botões de ação adaptados ao perfil).
* **Colunas da Tabela:** Placa formatada pelo helper (`data-mono`), Modelo, Secretaria, Combustível Padrão, KM Atual (`data-mono`), Status (Pílula: Verde para *Ativo*, Amarela para *Manutenção*, Vermelha para *Indisponível*), Ações.
* **Busca e Filtros:** Campo de busca textual por Placa ou Modelo; Filtro por Secretaria; Filtro por Status.
* **Botões:** "Novo Veículo" (Visível para Admin/Operador), "Ver Ficha" (Todos os perfis), "Editar" (Admin), "Inativar" (Admin).

### Tela 4: Form de Veículo (`/vehicles/new` ou `/vehicles/edit/{id}`)
* **Objetivo:** Cadastrar ou editar os dados de um veículo.
* **Usuários:** Operador (apenas criação), Administrador (criação e edição).
* **Campos:** Placa (Validação de formato antigo `ABC-1234` ou Mercosul `ABC1D23`), Modelo, Marca, Ano, Secretaria (Dropdown), Combustível Padrão, KM Inicial/Atual, Status.
* **Botões:** "Salvar Veículo" (Primário Verde), "Cancelar".

### Tela 5: Ficha Individual do Veículo (`/vehicles/show/{id}`)
* **Objetivo:** Exibir o raio-X completo do veículo e seu histórico.
* **Usuários:** Todos os perfis.
* **Elementos Exibidos:**
  * Painel Superior: Placa Formatada em Destaque, Modelo, Secretaria, Status atual, KM Atual rodada e **Consumo Médio Calculado ($\text{KM/L}$)**;
  * Aba 1: Histórico de Abastecimentos (Tabela com Data, KM, Litros, Valor R$, $\text{KM/L}$, Posto);
  * Aba 2: Histórico de Manutenções (Tabela com Data, Categoria, Oficina, OS, Valor R$, Status).

### Tela 6: Listagem e Form de Abastecimentos (`/fuelings` e `/fuelings/new`)
* **Objetivo:** Registrar novos abastecimentos e consultar os já efetuados.
* **Usuários:** Operador, Administrador (criação), Gestor (apenas leitura da lista).
* **Campos do Formulário:** Veículo (Select filtrando apenas veículos com status *Ativo*), Data/Hora, KM no Momento, Quantidade de Litros, Valor Total (R$), Tipo de Combustível, Posto Fornecedor.
* **Validação em Tela:** Bloqueio e mensagem destacada em vermelho se a KM informada for menor ou igual à KM cadastrada anteriormente no veículo.

### Tela 7: Listagem e Form de Manutenções (`/maintenances` e `/maintenances/new`)
* **Objetivo:** Registrar ordens de serviço de oficina e acompanhar pendências.
* **Usuários:** Operador, Administrador (criação e atualização de status), Gestor (apenas leitura).
* **Campos do Formulário:** Veículo, Categoria da Manutenção (Dropdown), Data do Serviço, Oficina Contratada, Número da OS, Valor (R$), Status (*Agendada*, *Em Andamento*, *Pendente*, *Concluída*), Observações. (Nota: Não solicita KM).

### Tela 8: Módulo de Relatórios (`/reports`)
* **Objetivo:** Filtro e geração de relatórios oficiais em CSV e PDF.
* **Usuários:** Gestor, Administrador.
* **Campos de Filtro:** Tipo de Registro (*Abastecimentos*, *Manutenções*, *Consolidado de Gastos por Veículo*), Data Inicial, Data Final, Secretaria/Setor (*Todas* ou específica).
* **Botões:** "Exportar CSV" (Botão Secundário), "Gerar Relatório PDF" (Botão Primário Verde).

### Tela 9: Configurações - Gestão de Usuários (`/settings/users`)
* **Objetivo:** Cadastro e controle das contas de acesso dos servidores.
* **Usuários:** Exclusivo Administrador.
* **Campos:** Nome Completo, Nome de Usuário (*username*), Senha, Perfil (`ADMIN`, `OPERATOR`, `MANAGER`), Status.

### Tela 10: Configurações - Categorias de Manutenção (`/settings/categories`)
* **Objetivo:** Manutenção das categorias de oficina.
* **Usuários:** Exclusivo Administrador.
* **Campos:** Nome da Categoria, Descrição, Status.

### Tela 11: Configurações - Gestão de Secretarias (`/settings/secretariats`)
* **Objetivo:** Cadastro e atualização das secretarias/setores municipais.
* **Usuários:** Exclusivo Administrador.
* **Campos:** Nome da Secretaria, Sigla.

---

## 13. Fluxos Funcionais

### Fluxo 1: Autenticação do Servidor Municipal
1. O usuário acessa a URL do sistema. Se não houver sessão ativa, é redirecionado para `/login`.
2. O usuário preenche o *Nome de Usuário* e a *Senha* e clica em "Entrar".
3. O `AuthController` recebe a requisição, sanitiza os campos e consulta o `User` Model.
4. O Model verifica se o usuário existe, se o status é `ACTIVE` e compara o hash da senha via `password_verify`.
5. Se as credenciais forem válidas, a sessão HTTP é iniciada, os dados do usuário (ID, Nome, Role) são gravados na sessão, a ID de sessão é regenerada (`session_regenerate_id(true)`) e um registro de login com sucesso é gravado no log de segurança.
6. O usuário é redirecionado para o Dashboard (`/dashboard`).

### Fluxo 2: Registro de Abastecimento com Validação Bloqueante de KM
1. O Operador clica em "Abastecimentos" -> "Novo Abastecimento".
2. O formulário exibe a lista de veículos com status `ACTIVE`.
3. O Operador seleciona o Veículo. O sistema carrega dinamicamente a KM Atual do veículo selecionado para orientação visual.
4. O Operador preenche a Data/Hora, a KM no momento, Litros, Valor Total (R$), Tipo de Combustível e Posto.
5. O Operador clica em "Salvar Abastecimento".
6. O `FuelingController` envia os dados ao `Fueling` Model.
7. **Validação Rígida de KM:** O Model consulta a última KM registrada para o veículo no banco.
   * *Falha:* Se a KM informada for **menor ou igual** à KM anterior, a transação é abortada. O formulário é recarregado com os dados preenchidos e exibe um alerta de erro em destaque: `"Erro: A quilometragem informada ([KM Digitada]) é menor ou igual à última registrada ([KM Anterior]) para este veículo."`. Um evento de aviso é registrado no log de segurança.
   * *Sucesso:* Se a KM informada for **maior** que a anterior, a inserção é aprovada.
8. **Cálculo Automático de Consumo ($\text{KM/L}$):** O Model verifica se existe um abastecimento anterior registrado. Se existir, aplica a fórmula:
   $$\text{Consumo Médio} = \frac{\text{KM}_{\text{atual}} - \text{KM}_{\text{anterior}}}{\text{Litros}_{\text{atual}}}$$
   O valor resultante é gravado no campo `avg_kml` do novo registro.
9. A KM do veículo na tabela `vehicles` é atualizada para a nova KM informada.
10. O sistema exibe a mensagem de sucesso e redireciona para a Ficha do Veículo ou Lista de Abastecimentos.

### Fluxo 3: Registro e Mudança de Status de Manutenção
1. O Operador ou Admin acessa "Manutenções" -> "Nova Manutenção".
2. Seleciona o Veículo, Categoria, Data do Serviço, Oficina, Número da OS, Valor (R$) e Status inicial (*Agendada*, *Em Andamento* ou *Pendente*).
3. Ao salvar, se o status for *Em Andamento* ou *Pendente*, o sistema pode atualizar automaticamente o status do veículo para `MAINTENANCE`, removendo-o temporariamente dos seletores de novos abastecimentos.
4. Quando a manutenção é concluída na oficina, o usuário edita a Ordem de Serviço, altera o status para `COMPLETED` e salva.
5. O sistema atualiza o status do veículo de volta para `ACTIVE`, liberando-o para abastecimentos.

### Fluxo 4: Emissão de Relatórios Oficiais (CSV e PDF)
1. O Gestor ou Administrador acessa a tela de "Relatórios".
2. Seleciona o Tipo de Registro (ex: *Abastecimentos*), o Período (Data Inicial e Data Final) e a Secretaria municipal desejada (ou *Todas*).
3. Para exportação em **CSV**:
   * O usuário clica em "Exportar CSV".
   * O `ReportController` executa a consulta filtrada no banco, monta o arquivo CSV com suporte ao encoding UTF-8 com BOM (`\xEF\xBB\xBF` para abertura perfeita no Excel) e envia os headers HTTP adequados (`Content-Type: text/csv; charset=utf-8`, `Content-Disposition: attachment; filename="relatorio_abastecimentos.csv"`).
4. Para exportação em **PDF**:
   * O usuário clica em "Gerar PDF".
   * O Controller busca os dados filtrados, renderiza uma View HTML específica de impressão (contendo o brasão/cabeçalho oficial da Prefeitura, tabela formatada e rodapé de assinatura) e compila o PDF para download ou exibição em aba do navegador.

### Fluxo 5: Edição Administrativa e Correção de Erros de Lançamento
1. Caso um Operador tenha digitado uma informação errada que tenha passado (ex: valor em reais digitado incorretamente pelo Admin), apenas o perfil `ADMIN` visualiza os botões de edição nos históricos.
2. O Administrador acessa o registro, realiza a alteração no formulário administrativo de edição e fornece uma justificativa da correção.
3. O sistema atualiza o registro no banco de dados, recalcula a média $\text{KM/L}$ dos registros subsequentes se a KM tiver sido alterada, e grava uma entrada de auditoria detalhada no `system_logs` contendo o ID do Admin, a data/hora e o valor original vs. o valor corrigido.

---

## 14. Validações e Regras de Negócio

1. **Trava Bloqueante de Quilometragem (KM) Exclusiva dos Abastecimentos:**
   * No lançamento de abastecimentos (`fuelings`), o valor do campo `km` informado pelo operador deve ser estritamente maior que a `current_km` gravada no cadastro do veículo.
   * Tentativas de lançamento de abastecimento com $\text{KM}_{\text{informada}} \le \text{KM}_{\text{cadastrada}}$ devem ser rejeitadas pelo backend, impedindo a inserção no banco e notificando o usuário com mensagem de erro amigável. A rotina de manutenções não coleta ou altera a quilometragem do veículo.
2. **Cálculo de Consumo Médio ($\text{KM/L}$):**
   * O cálculo de consumo em $\text{KM/L}$ ocorre automaticamente a partir do 2º abastecimento registrado para o veículo.
   * Fórmula matemática oficial:
     $$\text{Consumo Médio (KM/L)} = \frac{\text{KM}_{\text{atual}} - \text{KM}_{\text{anterior}}}{\text{Litros}_{\text{atual}}}$$
   * No 1º abastecimento cadastrado do veículo, o campo `avg_kml` permanecerá `NULL`, pois não há marco inicial de rodagem anterior para cálculo de delta de quilometragem.
3. **Formato e Tratamento das Placas de Veículos:**
   * O campo `plate` deve aceitar e validar os formatos de placa vigentes no Brasil:
     * Padrão Tradicional: `3 letras - 4 números` (Ex: `ABC-1234`);
     * Padrão Mercosul: `3 letras 1 número 1 letra 2 números` (Ex: `ABC1D23`).
   * As placas são convertidas e armazenadas em letras maiúsculas e sem traços no banco de dados para padronização de busca. A View utiliza o helper `FormatHelper::formatPlate($plate)` para reexibir o hífen no padrão antigo.
4. **Exclusividade de Correção por Administrador:**
   * Operadores não possuem permissão para alterar ou excluir registros passados de abastecimento, manutenção ou KM. Apenas usuários com `role = 'ADMIN'` possuem o botão de alteração visível e a permissão no Controller backend.
5. **Veículos Inativos e Bloqueio de Abastecimento:**
   * Veículos com status `MAINTENANCE` ou `UNAVAILABLE`, ou marcados com soft delete (`is_deleted = 1`), não devem aparecer na lista de seleção para novos abastecimentos.
6. **Preservação de Dados Históricos na Inativação (Soft Delete):**
   * Ao inativar um veículo (`is_deleted = 1`), a linha permanece mantida no banco de dados. Todos os registros de abastecimentos e manutenções passadas continuam computados em relatórios e prestação de contas. As chaves estrangeiras com `ON DELETE RESTRICT` garantem que os dados históricos não sejam destruídos.

---

## 15. Autenticação e Sessão

* **Mecanismo de Autenticação:** Base local PHP utilizando sessões seguras (`$_SESSION`).
* **Segurança da Senha:**
  * As senhas nunca são armazenadas em texto puro.
  * O cadastro e a alteração de senha utilizam `password_hash($password, PASSWORD_DEFAULT)`.
  * A verificação utiliza `password_verify($password, $hash)`.
* **Gerenciamento de Sessão:**
  * Tempo de expiração por inatividade: 30 minutos.
  * Proteção contra *Session Hijacking*: Chamada obrigatória de `session_regenerate_id(true)` no momento do login.
  * Configuração dos parâmetros de cookie de sessão: `cookie_httponly = true`, `cookie_samesite = 'Lax'`, `use_only_cookies = true`.
* **Proteção de Rotas:**
  * Todas as requisições enviadas ao `index.php` passam por um middleware/filtro de autenticação que verifica se `$_SESSION['user_id']` está definido e ativo.
  * Caso o usuário tente acessar uma rota protegida sem estar autenticado, é redirecionado para `/login` com mensagem explicativa.
* **Prevenção contra Enumeração de Usuários:**
  * Na tentativa frustrada de login, a mensagem exibida em tela é genérica: `"Nome de usuário ou senha incorretos."`, evitando informar se o erro foi no usuário ou na senha.
* **Credenciais Iniciais de Fábrica:**
  * A migration de carga inicial (`seed`) disponibilizará a conta do Administrador padrão de fábrica com `username`: `admin` e `senha`: `admin123` (armazenada como hash BCrypt na tabela `users`).

---

## 16. Controle de Acesso

* **Implementação de RBAC no Backend:**
  * A autorização **nunca** depende apenas da ocultação de botões na interface View.
  * Todo Controller valida o perfil do usuário logado antes de processar requisições. Exemplo de verificação em método protegido:
    ```php
    if ($_SESSION['user_role'] !== 'ADMIN') {
        http_response_code(403);
        require_once VIEWS_PATH . '/errors/403.php';
        exit;
    }
    ```
* **Menus Dinâmicos conforme Perfil:**
  * O menu lateral (sidebar) renderiza opções conforme a matriz RBAC:
    * `OPERATOR`: Visualiza Dashboard, Veículos, Abastecimentos (Novo/Lista) e Manutenções (Novo/Lista).
    * `MANAGER`: Visualiza Dashboard, Veículos (Listas/Fichas) e Relatórios.
    * `ADMIN`: Visualiza todos os menus acima mais a opção "Configurações do Sistema" (Usuários, Categorias e Secretarias).
* **Tratamento para Acesso Negado:**
  * Requisições diretas por URL para rotas não autorizadas exibem uma tela amigável com a mensagem `"Acesso Negado: Seu perfil de usuário não possui permissão para executar esta ação."` acompanhada de um código HTTP 403 Forbidden.

---

## 17. Auditoria e Histórico

* **Campos de Auditoria Padrão:**
  Todas as tabelas principais mantêm 4 campos para rastreabilidade completa das operações de inserção e atualização:
  * `created_at`: Registro automático da data/hora de criação do registro;
  * `created_by`: ID do usuário logado que efetuou o cadastro;
  * `updated_at`: Registro automático da data/hora da última edição;
  * `updated_by`: ID do usuário logado que efetuou a alteração.
* **Auditoria de Operações Críticas:**
  * Alterações ou correções de dados realizadas por Administradores (ex: edição de valor ou KM de abastecimento passado) gravam um evento na tabela `system_logs` registrando o usuário executor, a data/hora, a tabela afetada e os valores original e alterado.
* **Consulta de Histórico:**
  * O histórico completo de um veículo pode ser consultado por qualquer perfil através da Ficha do Veículo, exibindo a linha do tempo imutável de todas as suas operações.

---

## 18. Soft Delete e Exclusões

* **Estratégia de Inativação (Soft Delete):**
  * Aplicada precipuamente à entidade de Veículos (`vehicles`).
  * Em vez de executar a instrução SQL `DELETE FROM vehicles WHERE id = :id`, o sistema atualiza o registro com `UPDATE vehicles SET is_deleted = 1, deleted_at = NOW(), status = 'UNAVAILABLE' WHERE id = :id`.
* **Comportamento do Sistema com Registros Inativados:**
  * *Listagens Ativas e Dropdowns:* As consultas SQL de veículos ativos para formulários de abastecimento ou manutenção utilizam a cláusula `WHERE is_deleted = 0 AND status = 'ACTIVE'`.
  * *Relatórios e Históricos Passados:* As consultas de relatórios de auditoria e despesas consolidadas **não** filtram por `is_deleted = 0` para períodos passados, garantindo que o dinheiro gasto em um veículo inativado continue sendo prestado às contas do município.
* **Permissão e Proteção de Exclusão:**
  * Apenas usuários com perfil `ADMIN` possuem o recurso de inativar veículos.
  * A exclusão física (`DELETE`) de registros no banco de dados é proibida para tabelas operacionais (`fuelings` e `maintenances`) para resguardar a integridade financeira pública, reforçada pelas chaves estrangeiras com `ON DELETE RESTRICT`.

---

## 19. Logs

### Log de Erros
* **Objetivo:** Registrar exceções de código, erros de instrução SQL ou falhas técnicas sem expor detalhes internos ao usuário final.
* **Informações Registradas:** Data/Hora, mensagem do erro, arquivo de origem, linha do código, código do erro, stack trace resumido e ID do usuário conectado.
* **Comportamento em Tela:** O usuário visualizará uma mensagem amigável e segura em tela: `"Ocorreu uma falha técnica ao processar a solicitação. O erro foi registrado para análise da equipe de TI."`.
* **Armazenamento Principal (Banco de Dados):** Gravado na tabela `system_logs` com `event_type = 'ERROR'`.
* **Mecanismo de Contingência em Arquivo (Fallback):**
  * Se o erro for decorrente de falha de conexão com o banco MySQL ou indisponibilidade do servidor de banco, o sistema aciona a rotina de contingência em código.
  * A rotina de contingência grava o registro formatado no arquivo físico `logs/system_error.log`.
  * O diretório `logs/` é protegido por `.htaccess` para impedir o acesso direto via URL no navegador.

### Log de Segurança
* **Objetivo:** Auditar eventos relevantes para a segurança do sistema e prevenções contra fraude.
* **Eventos Registrados:**
  1. Tentativas incorretas de login (usuário e IP de origem);
  2. Sucesso de autenticação (usuário, perfil e IP);
  3. Tentativas de acesso bloqueado por RBAC (HTTP 403);
  4. Bloqueios disparados pela validação de KM inferior;
  5. Edições e correções de dados históricos efetuadas por Administradores;
  6. Criação, inativação ou alteração de senhas de usuários.
* **Armazenamento:** Gravado na tabela `system_logs` com `event_type = 'SECURITY_AUDIT'`. Restrito para leitura ao perfil Administrador.

---

## 20. Configurações Globais

### Interface de Configurações do Sistema
Disponível exclusivamente para o perfil Administrador no menu "Configurações do Sistema", contempla:
1. **Gestão de Usuários:** Cadastro, redefinição de senhas e inativação de contas de servidores municipais.
2. **Categorias de Manutenção:** Cadastro e alteração de nomes e descrições dos tipos de serviços mecânicos prestados por oficinas.
3. **Gestão de Secretarias:** Cadastro e alteração de secretarias e setores municipais.

### Estratégia de Configuração Técnica do Projeto
* **Proibição de Arquivos `.env`:** Credenciais técnicas e configurações de ambiente **não** devem ser gravadas em arquivos `.env`.
* **Arquivo de Configuração em Código PHP (`config/config.php`):**
  * Os dados sensíveis ficam armazenados em um arquivo PHP localizado em `config/config.php`.
  * Exemplo de estrutura do arquivo de configuração:
    ```php
    <?php
    defined('SYS_ENTRY') or die('Acesso direto negado.');

    return [
        'db' => [
            'host' => 'localhost',
            'dbname' => 'sisfrota_db',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'port' => 3306
        ],
        'app' => [
            'name' => 'SisFrota Municipal',
            'url' => 'http://localhost/nome-do-projeto',
            'timezone' => 'America/Sao_Paulo',
            'session_lifetime' => 1800,
            'debug' => false
        ],
        'logs' => [
            'enabled' => true,
            'path' => __DIR__ . '/../logs/system_error.log'
        ]
    ];
    ```
* **Proteção:** O diretório `config/` contém um arquivo `.htaccess` impedindo a leitura remota e o código PHP valida a constante `SYS_ENTRY`, impedindo a execução direta.

---

## 21. Uploads, Anexos e Arquivos

* **Status da Funcionalidade:** O recurso de upload de arquivos, envio de fotos de notas fiscais, digitalização de comprovantes de abastecimento ou anexos em PDF **não faz parte da primeira versão (V1)** do sistema.
* **Orientações para a Codificação:** A IA codificadora não deve criar inputs do tipo `file`, tabelas de anexos ou diretórios de upload de mídia no projeto para esta versão.

---

## 22. Relatórios, Consultas e Exportações

### Relatórios Disponíveis

1. **Relatório de Abastecimentos:**
   * *Objetivo:* Prestação de contas detalhada dos gastos com combustível.
   * *Filtros:* Período (Data Início e Fim), Secretaria, Veículo.
   * *Colunas:* Data/Hora, Placa, Modelo, Secretaria, KM, Litros, Valor Total (R$), Valor/L (R$), Combustível, Posto, Consumo ($\text{KM/L}$).
2. **Relatório de Manutenções:**
   * *Objetivo:* Auditoria de despesas com oficinas e serviços mecânicos.
   * *Filtros:* Período (Data Início e Fim), Secretaria, Categoria de Manutenção, Status.
   * *Colunas:* Data, Placa, Modelo, Secretaria, Categoria, Oficina, Número da OS, Valor Total (R$), Status.
3. **Relatório Consolidado de Gastos por Veículo:**
   * *Objetivo:* Apresentar a soma acumulada de despesas e rodagem por veículo no período.
   * *Colunas:* Placa, Modelo, Secretaria, KM Total Rodada no Período, Litros Consumidos, Total Gasto em Combustível (R$), Total Gasto em Manutenção (R$), Custo Total Geral (R$).

### Formatos de Exportação
* **Exportação CSV:** Arquivo formatado em valores separados por ponto e vírgula (`;`), com codificação UTF-8 com BOM (`\xEF\xBB\xBF`), garantindo que o arquivo seja aberto diretamente no Microsoft Excel sem desconfigurar acentuações.
* **Exportação PDF:** Gerada através da biblioteca FPDF (mantida localmente), contendo layout formatado com cabeçalho institucional (nome e brasão da Prefeitura Municipal), tabela de dados estilizada e rodapé com data de emissão e identificação do usuário emissor.

### Otimização de Consultas (Desempenho)
* Para garantir a execução rápida dos relatórios, o banco de dados utilizará os índices compostos previamente definidos (`idx_fuelings_vehicle_date`, `idx_fuelings_date`, `idx_maint_service_date`, `idx_vehicles_secretariat`).

---

## 23. APIs e Integrações Externas

* **Status da Funcionalidade:** O desenvolvimento de APIs REST abertas, webhooks ou integrações com sistemas externos (posturas de combustível, GPS, telemetria) **não faz parte da primeira versão (V1)** do sistema.
* **Orientações para a Codificação:** O sistema opera de forma autônoma sem dependências de serviços web ou endpoints externos.

---

## 24. Segurança Funcional

1. **Proteção contra SQL Injection:** Uso obrigatório de instruções preparadas (*Prepared Statements*) via PDO em todas as consultas executadas pelos Models. É expressamente proibida a concatenação direta de variáveis em strings SQL.
2. **Proteção contra Cross-Site Scripting (XSS):** Todos os dados vindos do banco de dados ou digitados por usuários devem ser higienizados com `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')` antes de serem renderizados nas Views HTML.
3. **Proteção contra Cross-Site Request Forgery (CSRF):** Formulários do tipo `POST` devem conter um token CSRF único gerado por sessão (`<input type="hidden" name="csrf_token" value="...">`) e validado pelo Controller antes do processamento.
4. **Proteção de Rotas e Verificação RBAC:** Validação no backend em dois níveis: (1) verificação de sessão ativa; (2) verificação de permissão do perfil para a rota solicitada.
5. **Tratamento Seguro de Mensagens de Erro:** Nenhuma mensagem contendo caminhos físicos do servidor, consultas SQL brutas ou detalhes do PHP deve ser exibida ao usuário final.
6. **Segurança no Upload / Download:** Arquivos de relatório exportados são gerados dinamicamente em memória e transmitidos por stream seguro de download com headers HTTP apropriados.

---

## 25. Organização Sugerida da Implementação

A implementação do projeto pela IA codificadora deve ser conduzida de maneira progressiva, organizada nas seguintes 24 etapas sequenciais e testáveis:

1. **Etapa 1:** Preparação do `[Diretório do Projeto - Repositório]` na pasta web local do XAMPP (`htdocs/nome-do-projeto/`).
2. **Etapa 2:** Criação da estrutura completa de pastas do projeto (`config/`, `app/`, `assets/`, `database/`, `logs/`).
3. **Etapa 3:** Implementação dos arquivos de proteção `.htaccess` e constantes de segurança (`SYS_ENTRY`) em todas as pastas internas.
4. **Etapa 4:** Criação do arquivo de configuração em código PHP (`config/config.php`) contendo os parâmetros locais do MySQL e sistema.
5. **Etapa 5:** Desenvolvimento da classe singleton de conexão ao banco de dados via PDO (`app/models/Database.php`).
6. **Etapa 6:** Criação da estrutura de migrations em `database/migrations/` e do script CLI de execução segura `database/migrate.php` com declaração da constante `SYS_ENTRY`.
7. **Etapa 7:** Execução das migrations para criação das tabelas com restrições `ON DELETE RESTRICT`, chaves primárias, estrangeiras, índices e carga inicial (seed) do Administrador padrão (`username`: `admin` / `senha`: `admin123`), secretarias e categorias.
8. **Etapa 8:** Desenvolvimento da infraestrutura de rotas, helper de formatação (`FormatHelper.php`) e do arquivo de entrada principal (`index.php`) com definição obrigatória de timezone.
9. **Etapa 9:** Implementação da classe de controle de logs de erro e segurança (`app/models/SystemLog.php`) com contingência em arquivo `logs/system_error.log`.
10. **Etapa 10:** Desenvolvimento do Módulo de Autenticação (`AuthController.php`, `User.php` Model, View `/views/auth/login.php` e controle de sessão).
11. **Etapa 11:** Criação da estrutura de Layout Base (`views/layouts/header.php`, `sidebar.php`, `footer.php`) aplicando as variáveis de cor do `docs/DESIGN.md` (`--primary-color: #16a34a`) e Bootstrap local.
12. **Etapa 12:** Implementação do Módulo de Veículos (`Vehicle.php` Model, `VehicleController.php`, Views de listagem, cadastro e inativação com Soft Delete).
13. **Etapa 13:** Implementação da Ficha Individual do Veículo (`/vehicles/show/{id}`) com abas de dados e históricos.
14. **Etapa 14:** Implementação do Módulo de Abastecimentos (`Fueling.php` Model, `FuelingController.php`, Views de formulário e listagem).
15. **Etapa 15:** Implementação da **Validação Bloqueante de KM** no backend do `Fueling` Model com emissão de alertas em tela.
16. **Etapa 16:** Implementação do **Cálculo Automático de Consumo Médio ($\text{KM/L}$)** a partir do 2º abastecimento.
17. **Etapa 17:** Implementação do Módulo de Manutenções (`Maintenance.php` Model, `MaintenanceCategory.php` Model, `MaintenanceController.php`, Views de formulário e acompanhamento por status).
18. **Etapa 18:** Implementação do Dashboard Gerencial (`DashboardController.php`, cálculo das 4 métricas do mês vigente e renderização de gráficos).
19. **Etapa 19:** Implementação do Módulo de Relatórios e Filtros (`ReportController.php`, Views de seleção).
20. **Etapa 20:** Implementação do exportador de relatórios em formato **CSV** com suporte a UTF-8 BOM (`\xEF\xBB\xBF`).
21. **Etapa 21:** Implementação do gerador de relatórios formatados em **PDF**.
22. **Etapa 22:** Implementação das telas de Configuração do Sistema exclusivas para Admin (Gestão de Usuários, Categorias e Secretarias).
23. **Etapa 23:** Revisão geral de segurança (validação de CSRF, escape de XSS com `htmlspecialchars`, verificação estrita de RBAC em todos os Controllers).
24. **Etapa 24:** Testes de aceitação integrados e preparação final para validação.

---

## 26. Critérios de Aceitação Técnica e Funcional

Para que o sistema seja considerado totalmente concluído e pronto para entrega, ele deve atender rigorosamente aos seguintes critérios:

- [ ] A arquitetura do projeto está organizada a partir do `[Diretório do Projeto - Repositório]`, funcionando em subpastas de servidores web sem dependência de caminhos públicos fixos (`public_html`, `public`, `htdocs`, `www`).
- [ ] O arquivo de configuração em código PHP (`config/config.php`) está criado, funcionando e protegido contra acesso direto por URL, sem qualquer uso de arquivo `.env`.
- [ ] Todas as pastas internas (`config/`, `app/`, `database/`, `logs/`) estão devidamente protegidas contra acesso direto pelo navegador via `.htaccess` e checagem de constante de segurança (`SYS_ENTRY`).
- [ ] O banco de dados é criado e atualizado exclusivamente via infraestrutura de migrations versionadas em `database/migrations/`, com o script `migrate.php` definindo `SYS_ENTRY` e tabela de controle de execuções duplicadas.
- [ ] A separação de responsabilidades pelo padrão MVC está estritamente respeitada (Models cuidando da lógica e SQL, Views cuidando apenas da interface e Controllers mediando e checando permissões).
- [ ] O login por `username` e `senha` funciona perfeitamente, com hash de senha BCrypt e controle de sessão seguro.
- [ ] O controle de acesso por perfil (RBAC) está ativo em 100% dos Controllers, bloqueando acessos indevidos e renderizando menus adaptados ao perfil logado.
- [ ] A trava de validação de quilometragem impede o salvamento de qualquer abastecimento com KM menor ou igual à anterior, exibindo mensagem clara em tela.
- [ ] O cálculo automático de consumo médio em $\text{KM/L}$ é realizado e exibido corretamente a partir do 2º abastecimento de cada veículo.
- [ ] A exclusão lógica (Soft Delete) de veículos inativa o carro para novos lançamentos, mas mantém seus dados íntegros em relatórios e históricos passados, resguardados por chaves estrangeiras `ON DELETE RESTRICT`.
- [ ] O Dashboard exibe corretamente as 4 métricas do mês vigente e os 2 gráficos gerenciais.
- [ ] A Ficha Individual do Veículo exibe os dados cadastrais, consumo médio e o histórico completo de abastecimentos e manutenções.
- [ ] A geração e download de relatórios nos formatos CSV (com UTF-8 BOM) e PDF funcionam sem erros com a aplicação dos filtros selecionados.
- [ ] O log de erros grava falhas no banco de dados e executa a contingência em arquivo físico `logs/system_error.log` em caso de indisponibilidade do banco.
- [ ] O log de segurança audita tentativas de login, bloqueios por validação de KM e ações administrativas.
- [ ] A interface do sistema respeita integralmente as diretrizes de cores, tipografia (Inter/Mono), layout e componentes definidos no `docs/DESIGN.md`, utilizando helper de placas na exibição visual.
- [ ] Nenhuma funcionalidade declarada fora de escopo (uploads, GPS, APIs, motoristas) foi criada ou inventada.

---

## 27. Pontos Pendentes e Decisões Futuras

Não foram identificadas pendências para iniciar a codificação com base neste FSD.

---

## 28. Conclusão

Este Documento de Especificação Funcional (FSD) encontra-se **100% concluído, corrigido, consolidado e pronto** para orientar a IA codificadora e os desenvolvedores no projeto e construção do sistema **SisFrota Municipal**.

Para a fase de codificação do sistema, devem ser fornecidos à IA codificadora apenas os seguintes documentos oficiais:

1. `docs/FSD.md` (este documento consolidado de especificação funcional e técnica);
2. `docs/DESIGN.md` (documento de especificação visual e sistema de design).

> [!NOTE]
> Os documentos preliminares de levantamento funcional (`PRD.md`) e decisões técnicas (`DECISOES_TECNICAS.md`) não são necessários para a fase de codificação, pois todo o seu conteúdo foi inteiramente refinado e incorporado neste FSD.
