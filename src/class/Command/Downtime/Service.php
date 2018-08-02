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
namespace ChatOpsModule\Command\Downtime;

use \ChatOpsModule\AbstractCommand;
use \ChatOpsModule\Command\Downtime;

class Service extends AbstractCommand
{
    protected $commandFormat = '([\w_-]+)\s+([\w_-]+)\s+(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[^ ]*)\s+(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[^ ]*)\s+(fixed\s+|duration=\d+\s+){0,2}(.*)';
    protected $commands = array('service', 'svc');
    protected $subcommand = true;

    protected $help = '_hostname_ _service_ _datestart_ _dateend_ [fixed] [duration=300] _message_';
    protected $helpDescription = array(
        'hostname' => 'The hostname',
        'service' => 'The service description',
        'datestart' => 'The date start on ISO 8601 format',
        'dateend' => 'The date end on ISO 8601 format',
        'fixed' => 'If the downtime is fixed',
        'duration' => 'The time of downtime when it\'s flexible',
        'message' => 'The comment of the acknowledge'
    );

    public function __construct($module)
    {
        parent::__construct($module);
        Downtime::registerSubcommand('service', $this);
        Downtime::registerSubcommand('svc', $this);
    }

    public function run()
    {
        $comment = array_pop($this->arguments);
        $hostname = array_shift($this->arguments);
        $service = array_shift($this->arguments);
        $dateStart = array_shift($this->arguments);
        $dateEnd = array_shift($this->arguments);
        $fixed = false;
        $duration = 0;

        /* Alternative fix for multiple option */
        $arguments = $this->getArguments($hostname . ' ' . $service . ' ' . $dateStart . ' ' . $dateEnd, $comment);

        /* Find sticky on notify options */
        if (count($arguments) > 0) {
            foreach ($arguments as $arg) {
                if (trim($arg) === 'fixed') {
                    $fixed = true;
                } elseif (preg_match('/^duration=(\d+)$/', trim($arg), $matches)) {
                    $duration = $matches[1];
                }
            }
        }

        $extCmd = new \CentreonExternalCommand();
        $extCmd->setUserAlias($this->author);
        $extCmd->addServiceDowntime(
            $hostname,
            $service,
            $comment,
            $tsStart,
            $tsEnd,
            $fixed ? '1;' : '0;',
            $duration == 0 ? null : $duration,
            true
        );

        $tsStart = strtotime($dateStart);
        $tsEnd = strtotime($dateEnd);

        $extCmdLine = 'SCHEDULE_SVC_DOWNTIME;' . $hostname . ';' . $service . ';' . $tsStart . ';' . $tsEnd . ';';
        $extCmdLine .= $fixed ? '1;' : '0;';
        $extCmdLine .= '0;' . $duration . ';' . $this->author . ';' . $comment;

        return array(
            'type' => 'stringWithStatus',
            'result' => array(
                'status' => 'ok',
                'text' => $extCmdLine
            )
        );
    }
}
