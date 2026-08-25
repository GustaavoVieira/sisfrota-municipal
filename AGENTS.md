# Guia do Agente IA - SisFrota Municipal

Este arquivo define as diretrizes, stack, arquitetura, regras de segurança e protocolo de operação para qualquer agente de IA que atue neste repositório em chats futuros.

---

## 1. Idioma e Comunicação
* **Idioma:** Responder sempre em **português do Brasil**.
* **Estilo:** Respostas claras, objetivas e técnicas.

---

## 2. Stack Tecnológica e Arquitetura
* **Backend:** PHP (versão 8.0 ou superior) em arquitetura **MVC (Model-View-Controller)**.
* **Banco de Dados Relacional:** MySQL (versão 8.0) ou MariaDB (versão 10.4 ou superior) acessado estritamente via PDO com Prepared Statements.
* **Frontend:** HTML5 semântico, CSS3 customizado estruturado com as variáveis do `docs/DESIGN.md`, JavaScript puro (Vanilla JS - ES6+) e Framework Bootstrap (versão 5.x).
* **Bibliotecas Locais (Sem CDNs):**
  * **Gráficos do Dashboard:** Chart.js (versão minificada mantida em `assets/js/vendor/chart.min.js`).
  * **Geração de PDF:** FPDF (mantida localmente no projeto).
* **Ponto de Entrada Único:** `index.php` atua como bootstrap e roteador central.
* **Configuração Técnica:** `config/config.php` (proibido o uso de arquivo `.env`).
* **Segurança de Entrada:** Todo arquivo interno deve validar a constante de segurança `SYS_ENTRY` (ex: `defined('SYS_ENTRY') or die('Acesso direto negado.');`).
* **Fuso Horário:** Obrigatoriamente configurado para `America/Sao_Paulo`.

---

## 3. Ambientes
* **Desenvolvimento Local:** Servidor Apache com PHP 8.x + MySQL/MariaDB via XAMPP no Windows, navegável em `http://localhost/nome-do-projeto/`.
* **Homologação / Testes:** Testes funcionais executados diretamente no ambiente local.
* **Produção:** Servidor web gerenciado compatível com PHP e MySQL (ex: Hostnet/Vercel com DB gerenciado), prevendo migração para a infraestrutura física da Prefeitura.

---

## 4. Estrutura de Diretórios

```
[Diretório do Projeto - Repositório]/
├── index.php                      # Arquivo de entrada principal e roteador
├── .htaccess                      # Proteção e reescrita de URLs
├── AGENTS.md                      # Instruções de contexto para IA
├── config/                        # Configurações sensíveis (protegido por .htaccess)
│   ├── config.php                 # Arquivo de configuração PHP
│   └── .htaccess
├── app/                           # Código-fonte MVC (protegido por .htaccess)
│   ├── .htaccess
│   ├── controllers/               # Controllers da aplicação
│   ├── models/                    # Models da aplicação
│   ├── helpers/                   # Helpers e utilitários de formatação
│   └── views/                     # Views da aplicação
├── assets/                        # Recursos públicos estáticos
│   ├── css/                       # CSS customizado e Bootstrap
│   ├── js/                        # Scripts JS e bibliotecas locais
│   └── img/                       # Logotipos e imagens da interface
├── database/                      # Migrations e scripts de banco (protegido por .htaccess)
│   ├── .htaccess
│   ├── migrations/                # Scripts SQL versionados
│   └── migrate.php                # Script CLI de execução de migrations
├── docs/                          # Documentação e insumos do projeto
│   ├── FSD.md                     # Especificação funcional e técnica principal
│   ├── DESIGN.md                  # Especificação visual e sistema de design
│   ├── INSUMOS.md                 # Inventário de insumos de apoio
│   ├── PLANO.md                   # Plano de construção incremental
│   ├── STATUS.md                  # Estado atual do projeto
│   └── ERROS.md                   # Registro de erros e soluções
└── logs/                          # Registros em arquivo (protegido por .htaccess)
    ├── .htaccess
    └── system_error.log           # Contingência de log em disco
```

---

## 5. Regras de Segurança

* **Prevenção contra SQL Injection:** Uso obrigatório de instruções preparadas (*Prepared Statements*) via PDO em todas as consultas SQL. Proibida concatenação direta de variáveis em strings SQL.
* **Prevenção contra XSS (Cross-Site Scripting):** Higienização de todas as saídas dinâmicas no HTML utilizando `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')`.
* **Prevenção contra CSRF:** Formulários POST devem gerar e validar um token CSRF gravado em sessão.
* **Armazenamento Seguro de Senhas:** Senhas nunca são armazenadas em texto puro. Utilizar `password_hash($password, PASSWORD_DEFAULT)` com algoritmo BCrypt e verificação por `password_verify`.
* **Segurança de Sessão:** Tempo de expiração de 30 minutos por inatividade, chamada obrigatória de `session_regenerate_id(true)` no login, e cookies configurados com `httponly = true`, `samesite = 'Lax'`.
* **RBAC no Backend:** Controle de acesso por perfil (`ADMIN`, `OPERATOR`, `MANAGER`) validado em nível de Controller antes de executar ações ou renderizar Views. Retornar HTTP 403 Forbidden para acessos negados.
* **Proteção de Arquivos Internos:** Pastas `config/`, `app/`, `database/` e `logs/` protegidas por `.htaccess` (`Require all denied`) e checagem de constante `SYS_ENTRY`.
* **Logs Protegidos:** Gravação em banco (`system_logs`) com fallback para `logs/system_error.log` em caso de falha no MySQL.

---

## 6. Interface e Sistema de Design
* Seguir estritamente `docs/DESIGN.md`.
* **Cores:** Verde Municipal (`#16a34a`), Verde Escuro (`#006b2c`), Amarelo Alerta (`#facc15`), Fundo Slate (`#f8fafc`), Texto Charcoal (`#0f172a`), Bordas (`#e2e8f0`).
* **Tipografia:** Fonte **Inter** para texto geral e **monospace** (`data-mono`) para placas, números, valores em R$ e datas.
* **Formatação de Placas:** Utilizar o helper `FormatHelper::formatPlate($plate)` para exibir placas nos padrões tradicional (`ABC-1234`) ou Mercosul (`ABC1D23`).

---

## 7. Protocolo dos Arquivos Vivos

Antes de iniciar qualquer trabalho:
1. Ler `docs/FSD.md`.
2. Ler `docs/DESIGN.md`.
3. Ler `docs/INSUMOS.md`.
4. Ler `docs/PLANO.md`.
5. Ler `docs/STATUS.md`.
6. Ler `docs/ERROS.md`.

Use sempre caminhos relativos à raiz do projeto.
Não transformar estes caminhos em links absolutos.
Não usar links `file:///`.
Não registrar caminhos locais da máquina atual dentro do `AGENTS.md`.

Ao terminar qualquer trabalho:
1. Atualizar `docs/STATUS.md`.
2. Registrar erros e soluções em `docs/ERROS.md`, se houver.
3. Informar ao usuário o que foi feito.
4. Informar como testar ou validar a entrega.

---

## 8. Boas Práticas de Código
* Código limpo, legível e modular.
* Nomes descritivos para classes, métodos e variáveis em inglês ou português coerente com o FSD.
* Comentários úteis em português do Brasil quando necessários.
* Não criar funcionalidades fora do escopo definido no `docs/FSD.md`.
