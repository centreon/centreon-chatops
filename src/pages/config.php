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

use \ChatOpsModule\Module;

if (!isset($centreon)) {
    die();
}

$chatOps = new Module(_CENTREON_PATH_, $dependencyInjector);

$errormsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['command_name']) || trim($_POST['command_name']) === '') {
        $errormsg = 'The command name is required.';
    } else {
        $chatOps->setConfig('command_name', trim($_POST['command_name']));
        $chatOps->saveConfig();
    }
}

$path = __DIR__;
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$tpl->assign('command_name', $chatOps->getConfig('command_name'));
$tpl->assign('errormsg', $errormsg);

$tpl->display('config.ihtml');
