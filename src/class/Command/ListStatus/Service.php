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
namespace ChatOpsModule\Command\ListStatus;

use \ChatOpsModule\AbstractCommand;
use \ChatOpsModule\Command\ListStatus;

class Service extends AbstractCommand
{
    protected $commandFormat = '^(limit=\d+\s*|host=[\w-]+\s*|service=[\w-]+\s*){0,3}(.*)$';
    protected $commands = array('service', 'svc');
    protected $subcommand = true;
    protected $help = '[limit=5] [host=host] [service=service]';
    protected $helpDescription = array(
      'limit' => 'The number of element returned (default: 5)',
      'host' => 'Filter by host',
      'service' => 'Filter by service'
    );

    protected $status = array(
      0 => 'ok',
      1 => 'warning',
      2 => 'critical',
      3 => 'unknown',
      4 => 'pending'
    );

    public function __construct($module)
    {
        parent::__construct($module);
        ListStatus::registerSubcommand('service', $this);
        ListStatus::registerSubcommand('svc', $this);
    }

    public function run()
    {
        $limit = 5;

        $comment = array_pop($this->arguments);

      /* Parse argument */
        $arguments = $this->getArguments('', $comment);
        $params = array();
        $query = 'SELECT h.name as hostname, s.description, s.state, s.output, s.last_check, s.last_hard_state_change
            FROM hosts h, services s
            WHERE s.enabled = 1 AND s.state IN (1, 2, 3) AND s.state_type = 1';
        foreach ($arguments as $argument) {
            if (preg_match('/^limit=(\d+)$/', $argument, $matches)) {
                $limit = $matches[1];
            } elseif (preg_match('/^host=([\w-]+)$/', $argument, $matches)) {
                $query .= ' AND h.name LIKE ?';
                $params[] = '%' . $matches[1] . '%';
            } elseif (preg_match('/^service=([\w-]+)$/', $argument, $matches)) {
                $query .= ' AND s.description LIKE ?';
                $params[] = '%' . $matches[1] . '%';
            }
        }

        $query .= ' ORDER BY s.last_check DESC
            LIMIT ' . $limit;

        $result = array();
        $stmt = $this->module->getDb('storage')->prepare($query);
        $res = $this->module->getDb('storage')->execute($stmt, $params);
        while ($row = $res->fetchRow()) {
            $dateNow = new \Datetime();
            $dateLastChange = new \Datetime();
            $dateLastChange->setTimestamp($row['last_hard_state_change']);
            $interval = $dateNow->diff($dateLastChange);
            $since = '';
            if ($interval->y > 0) {
                $since = $interval->format('%y years');
            } elseif ($interval->m > 0) {
                $since = $interval->format('%m months');
            } elseif ($interval->d > 0) {
                $since = $interval->format('%d days');
            } elseif ($interval->h > 0) {
                $since = $interval->format('%h hours');
            } elseif ($interval->i > 0) {
                $since = $interval->format('%i minutes');
            } elseif ($interval->s > 0) {
                $since = $interval->format('%s seconds');
            }
            $result[] = array(
                'status' => $this->status[$row['state']],
                'title' => $row['hostname'] . ' - ' . $row['description'],
                'extra' => array(
                    array(
                        'title' => 'Status',
                        'value' => strtoupper($this->status[$row['state']]),
                        'short' => true
                    ),
                    array(
                        'title' => 'Since',
                        'value' => $since,
                        'short' => true
                    ),
                    array(
                        'title' => 'Output',
                        'value' => $row['output']
                    )
                ),
                'time' => $row['last_check']
            );
        }

        return array(
            'type' => 'listWithStatus',
            'result' => $result
        );
    }
}
