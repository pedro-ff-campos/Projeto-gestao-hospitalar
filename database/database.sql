USE `db1240773`;

-- ── 1. TABELA DE UTILIZADORES ──
CREATE TABLE IF NOT EXISTS `utilizadores` (
  `id` INT AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `hospital` VARCHAR(150) DEFAULT 'Hospital Geral',
  `criado_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

-- ── 2. TABELA DE AUDITORIA (LOGS DE SEGURANÇA) ──
CREATE TABLE IF NOT EXISTS `logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `utilizador_id` INT NULL,
  `acao` VARCHAR(50) NOT NULL,
  `detalhes` TEXT NOT NULL,
  `criado_at` DATETIME NOT NULL,
  CONSTRAINT `fk_logs_utilizador` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizadores` (`id`) ON DELETE SET NULL
);

-- ── 3. TABELA DE LOCALIZAÇÕES ──
CREATE TABLE IF NOT EXISTS `localizacoes` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `edificio` VARCHAR(100) NOT NULL,
  `piso` VARCHAR(50) NOT NULL,
  `servico` VARCHAR(100) NOT NULL,
  `sala` VARCHAR(100) NOT NULL
);

-- ── 4. TABELA DE CATEGORIAS ──
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `descricao` TEXT
);

-- ── 5. TABELA DE FORNECEDORES ──
CREATE TABLE IF NOT EXISTS `fornecedores` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `nome` VARCHAR(150) NOT NULL,
  `nif` VARCHAR(20) NOT NULL,
  `telefone` VARCHAR(20),
  `email` VARCHAR(100),
  `morada` TEXT,
  `website` VARCHAR(150),
  `pessoa_contacto` VARCHAR(100),
  `telefone_contacto` VARCHAR(20),
  `tipo` VARCHAR(50),
  `observacoes` TEXT
);

-- ── 6. TABELA DE EQUIPAMENTOS ──
CREATE TABLE IF NOT EXISTS `equipamentos` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `codigo` VARCHAR(50) NOT NULL UNIQUE,
  `designacao` VARCHAR(150) NOT NULL,
  `marca` VARCHAR(100),
  `modelo` VARCHAR(100),
  `numero_serie` VARCHAR(100),
  `fabricante` VARCHAR(100),
  `data_aquisicao` DATE,
  `ano_fabrico` INT,
  `custo_aquisicao` DECIMAL(10,2),
  `tipo_entrada` VARCHAR(50),
  `estado` VARCHAR(50) NOT NULL,
  `criticidade` VARCHAR(50) NOT NULL,
  `observacoes` TEXT,
  `id_localizacao` INT NOT NULL,
  `id_categoria` INT NOT NULL
);

-- ── 7. TABELA DE LIGAÇÃO EQUIPAMENTO/FORNECEDOR ──
CREATE TABLE IF NOT EXISTS `equipamento_fornecedor` (
  `id_equipamento` INT NOT NULL,
  `id_fornecedor` INT NOT NULL,
  PRIMARY KEY (`id_equipamento`, `id_fornecedor`)
);

-- ── 8. TABELA DE DOCUMENTAÇÃO TÉCNICA ──
CREATE TABLE IF NOT EXISTS `documentacao` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `tipo` VARCHAR(100) NOT NULL,
  `titulo` VARCHAR(150) NOT NULL,
  `data_documento` DATE,
  `data_validade` DATE,
  `ficheiro` VARCHAR(255) NOT NULL,
  `observacoes` TEXT,
  `id_equipamento` INT NOT NULL,
  `id_fornecedor` INT NULL
);

-- ── 9. TABELA DE GARANTIAS ──
CREATE TABLE IF NOT EXISTS `garantias` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `referencia` VARCHAR(100) DEFAULT '—',
  `fornecedor_garantia` VARCHAR(150) DEFAULT '—',
  `data_inicio` DATE,
  `data_fim` DATE NOT NULL,
  `observacoes` TEXT,
  `id_equipamento` INT NOT NULL
);

-- ── 10. TABELA DE CONTRATOS DE MANUTENÇÃO ──
CREATE TABLE IF NOT EXISTS `contratos` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `numero_contrato` VARCHAR(100) NOT NULL,
  `tipo` VARCHAR(100),
  `entidade_responsavel` VARCHAR(150),
  `periodicidade` VARCHAR(50),
  `data_inicio` DATE,
  `data_fim` DATE NOT NULL,
  `valor` DECIMAL(10,2) NULL,
  `observacoes` TEXT,
  `id_equipamento` INT NOT NULL,
  `id_fornecedor` INT NULL
);

-- ── 11. TABELA DE CONTEÚDOS PÚBLICOS (CMS) ──
CREATE TABLE IF NOT EXISTS `conteudos_publicos` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `chave` VARCHAR(100) NOT NULL UNIQUE,
  `valor` TEXT NOT NULL,
  `descricao` VARCHAR(255),
  `atualizado_em` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ── INSERÇÃO DOS DADOS DO SITE PÚBLICO ──
INSERT INTO `conteudos_publicos` (`chave`, `valor`, `descricao`) VALUES
('titulo_hero', 'O inventário hospitalar que o seu hospital merece', 'Título principal da página'),
('texto_hero', 'A MedInvent desenvolve soluções web para a gestão centralizada de equipamentos médicos.', 'Texto da secção hero'),
('titulo_sobre', 'Uma empresa focada na saúde digital', 'Título da secção sobre nós'),
('texto_sobre', 'A MedInvent é uma empresa especializada no desenvolvimento de sistemas de informação para instituições de saúde.', 'Texto da secção sobre nós'),
('email', 'geral@medinvent.pt', 'Email de contacto'),
('telefone', '+351 220 000 000', 'Telefone de contacto'),
('morada', 'Rua Dr. António Bernardino de Almeida, Porto', 'Morada da empresa')
ON DUPLICATE KEY UPDATE `chave`=`chave`;


-- ════════════ RESTRIÇÕES DE INTEGRIDADE (FOREIGN KEYS) ════════════

ALTER TABLE `equipamentos` ADD CONSTRAINT `fk_eq_localizacao` FOREIGN KEY (`id_localizacao`) REFERENCES `localizacoes` (`id`) ON DELETE RESTRICT;
ALTER TABLE `equipamentos` ADD CONSTRAINT `fk_eq_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id`) ON DELETE RESTRICT;

ALTER TABLE `equipamento_fornecedor` ADD CONSTRAINT `fk_ef_equipamento` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id`) ON DELETE CASCADE;
ALTER TABLE `equipamento_fornecedor` ADD CONSTRAINT `fk_ef_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id`) ON DELETE RESTRICT;

ALTER TABLE `documentacao` ADD CONSTRAINT `fk_doc_equipamento` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id`) ON DELETE CASCADE;
ALTER TABLE `documentacao` ADD CONSTRAINT `fk_doc_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id`) ON DELETE RESTRICT;

ALTER TABLE `garantias` ADD CONSTRAINT `fk_gar_equipamento` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id`) ON DELETE CASCADE;

ALTER TABLE `contratos` ADD CONSTRAINT `fk_ctr_equipamento` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id`) ON DELETE CASCADE;
ALTER TABLE `contratos` ADD CONSTRAINT `fk_ctr_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id`) ON DELETE RESTRICT;

