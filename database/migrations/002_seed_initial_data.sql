-- Migration 002: Seed de dados iniciais do sistema SisFrota Municipal
-- Insere usuario administrador padrao, secretarias municipais e categorias de manutencao iniciais

-- 1. Insercao do usuario Administrador padrao (username: admin / senha: admin123)
INSERT INTO `users` (`username`, `password_hash`, `full_name`, `role`, `status`)
VALUES (
  'admin',
  '$2y$10$3HJzaRQJ8URB3PrUGTLb2uL6/yEt51BngYZAirf5Y.dCeybMTaWW.',
  'Administrador do Sistema',
  'ADMIN',
  'ACTIVE'
) ON DUPLICATE KEY UPDATE `username` = `username`;

-- 2. Insercao das Secretarias Municipais Iniciais
INSERT INTO `secretariats` (`name`, `acronym`) VALUES
  ('Secretaria Municipal de Saúde', 'SMS'),
  ('Secretaria Municipal de Educação', 'SME'),
  ('Secretaria de Obras e Serviços Públicos', 'SEOSP'),
  ('Secretaria de Administração e Finanças', 'SEAD'),
  ('Secretaria de Assistência Social', 'SEMAS'),
  ('Gabinete do Prefeito', 'GAB')
ON DUPLICATE KEY UPDATE `name` = `name`;

-- 3. Insercao das Categorias de Manutenção Mecânica Iniciais
INSERT INTO `maintenance_categories` (`name`, `description`, `status`) VALUES
  ('Troca de Óleo e Filtros', 'Substituição preventiva de óleo lubrificante e filtros do motor.', 'ACTIVE'),
  ('Sistema de Freios', 'Inspeção e substituição de pastilhas, discos, lonas e fluido de freio.', 'ACTIVE'),
  ('Substituição de Pneus', 'Troca de pneus, alinhamento e balanceamento das rodas.', 'ACTIVE'),
  ('Revisão Elétrica e Bateria', 'Reparo na fiação elétrica, faróis, alternadores e troca de bateria.', 'ACTIVE'),
  ('Funilaria e Pintura', 'Reparos estéticos e estruturais em lataria e pintura veicular.', 'ACTIVE'),
  ('Manutenção de Suspensão e Direção', 'Substituição de amortecedores, pivôs, buchas e terminais de direção.', 'ACTIVE'),
  ('Manutenção do Motor e Câmbio', 'Reparos mecânicos complexos em motorização e sistema de transmissão.', 'ACTIVE')
ON DUPLICATE KEY UPDATE `name` = `name`;
