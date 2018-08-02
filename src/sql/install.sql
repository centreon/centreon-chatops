CREATE TABLE mod_chatops_config (
  config_key VARCHAR(255) NOT NULL,
  config_value TEXT,
  PRIMARY KEY(config_key)
);

CREATE TABLE mod_chatops_token (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  token VARCHAR(255) NOT NULL,
  client VARCHAR(50) NOT NULL,
  active TINYINT(1) DEFAULT 0,
  create_at DATETIME DEFAULT NOW(),
  PRIMARY KEY(id),
  UNIQUE INDEX(token, client)
);

INSERT INTO mod_chatops_config (config_key, config_value) VALUES ('command_name', '"centreon"');

INSERT INTO `topology` (`topology_name`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`) VALUES
('ChatOps', 507, NULL, NULL, 40, NULL, NULL, '0', '1', '1'),
('Configuration', 507, 50741, 11, 40, './modules/centreon-chatops/pages/config.php', NULL, NULL, '1', '1'),
('Tokens', 507, 50742, 12, 40, './modules/centreon-chatops/pages/tokens.php', NULL, NULL, '1', '1');
