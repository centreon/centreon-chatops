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

require_once realpath(__DIR__ . '/../autoload.php');
require_once realpath(__DIR__ . '/../initCentreon.php');

use \ChatOpsModule\Module;
use \ChatOpsModule\CommandExec;
use \ChatOpsModule\Engine\Mattermost;

header('Content-Type: application/json');

try {
    $chatops = new Module(_CENTREON_PATH_, $dependencyInjector);
    $registry = $chatops->getCommandRegistry();
    $commandExec = new CommandExec($registry, $chatops);
    $client = new Mattermost($chatops, $commandExec);
    $event = $client->validateContent($_POST);
    if (!$client->validateAuth($event)) {
        header('HTTP/1.1 403 Forbidden');
        echo json_encode(array('message' => 'Bad authentication token.'));
        return;
    }
    $result = $client->call($event);
    echo json_encode($result);
} catch (\BadFunctionCallException $e) {
    heaer('HTTP/1.1 404 Not found');
    echo json_encode(array('message' => 'Command not found.'));
} catch (\InvalidArgumentException $e) {
    header('HTTP/1.1 400 Bad request');
    echo json_encode(array('message' => $e->getMessage()));
} catch (\Exception $e) {
    error_log($e->getMessage());
    header('HTTP/1.1 500 Internal server error');
}
