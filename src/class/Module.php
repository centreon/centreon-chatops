<?php
/*
 * Copyright 2018 Centreon (http://www.centreon.com/)
 *
 * Centreon is a full-fledged industry-strength solution that meets
 * the needs in IT infrastructure and application monitoring for
 * service performance.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,*
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace ChatOpsModule;

use ChatOpsModule\CommandRegistry;

/**
 * The module object
 *
 * @package ChatOpsModule
 * @version 1.0.0
 * @license Apache-2.0
 */
class Module
{
  /**
   * @var string The base path of Centreon Web
   */
    protected $centreonPath;
  /**
   * @var CentreonDB The configuration database
   */
    protected $dbCentreon;
  /**
   * @var CentreonDB The realtime database
   */
    protected $dbCentreonStorage;
  /**
   * @var array The module configuration
   */
    protected $config;
  /**
   * @var ChatOpsModule\CommandRegistry The command registry
   */
    protected $registry = null;

  /**
   * Constructor
   *
   * @param string $centreonPath The base path of Centreon Web
   * @param CentreonDB $dbCentreon The configuration database
   * @param CentreonDB $dbCentreonStorage The realtime database
   */
    public function __construct($centreonPath, $dbCentreon, $dbCentreonStorage)
    {
        $this->centreonPath = $centreonPath;
        $this->dbCentreon = $dbCentreon;
        $this->dbCentreonStorage = $dbCentreonStorage;

        $this->loadConfiguration();
    }

  /**
   * Load the module configuration
   */
    protected function loadConfiguration()
    {
        $query = 'SELECT config_key, config_value FROM mod_chatops_config';
        $res = $this->dbCentreon->query($query);
        if (\PEAR::isError($res)) {
            throw new \Exception('Error during execute query');
        }
        while ($row = $res->fetchRow()) {
            $this->config[$row['config_key']] = json_decode($row['config_value'], true);
        }
    }

  /**
   * Scan a directories for find commands
   *
   * @param string $directory The directory to scan
   * @param string $ps4 If use the namespace
   * @param string $baseDir The base directory for find classname
   */
    protected function scanCommand($directory, $ps4 = null, $baseDir = '')
    {
        if ($baseDir === '') {
            $baseDir = $directory;
        }
        $listFs = new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS);
        foreach ($listFs as $fs) {
            if ($fs->isDir()) {
                $this->scanCommand($fs->getPathname(), $ps4, $baseDir);
            } elseif (preg_match('/.+\.php$/i', $fs->getPathname())) {
                $file = str_replace('\\', '/', $fs->getPathname());
                $classFile = str_replace('/', '\\', str_replace($baseDir, '', str_replace('.php', '', $file)));
                if (!is_null($ps4)) {
                    $cmdClass = str_replace('\\\\', '\\', '\\' . $ps4 . $classFile);
                } else {
                    require_once $file;
                    $cmdClass = str_replace('\\', '_', $classFile);
                }
                $cmdObj = new $cmdClass($this);
                $this->registry->register($cmdObj);
            }
        }
    }

  /**
   * Get a configuration value
   *
   * @param string $key The config key name
   * @return mixed The config value or null is not exists
   */
    public function getConfig($key)
    {
        if (!isset($this->config[$key])) {
            return null;
        }
        return $this->config[$key];
    }

  /**
   * Set the configuration value
   *
   * @param string $key The config key name
   * @param mixed $value The config value
   */
    public function setConfig($key, $value)
    {
        $this->config[$key] = $value;
    }

  /**
   * Save the module configuration
   */
    public function saveConfig()
    {
        $queryDelete = 'DELETE FROM mod_chatops_config';
        $res = $this->dbCentreon->query($queryDelete);
        if (\PEAR::isError($res)) {
            throw new \Exception('Error during execute query');
        }
        $stmt = $this->dbCentreon->prepare('INSERT INTO mod_chatops_config (config_key, config_value) VALUES (?, ?)');
        foreach ($this->config as $key => $value) {
            $res = $this->dbCentreon->execute($stmt, array($key, json_encode($value)));
            if (\PEAR::isError($res)) {
                throw new \Exception('Error during execute query');
            }
        }
    }

  /**
   * Build the command registry
   *
   * @return ChatOpsModule\CommandRegistry The command registry
   */
    public function getCommandRegistry()
    {
        if (!is_null($this->registry)) {
            return $this->registry;
        }
        $this->registry = new CommandRegistry();
        $this->scanCommand(__DIR__ . '/Command', 'ChatOpsModule\\Command\\');
      // @todo Scan modules directory

        return $this->registry;
    }

  /**
   * Validate a token
   *
   * @param string $token The token to validate
   * @param string $clientType The type of client
   * @return bool If the token is valid
   */
    public function validateToken($token, $clientType)
    {
        $stmt = $this->dbCentreon->prepare(
            'SELECT COUNT(*) as nb FROM mod_chatops_token WHERE active = 1 AND token = ? AND client = ?'
        );
        $res = $this->dbCentreon->execute($stmt, array($token, $clientType));
        if (\PEAR::isError($res)) {
            throw new \Exception('Error during execute query');
        }
        $row = $res->fetchRow();
        if ($row['nb'] != 1) {
            return false;
        }
        return true;
    }

  /**
   * Get the list of token
   *
   * @param bool $hide If hide the token
   * @return array The list of token
   */
    public function getListToken($hide = false)
    {
        $res = $this->dbCentreon->query('SELECT id, token, client, active, create_at FROM mod_chatops_token');
        if (\PEAR::isError($res)) {
            throw new \Exception('Error during execute query');
        }
        $result = array();
        while ($row = $res->fetchRow()) {
            if ($hide) {
                $start = substr($row['token'], 0, 3);
                $end = substr($row['token'], -3);
                $pad = str_pad('', strlen($row['token']) - 6, '*');
                $row['token'] = $start . $pad . $end;
            }
            $result[] = $row;
        }
        return $result;
    }

  /**
   * Add a token
   *
   * @param string $client The team chat type
   * @param string $token The token
   * @param bool $activate If the token is activated by default
   */
    public function addToken($client, $token, $activate = false)
    {
        $act = $activate ? 1 : 0;
        $stmt = $this->dbCentreon->prepare('INSERT INTO mod_chatops_token (client, token, active) VALUES (?, ?, ?)');
        $res = $this->dbCentreon->execute($stmt, array($client, $token, $act));
        if (\PEAR::isError($res)) {
            throw new \Exception('Error during execute query');
        }
    }

  /**
   * Set active or deactive a token
   *
   * @param int $tokenId The token id
   * @param bool $activate If the token is activate
   */
    public function setActivateToken($tokenId, $activate)
    {
        $act = $activate ? 1 : 0;
        $stmt = $this->dbCentreon->prepare('UPDATE mod_chatops_token SET active = ? WHERE id = ?');
        $res = $this->dbCentreon->execute($stmt, array($act, $tokenId));
        if (\PEAR::isError($res)) {
            throw new \Exception('Error during execute query');
        }
    }

  /**
   * Delete a token
   *
   * @param int $tokenId The token id to delete
   */
    public function deleteToken($tokenId)
    {
        $stmt = $this->dbCentreon->prepare('DELETE FROM mod_chatops_token WHERE id = ?');
        $res = $this->dbCentreon->execute($stmt, array($tokenId));
        if (\PEAR::isError($res)) {
            throw new \Exception('Error during execute query');
        }
    }

  /**
   * Return the database connection
   *
   * @param string $type The type of connection
   * @return \CentreonDB The database connection
   */
    public function getDb($type)
    {
        if ($type === 'storage') {
            return $this->dbCentreonStorage;
        }
        return $this->dbCentreon;
    }
}
