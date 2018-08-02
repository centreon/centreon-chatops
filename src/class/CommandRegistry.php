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
 * The command registry
 *
 * @package ChatOpsModule
 * @version 1.0.0
 * @license Apache-2.0
 */
class CommandRegistry
{
  /**
   * @var AbstractCommand[] The list of command
   */
    protected $commands = array();

  /**
   * Register a subcommand object
   *
   * @param AbstractCommand $objectCommand The object command to register
   */
    public function register($objectCommand)
    {
        if ($objectCommand->isSubcommand()) {
            return;
        }
        $commands = $objectCommand->getCommandList();

        if (is_array($commands)) {
            foreach ($commands as $command) {
                $this->registerCommand($command, $objectCommand);
            }
        } else {
            $this->registerCommand($commands, $objectCommand);
        }
    }

  /**
   * Get the command object
   *
   * @param string $name The command name
   * @return AbstractCommand The object command
   */
    public function getCommand($name)
    {
        if (!array_key_exists($name, $this->commands)) {
            throw new \Exception('The command ' . $name . ' is not registred.');
        }

        return $this->commands[$name];
    }

  /**
   * Return the help of commands
   */
    public function getHelp()
    {
        $help = '';
        foreach ($this->commands as $commandname => $command) {
            if (!$command->isSubcommand()) {
                $commandHelp = $command->getShortHelp();
                if (!is_null($commandHelp)) {
                    $help .= preg_replace('/^/m', $commandname . ' ', $commandHelp) . "\n";
                }
            }
        }
        return array(
            'type' => 'string',
            'result' => trim(preg_replace('/^/m', 'centreon ', $help))
        );
    }

  /**
   * Register the subcommand
   *
   * @param string $name The subcommand name
   * @param AbstractCommand $objectCommand The object command to register
   * @throws Exception If the name is malformated or if the subcommand already
   *                   exists
   */
    protected function registerCommand($name, $object)
    {
        if (!preg_match('/[\w_]+/', $name)) {
            throw new \Exception('Malformated command name format for command ' . $name . '.');
        }

        if (array_key_exists($name, $this->commands)) {
            throw new \Exception('The command ' . $name . ' already registred.');
        }
        $this->commands[$name] = $object;
    }
}
