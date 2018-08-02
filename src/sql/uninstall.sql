DROP TABLE mod_chatops_config;
DROP TABLE mod_chatops_token;

DELETE FROM topology WHERE topology_page = 50741;
DELETE FROM topology WHERE topology_page = 50742;
DELETE FROM topology WHERE topology_name = 'ChatOps';
