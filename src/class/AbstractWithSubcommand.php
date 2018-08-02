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

/**
 * The abstract class for implement one command with subcommand
 *
 * @package ChatOpsModule
 * @version 1.0.0
 * @license Apache-2.0
 */
abstract class AbstractWithSubcommand extends AbstractCommand
{
  /**
   * @var array The list of subcommand - to set into child class
   */
  //static protected $subcommands = array();

  /**
   * Parsing the command and return argurments
   *
   * @param string $rawCommand The raw command to parse
   * @param string $author The author who ask the command
   */
    public function parseCommand($rawCommand, $author)
    {
      /* Build command format */
        $this->commandFormat = '(' . join('|', array_keys(static::$subcommands)) . ')(\s+(.*))?';
        parent::parseCommand($rawCommand, $author);
    }

  /**
   * Get short help for submodule
   *
   * @return string The help
   */
    public function getShortHelp()
    {
        $help = '';
        foreach (static::$subcommands as $commandName => $command) {
            $commandHelp = $command->getShortHelp();
            if (!is_null($commandHelp)) {
                $help .= $commandName . ' ' . $commandHelp . "\n";
            }
        }
        if ($help === '') {
            return null;
        }
        return trim($help);
    }

  /**
   * Abstract function for execute the command
   *
   * @param string $breadcrumb The following command breadcrumb
   * @return array|string The result of the command
   */
    public function run($breadcrumb = '')
    {
        if (isset($this->arguments[1]) && $this->isHelp(trim($this->arguments[1]))) {
            return array(
                'type' => 'string',
                'result' => $breadcrumb . ' ' . static::$subcommands[$this->arguments[0]]->getHelp($this->arguments[0])
            );
        }

        $cmdObj = static::$subcommands[$this->arguments[0]];
        $cmdObj->parseCommand(isset($this->arguments[1]) ? trim($this->arguments[1]) : '', $this->author);
        return $cmdObj->run($breadcrumb . ' ' . $this->arguments[0]);
    }

  /**
   * Register a subcommand
   *
   * @param string $name The name of subcommand
   * @param AbstractCommand $obj The command object
   * @throws Exception If the subcommand name already register
   */
    public static function registerSubcommand($name, $obj)
    {
        if (array_key_exists($name, static::$subcommands)) {
            throw new \Exception('The subcommand ' . $name . ' is already register.');
        }
        static::$subcommands[$name] = $obj;
    }
}
