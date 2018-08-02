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
 * The abstract class for implement one command
 *
 * @package ChatOpsModule
 * @version 1.0.0
 * @license Apache-2.0
 */
abstract class AbstractCommand
{
  /**
   * @var bool If the command is a subcommand
   */
    protected $subcommand = false;
  /**
   * @var string The raw command
   */
    protected $rawCommand = '';
  /**
   * @var string The author who run the command
   */
    protected $author = '';
  /**
   * @var ChatOpsModule\Module
   */
    protected $module;

  /**
   * The module class for configuration and db object
   *
   * @param ChatOpsModule\Module $module The module classe
   */
    public function __construct($module)
    {
        $this->module = $module;
    }

  /**
   * Parsing the command and return argurments
   *
   * @param string $rawCommand The raw command to parse
   * @param string $author The author who ask the command
   * @return bool To execute
   * @throws Exception If the command format is not defined or not match
   */
    public function parseCommand($rawCommand, $author)
    {
        $this->author = $author;
        $this->rawCommand = $rawCommand;
        if (!isset($this->commandFormat)) {
            throw new \BadFunctionCallException('The command format is not defined.');
        }

        if (!preg_match('/^' . $this->commandFormat . '$/', $this->rawCommand, $matches)) {
            throw new \InvalidArgumentException('The command does not match.');
        }

      /* Remove the first entry : global match */
        array_shift($matches);

        $this->arguments = $matches;
    }

  /**
   * Return the command list
   *
   * @return array The command list
   * @throws \Exception If the command list
   */
    public function getCommandList()
    {
        if (!isset($this->commands)) {
            throw new \Exception('The command list is not set.');
        }
        return $this->commands;
    }

  /**
   * Is a subcommand
   *
   * @return bool If the class is a subcommand
   */
    public function isSubcommand()
    {
        return $this->subcommand;
    }

  /**
   * Return the help command
   *
   * @return string|array The help information
   */
    public function getShortHelp()
    {
        if (!isset($this->help)) {
            return null;
        }
        return $this->help;
    }

  /**
   * Return the full description of the command
   */
    public function getHelp($commandBase)
    {
        if (!isset($this->help) || !isset($this->helpDescription)) {
            return null;
        }
        $help = $commandBase . ' ' . $this->help . "\n";
        foreach ($this->helpDescription as $name => $desc) {
            $help .= "\t**" . $name . "**\t" . $desc . "\n";
        }
        return $help;
    }

  /**
   * Test if ask for the help of the command
   *
   * @param string $rawCommand The raw command
   * @return bool If the command is help
   */
    public function isHelp($rawCommand)
    {
        if (preg_match('/^help(\s+.*$)?/', $rawCommand)) {
            return true;
        }
        return false;
    }

  /**
   * Return the arguments after parsing the known ones
   *
   * @param string $start The starting known arguments
   * @param string $end The ending known arguments
   * @return array The listing of arguments
   */
    protected function getArguments($start = '', $end = '')
    {
        $command = $this->rawCommand;
        if ($start !== '') {
            $command = preg_replace('/^' . $start . ' /', '', $command);
        }
        if ($end !== '') {
            $command = preg_replace('/ ' . $end . '$/', '', $command);
        }
        return explode(' ', preg_replace('/\s+/', ' ', $command));
    }

  /**
   * Abstract function for execute the command
   *
   * Return format
   *
   * $return['type'] => The type of return
   * $return['result'] => The list of result
   *
   * @param string $breadcrumb The following command breadcrumb
   * @return array The result of the command
   */
    abstract public function run();
}
