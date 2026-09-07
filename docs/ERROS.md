# Registro de Erros e Soluções - SisFrota Municipal

Este arquivo é destinado a registrar falhas técnicas, bugs de execução e comportamentos inesperados encontrados durante o desenvolvimento ou execução de testes do sistema, descrevendo suas causas e as soluções adotadas para evitar regressões.

---

## Modelo de Registro

```text
## <data> - <título curto do erro>

- Sintoma:
- Causa:
- Solução aplicada:
- Como evitar no futuro:
```

---

## Histórico de Ocorrências

## 25/08/2026 - Exceção "There is no active transaction" na execução de DDL no MySQL via PDO

- **Sintoma:** Ao executar o script CLI `database/migrate.php`, a migration `001_create_system_tables.sql` falhou com a mensagem: `Erro ao aplicar a migration 001_create_system_tables.sql: There is no active transaction`.
- **Causa:** No MySQL/MariaDB, instruções DDL (como `CREATE TABLE`, `ALTER TABLE`, `DROP TABLE`) disparam um *implicit commit* automático. Ao executar um comando `CREATE TABLE` dentro de um bloco `$pdo->beginTransaction()`, o MySQL encerra a transação imediatamente, fazendo com que a chamada posterior `$pdo->commit()` lance a exceção.
- **Solução aplicada:** Removido o encapsulamento de transação explícita (`$pdo->beginTransaction()`) no executor de migrations DDL `database/migrate.php`. O controle de idempotência e ordenação passa pela verificação individual de cada arquivo na tabela `migrations`.
- **Como evitar no futuro:** Nunca envolver instruções DDL do MySQL dentro de transações manuais do PDO.

## 06/09/2026 - Undefined array key "n" em _putimages do FPDF ao referenciar array por valor

- **Sintoma:** Ao compilar um relatório PDF contendo o logo da prefeitura, o PHP 8.x disparou o warning: `Undefined array key "n" in fpdf.php on line 1300`.
- **Causa:** O método `_putimages()` iterava sobre `$this->images` usando `foreach ($this->images as $file => $info)`. A função `_putimage(&$info)` atribuía `$info['n'] = $this->n;` apenas na variável temporária por valor, não persistindo a chave `'n'` no array `$this->images`, gerando falha no momento de registrar o `/XObject` no dicionário de recursos.
- **Solução aplicada:** Alterado o loop para utilizar passagem por referência explícita: `foreach ($this->images as $file => &$info)` com posterior `unset($info)`.
- **Como evitar no futuro:** Sempre utilizar referência ao iterar coleções em que métodos internos modificam as propriedades dos elementos contidos.

## 06/09/2026 - Call to undefined method FormatHelper::formatDateTime() e formatMoney()

- **Sintoma:** Nas páginas de listagem de abastecimentos (`/fuelings`), manutenções (`/maintenances`), dashboard (`/dashboard`) e exportações de relatórios (`/reports/pdf` e `/reports/csv`), ocorria a tela de "Falha Técnica" com log `Call to undefined method FormatHelper::formatDateTime()` ou `FormatHelper::formatMoney()`.
- **Causa:** O helper `FormatHelper.php` possuía apenas `formatDate()` e `formatCurrency()`, enquanto diversas views e controllers utilizavam `formatDateTime()` para exibição de data e hora (DD/MM/AAAA HH:MM) e `formatMoney()` como alias de formatação de moeda.
- **Solução aplicada:** Implementados os métodos estáticos `FormatHelper::formatDateTime(?string $dateTime): string` e `FormatHelper::formatMoney(float|int|string|null $value): string` retornando as formatações corretas com fallback para valores nulos.
- **Como evitar no futuro:** Verificar se todos os métodos utilitários invocados pelas views e controladores existem na classe helper correspondente.

