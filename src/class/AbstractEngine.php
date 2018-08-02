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
 * The abstract class for implement one engine
 *
 * @package ChatOpsModule
 * @version 1.0.0
 * @license Apache-2.0
 */
abstract class AbstractEngine
{
  /**
   * @var ChatOpsModule\Module The module for chat ops
   */
    protected $module;
  /**
   * @var ChatOpsModule\CommandExec The command executor
   */
    protected $commandExec;
  /**
   * @var string The command name configured in chat
   */
    protected $commandName;
  /**
   * @var array The list of status color
   */
    protected $statusColors = array(
      'ok' => '#88b917',
      'warning' => '#ff9a13',
      'critical' => '#e00b3d',
      'unknown' => '#bcbdc0',
      'pending' => '#2ad1d4'
    );

  /**
   * Constructor
   *
   * @param ChatOpsModule\Module The module for chat ops
   * @param ChatOpsModule\CommandExec The command executor
   */
    public function __construct($module, $commandExec)
    {
        $this->module = $module;
        $this->commandExec = $commandExec;
        $this->commandName = $module->getConfig('command_name');
    }

  /**
   * Validate content receive from client
   *
   * @param mixed $event The data event
   * @param mixed $context The context of the call
   * @return array If the content is validated
   * @throws \InvalidArgumentException If the content is invalid
   */
    abstract public function validateContent($event, $content = null);

   /**
   * Validate authentication of the client
   *
   * @param mixed $event The data event
   * @param mixed $context The context of the call
   * @return bool If the authentication if valid
   */
    abstract public function validateAuth($event, $context = null);

  /**
   * The call function for execute action
   *
   * @param mixed $event The data event
   * @param mixed $context The context of the call
   */
    abstract public function call($event, $context = null);

  /**
   * Render a string for the return
   *
   * @param string $text The string to render
   */
    abstract protected function renderText($text);

  /**
   * Render a string with a status for the return
   *
   * @param array $info The string with a status to render
   */
    abstract protected function renderTextWithStatus($info);

  /**
   * Render a list with a status for the return
   *
   * @param array $info The list of information with a status to render
   */
    abstract protected function renderListWithStatus($info);
}
