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

