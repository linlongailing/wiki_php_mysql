ALTER TABLE `cmf_auth_rule` MODIFY COLUMN `app` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '规则所属module' AFTER `status`;