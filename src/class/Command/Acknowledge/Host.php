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
namespace ChatOpsModule\Command\Acknowledge;

use \ChatOpsModule\AbstractCommand;
use \ChatOpsModule\Command\Acknowledge;

class Host extends AbstractCommand
{
    protected $commandFormat = '([\w_-]+)\s+(sticky\s+|notify\s+){0,2}(.*)';
    protected $commands = 'host';
    protected $subcommand = true;

    protected $help = '_hostname_ [sticky] [notify] _message_';
    protected $helpDescription = array(
        'hostname' => 'The hostname',
        'sticky' => 'The acknowledgement will remain until the service returns to an OK state',
        'notify' => 'A notification will be sent out to contacts indicating that the current service problem has been acknowledged',
        'message' => 'The comment of the acknowledge'
    );

    public function __construct($module)
    {
        parent::__construct($module);
        Acknowledge::registerSubcommand('host', $this);
    }

    public function run()
    {
        $comment = array_pop($this->arguments);
        $hostname = array_shift($this->arguments);
        $sticky = false;
        $notify = false;

      /* Alternative fix for multiple option */
        $arguments = $this->getArguments($hostname, $comment);

      /* Find sticky on notify options */
        if (count($arguments) > 0) {
            foreach ($arguments as $arg) {
                if (trim($arg) === 'sticky') {
                    $sticky = true;
                } elseif (trim($arg) === 'notify') {
                    $notify = true;
                }
            }
        }

        $extCmd = new \CentreonExternalCommand();
        $extCmd->acknowledgeHost(
            $hostname,
            $sticky ? 1 : 0,
            $notify ? 1 : 0,
            1,
            $this->author,
            $comment
        );

        $extCmdLine = 'ACKNOWLEDGE_HOST_PROBLEM;' . $hostname . ';';
        $extCmdLine .= $sticky ? '1;' : '0;';
        $extCmdLine .= $notify ? '1;' : '0;';
        $extCmdLine .= '1;' . $this->author . ';' . $comment;

        return array(
            'type' => 'stringWithStatus',
            'result' => array(
                'status' => 'ok',
                'text' => $extCmdLine
            )
        );
    }
}
