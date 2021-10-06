/*
SQLyog Community
MySQL - 10.4.11-MariaDB : Database - organizza
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`organizza` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `organizza`;

/*Table structure for table `categoria` */

DROP TABLE IF EXISTS `categoria`;

CREATE TABLE `categoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(45) NOT NULL,
  `ativo` enum('S','N') NOT NULL DEFAULT 'S',
  `usuario_id` int(11) NOT NULL,
  `data_cadastro` datetime DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT NULL,
  `tipo_categoria` enum('DESPESA','RECEITA') DEFAULT 'DESPESA',
  PRIMARY KEY (`id`),
  KEY `fk_categoria_usuario_idx` (`usuario_id`),
  CONSTRAINT `fk_categoria_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8;

/*Data for the table `categoria` */

insert  into `categoria`(`id`,`descricao`,`ativo`,`usuario_id`,`data_cadastro`,`data_atualizacao`,`tipo_categoria`) values (20,'Casa','S',3,'2021-09-24 01:26:40',NULL,'DESPESA');
insert  into `categoria`(`id`,`descricao`,`ativo`,`usuario_id`,`data_cadastro`,`data_atualizacao`,`tipo_categoria`) values (21,'Educação','S',3,'2021-09-24 01:26:55',NULL,'DESPESA');
insert  into `categoria`(`id`,`descricao`,`ativo`,`usuario_id`,`data_cadastro`,`data_atualizacao`,`tipo_categoria`) values (22,'Eletrônicos','S',3,'2021-09-24 01:27:12',NULL,'DESPESA');
insert  into `categoria`(`id`,`descricao`,`ativo`,`usuario_id`,`data_cadastro`,`data_atualizacao`,`tipo_categoria`) values (23,'Lazer','S',3,'2021-09-24 01:27:16',NULL,'DESPESA');
insert  into `categoria`(`id`,`descricao`,`ativo`,`usuario_id`,`data_cadastro`,`data_atualizacao`,`tipo_categoria`) values (24,'Restaurante','S',3,'2021-09-24 01:28:14',NULL,'DESPESA');
insert  into `categoria`(`id`,`descricao`,`ativo`,`usuario_id`,`data_cadastro`,`data_atualizacao`,`tipo_categoria`) values (25,'Saúde','S',3,'2021-09-24 01:28:18',NULL,'DESPESA');
insert  into `categoria`(`id`,`descricao`,`ativo`,`usuario_id`,`data_cadastro`,`data_atualizacao`,`tipo_categoria`) values (26,'Serviços','S',3,'2021-09-24 01:28:24',NULL,'DESPESA');
insert  into `categoria`(`id`,`descricao`,`ativo`,`usuario_id`,`data_cadastro`,`data_atualizacao`,`tipo_categoria`) values (27,'Alimentação','S',3,'2021-09-24 01:28:35','2021-09-24 03:00:02','DESPESA');
insert  into `categoria`(`id`,`descricao`,`ativo`,`usuario_id`,`data_cadastro`,`data_atualizacao`,`tipo_categoria`) values (28,'Transporte','S',3,'2021-09-24 02:07:13',NULL,'DESPESA');
insert  into `categoria`(`id`,`descricao`,`ativo`,`usuario_id`,`data_cadastro`,`data_atualizacao`,`tipo_categoria`) values (29,'Vestuário','S',3,'2021-09-24 02:07:48',NULL,'DESPESA');
insert  into `categoria`(`id`,`descricao`,`ativo`,`usuario_id`,`data_cadastro`,`data_atualizacao`,`tipo_categoria`) values (30,'Viagem','S',3,'2021-09-24 02:07:57','2021-09-24 02:22:01','DESPESA');
insert  into `categoria`(`id`,`descricao`,`ativo`,`usuario_id`,`data_cadastro`,`data_atualizacao`,`tipo_categoria`) values (31,'Outros','S',3,'2021-09-24 02:08:07',NULL,'DESPESA');
insert  into `categoria`(`id`,`descricao`,`ativo`,`usuario_id`,`data_cadastro`,`data_atualizacao`,`tipo_categoria`) values (33,'Celular/TV/Internet','S',3,'2021-09-24 02:36:47',NULL,'DESPESA');
insert  into `categoria`(`id`,`descricao`,`ativo`,`usuario_id`,`data_cadastro`,`data_atualizacao`,`tipo_categoria`) values (34,'Freelancer','S',3,'2021-09-24 02:55:49','2021-09-24 02:59:36','RECEITA');
insert  into `categoria`(`id`,`descricao`,`ativo`,`usuario_id`,`data_cadastro`,`data_atualizacao`,`tipo_categoria`) values (36,'Emprego compreoseu','S',3,'2021-09-24 02:59:15','2021-09-27 00:27:50','RECEITA');
insert  into `categoria`(`id`,`descricao`,`ativo`,`usuario_id`,`data_cadastro`,`data_atualizacao`,`tipo_categoria`) values (37,'Outros','S',3,'2021-09-24 03:12:02','2021-09-28 01:50:38','RECEITA');
insert  into `categoria`(`id`,`descricao`,`ativo`,`usuario_id`,`data_cadastro`,`data_atualizacao`,`tipo_categoria`) values (50,'Alimentação','S',10,'2021-09-28 02:15:53',NULL,'DESPESA');
insert  into `categoria`(`id`,`descricao`,`ativo`,`usuario_id`,`data_cadastro`,`data_atualizacao`,`tipo_categoria`) values (51,'Transporte','S',10,'2021-09-28 02:16:00',NULL,'DESPESA');
insert  into `categoria`(`id`,`descricao`,`ativo`,`usuario_id`,`data_cadastro`,`data_atualizacao`,`tipo_categoria`) values (52,'Freelancer','S',10,'2021-09-28 02:16:09',NULL,'RECEITA');

/*Table structure for table `lancamento` */

DROP TABLE IF EXISTS `lancamento`;

CREATE TABLE `lancamento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria_id` int(11) NOT NULL,
  `tipo_lancamento` enum('DESPESA','RECEITA') DEFAULT 'DESPESA',
  `situacao` enum('PENDENTE','RESOLVIDO','CANCELADO') DEFAULT 'PENDENTE',
  `periodicidade` enum('MENSAL','ANUAL') DEFAULT 'MENSAL',
  `descricao` varchar(45) NOT NULL,
  `data_lancamento` date DEFAULT NULL,
  `data_vencimento` date DEFAULT NULL,
  `valor` double(10,2) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `data_cadastro` datetime DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT NULL,
  PRIMARY KEY (`id`,`valor`),
  KEY `fk_lancamento_categoria1_idx` (`categoria_id`),
  CONSTRAINT `fk_lancamento_categoria10` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8;

/*Data for the table `lancamento` */

insert  into `lancamento`(`id`,`categoria_id`,`tipo_lancamento`,`situacao`,`periodicidade`,`descricao`,`data_lancamento`,`data_vencimento`,`valor`,`usuario_id`,`data_cadastro`,`data_atualizacao`) values (40,36,'RECEITA','RESOLVIDO','MENSAL','Salário','2021-09-01',NULL,1472.50,3,'2021-09-23 22:01:13',NULL);
insert  into `lancamento`(`id`,`categoria_id`,`tipo_lancamento`,`situacao`,`periodicidade`,`descricao`,`data_lancamento`,`data_vencimento`,`valor`,`usuario_id`,`data_cadastro`,`data_atualizacao`) values (41,20,'DESPESA','PENDENTE','MENSAL','Talão de água','2021-10-11',NULL,80.33,3,'2021-09-23 22:02:54',NULL);
insert  into `lancamento`(`id`,`categoria_id`,`tipo_lancamento`,`situacao`,`periodicidade`,`descricao`,`data_lancamento`,`data_vencimento`,`valor`,`usuario_id`,`data_cadastro`,`data_atualizacao`) values (42,28,'DESPESA','PENDENTE','MENSAL','Prestação da moto','2021-10-11',NULL,456.42,3,'2021-09-23 22:09:37',NULL);
insert  into `lancamento`(`id`,`categoria_id`,`tipo_lancamento`,`situacao`,`periodicidade`,`descricao`,`data_lancamento`,`data_vencimento`,`valor`,`usuario_id`,`data_cadastro`,`data_atualizacao`) values (43,37,'RECEITA','RESOLVIDO','MENSAL','Antecipação prestação moto','2021-09-12',NULL,270.80,3,'2021-09-23 22:13:11',NULL);
insert  into `lancamento`(`id`,`categoria_id`,`tipo_lancamento`,`situacao`,`periodicidade`,`descricao`,`data_lancamento`,`data_vencimento`,`valor`,`usuario_id`,`data_cadastro`,`data_atualizacao`) values (44,28,'DESPESA','RESOLVIDO','MENSAL','Antecipação prestação moto','2021-09-12',NULL,270.80,3,'2021-09-23 22:13:36',NULL);
insert  into `lancamento`(`id`,`categoria_id`,`tipo_lancamento`,`situacao`,`periodicidade`,`descricao`,`data_lancamento`,`data_vencimento`,`valor`,`usuario_id`,`data_cadastro`,`data_atualizacao`) values (45,26,'DESPESA','PENDENTE','MENSAL','Seguro da moto','2021-10-11',NULL,57.81,3,'2021-09-23 22:14:36',NULL);
insert  into `lancamento`(`id`,`categoria_id`,`tipo_lancamento`,`situacao`,`periodicidade`,`descricao`,`data_lancamento`,`data_vencimento`,`valor`,`usuario_id`,`data_cadastro`,`data_atualizacao`) values (46,36,'RECEITA','PENDENTE','MENSAL','Salário','2021-10-01',NULL,1472.50,3,'2021-09-23 22:19:32',NULL);
insert  into `lancamento`(`id`,`categoria_id`,`tipo_lancamento`,`situacao`,`periodicidade`,`descricao`,`data_lancamento`,`data_vencimento`,`valor`,`usuario_id`,`data_cadastro`,`data_atualizacao`) values (47,34,'RECEITA','RESOLVIDO','MENSAL','Projeto recanto','2021-09-22',NULL,100.00,3,'2021-09-23 22:21:45',NULL);
insert  into `lancamento`(`id`,`categoria_id`,`tipo_lancamento`,`situacao`,`periodicidade`,`descricao`,`data_lancamento`,`data_vencimento`,`valor`,`usuario_id`,`data_cadastro`,`data_atualizacao`) values (48,28,'DESPESA','RESOLVIDO','MENSAL','Prestação da moto','2021-09-11',NULL,453.42,3,'2021-09-23 22:24:15',NULL);
insert  into `lancamento`(`id`,`categoria_id`,`tipo_lancamento`,`situacao`,`periodicidade`,`descricao`,`data_lancamento`,`data_vencimento`,`valor`,`usuario_id`,`data_cadastro`,`data_atualizacao`) values (49,20,'DESPESA','RESOLVIDO','MENSAL','Gás de cozinha','2021-09-11',NULL,105.00,3,'2021-09-23 22:24:55',NULL);
insert  into `lancamento`(`id`,`categoria_id`,`tipo_lancamento`,`situacao`,`periodicidade`,`descricao`,`data_lancamento`,`data_vencimento`,`valor`,`usuario_id`,`data_cadastro`,`data_atualizacao`) values (64,51,'DESPESA','CANCELADO','MENSAL','Combustível carro','2021-09-10',NULL,100.00,10,'2021-09-27 21:16:41','2021-09-28 02:17:59');
insert  into `lancamento`(`id`,`categoria_id`,`tipo_lancamento`,`situacao`,`periodicidade`,`descricao`,`data_lancamento`,`data_vencimento`,`valor`,`usuario_id`,`data_cadastro`,`data_atualizacao`) values (65,50,'DESPESA','CANCELADO','MENSAL','Supermercado','2021-09-04',NULL,1000.00,10,'2021-09-27 21:17:02','2021-09-28 02:18:00');
insert  into `lancamento`(`id`,`categoria_id`,`tipo_lancamento`,`situacao`,`periodicidade`,`descricao`,`data_lancamento`,`data_vencimento`,`valor`,`usuario_id`,`data_cadastro`,`data_atualizacao`) values (66,52,'RECEITA','CANCELADO','MENSAL','Projeto teste','2021-09-27',NULL,4000.00,10,'2021-09-27 21:17:20','2021-09-28 02:18:01');
insert  into `lancamento`(`id`,`categoria_id`,`tipo_lancamento`,`situacao`,`periodicidade`,`descricao`,`data_lancamento`,`data_vencimento`,`valor`,`usuario_id`,`data_cadastro`,`data_atualizacao`) values (67,52,'RECEITA','CANCELADO','MENSAL','Correção projeto','2021-09-01',NULL,200.00,10,'2021-09-27 21:17:52','2021-09-28 02:18:02');

/*Table structure for table `usuario` */

DROP TABLE IF EXISTS `usuario`;

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) NOT NULL,
  `email` varchar(45) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `ativo` enum('S','N') NOT NULL DEFAULT 'S',
  `data_cadastro` datetime DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

/*Data for the table `usuario` */

insert  into `usuario`(`id`,`nome`,`email`,`senha`,`ativo`,`data_cadastro`,`data_atualizacao`) values (3,'Carlos César','ce-sarr@hotmail.com','8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92','S','2021-09-17 21:11:22',NULL);
insert  into `usuario`(`id`,`nome`,`email`,`senha`,`ativo`,`data_cadastro`,`data_atualizacao`) values (9,'Carlos César','cesardev@gmail.com','8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92','S','2021-09-27 21:12:36',NULL);
insert  into `usuario`(`id`,`nome`,`email`,`senha`,`ativo`,`data_cadastro`,`data_atualizacao`) values (10,'Carlos César','cesarlimadev@gmail.com','8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92','S','2021-09-27 21:15:29',NULL);
insert  into `usuario`(`id`,`nome`,`email`,`senha`,`ativo`,`data_cadastro`,`data_atualizacao`) values (11,'teste','teste@qweqwe.com','8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92','S','2021-10-06 15:30:42',NULL);
insert  into `usuario`(`id`,`nome`,`email`,`senha`,`ativo`,`data_cadastro`,`data_atualizacao`) values (12,'qqq','qteste@gmail.com','8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92','S','2021-10-06 15:32:08',NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
