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
 * The command execution
 *
 * @package ChatOpsModule
 * @version 1.0.0
 * @license Apache-2.0
 */
class CommandExec
{
    protected $registry;
    protected $module;

  /**
   * Constructor
   *
   * @param CommandRegistry The registry of commands
   */
    public function __construct($registry, $module)
    {
        $this->registry = $registry;
        $this->module = $module;
    }

  /**
   * Execute the command
   */
    public function run($rawCommand, $author)
    {
        if (preg_match('/^help(\s+.*)?/', $rawCommand)) {
            return $this->registry->getHelp();
        }
        $command = preg_replace('/\s+/', ' ', $rawCommand, 1);
        list($subcommand, $other) = explode(' ', $command, 2);

        $commandClean = trim(substr($rawCommand, strlen($subcommand)));

        $cmdObj = $this->registry->getCommand($subcommand);
        if ($cmdObj->isHelp($commandClean)) {
            $help = $cmdObj->getHelp($subcommand);
            if (is_null($help)) {
                return array(
                    'type' => 'string',
                    'result' => 'No help defined.'
                );
            }
            return array(
                'type' => 'string',
                'result' => $this->module->getConfig('command_name') . ' ' . $help
            );
        }
        $cmdObj->parseCommand($commandClean, $author);

        return $cmdObj->run($this->module->getConfig('command_name') . ' ' . $subcommand);
    }
}
