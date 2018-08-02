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
namespace ChatOpsModule\Engine;

use \ChatOpsModule\AbstractEngine;

/**
 * The mattermost engine
 *
 * @package ChatOpsModule\Engine
 * @version 1.0.0
 * @license Apache-2.0
 */
class Mattermost extends AbstractEngine
{
    public function call($event, $context = null)
    {
        $result = $this->commandExec->run(
            trim(substr($event['command'] . ' ' . $event['text'], strlen('/' . $this->commandName))),
            $event['user_name']
        );

        $resultData = array();
        switch ($result['type']) {
            case 'string':
                $resultData = $this->renderText($result['result']);
                break;
            case 'stringWithStatus':
                $resultData = $this->renderTextWithStatus($result['result']);
                break;
            case 'listWithStatus':
                $resultData = $this->renderListWithStatus($result['result']);
                break;
        }

        return array_merge(
            array(
                'response_type' => 'in_channel'
            ),
            $resultData
        );
    }

    public function validateContent($event, $context = null)
    {
        if (!isset($_POST['user_name'])) {
            throw new \InvalidArgumentException('Missing user_name.');
        }
        if (!isset($_POST['command'])) {
            throw new \InvalidArgumentException('Missing command.');
        }
        if (!isset($_POST['text'])) {
            throw new \InvalidArgumentException('Missing text.');
        }
        if (!isset($_POST['token'])) {
            throw new \InvalidArgumentException('Missing token.');
        }
        return $event;
    }

    public function validateAuth($event, $context = null)
    {
        return $this->module->validateToken($event['token'], 'mattermost');
    }

    public function renderText($text)
    {
        return array(
            'text' => $text
        );
    }

    public function renderTextWithStatus($info)
    {
        return array(
            'attachments' => array(
                array(
                    'color' => $this->statusColors[$info['status']],
                    'text' => $info['text']
                )
            )
        );
    }

    public function renderListWithStatus($info)
    {
        $result = array();
        foreach ($info as $data) {
            $tmpResult = array(
            'color' => $this->statusColors[$data['status']]
            );
            if (isset($data['title'])) {
                  $tmpResult['title'] = $data['title'];
            }
            if (isset($data['text'])) {
                $tmpResult['text'] = $data['text'];
            }
            if (isset($data['extra'])) {
                $tmpResult['fields'] = $data['extra'];
            }
            if (isset($data['time'])) {
                $tmpResult['ts'] = $data['time'];
            }
            $result[] = $tmpResult;
        }

        return array(
            'attachments' => $result
        );
    }
}
