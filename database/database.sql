CREATE TABLE `utilizadores` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nome` varchar(100),
  `email` varchar(100) UNIQUE,
  `password` varchar(255),
  `criado_em` datetime
);

CREATE TABLE `localizacoes` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `edificio` varchar(100),
  `piso` varchar(50),
  `servico` varchar(100),
  `sala` varchar(100)
);

CREATE TABLE `categorias` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nome` varchar(100),
  `descricao` text
);

CREATE TABLE `fornecedores` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `nome` varchar(150),
  `nif` varchar(20),
  `telefone` varchar(20),
  `email` varchar(100),
  `morada` text,
  `website` varchar(150),
  `pessoa_contacto` varchar(100),
  `telefone_contacto` varchar(20),
  `tipo` varchar(50),
  `observacoes` text
);

CREATE TABLE `equipamentos` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `codigo_interno` varchar(50) UNIQUE,
  `designacao` varchar(150),
  `marca` varchar(100),
  `modelo` varchar(100),
  `numero_serie` varchar(100),
  `fabricante` varchar(100),
  `data_aquisicao` date,
  `ano_fabrico` int,
  `custo_aquisicao` decimal(10,2),
  `tipo_entrada` varchar(50),
  `estado` varchar(50),
  `criticidade` varchar(50),
  `observacoes` text,
  `id_localizacao` int,
  `id_categoria` int
);

CREATE TABLE `equipamento_fornecedor` (
  `id_equipamento` int,
  `id_fornecedor` int
);

CREATE TABLE `documentos` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `tipo` varchar(100),
  `nome` varchar(150),
  `data_documento` date,
  `data_validade` date,
  `ficheiro` varchar(255),
  `observacoes` text,
  `id_equipamento` int,
  `id_fornecedor` int
);

CREATE TABLE `garantias` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `data_inicio` date,
  `data_fim` date,
  `observacoes` text,
  `id_equipamento` int
);

CREATE TABLE `contratos` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `tipo` varchar(100),
  `entidade_responsavel` varchar(150),
  `periodicidade` varchar(50),
  `data_inicio` date,
  `data_fim` date,
  `observacoes` text,
  `id_equipamento` int,
  `id_fornecedor` int
);

ALTER TABLE `equipamentos` ADD FOREIGN KEY (`id_localizacao`) REFERENCES `localizacoes` (`id`);

ALTER TABLE `equipamentos` ADD FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id`);

ALTER TABLE `equipamento_fornecedor` ADD FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id`);

ALTER TABLE `equipamento_fornecedor` ADD FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id`);

ALTER TABLE `documentos` ADD FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id`);

ALTER TABLE `documentos` ADD FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id`);

ALTER TABLE `garantias` ADD FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id`);

ALTER TABLE `contratos` ADD FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id`);

ALTER TABLE `contratos` ADD FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id`);
