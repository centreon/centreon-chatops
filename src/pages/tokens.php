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

$chatOps = new Module(_CENTREON_PATH_, $pearDB, $pearDBO);

$errormsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] && $_POST['action'] === 'add') {
        if ($_POST['token'] === '') {
            $errormsg = 'The token is empty.';
        } else {
            try {
                $chatOps->addToken($_POST['client'], $_POST['token'], true);
            } catch (\Exception $e) {
                $errormsg = 'Error during insert the new token';
            }
        }
    } elseif ($_POST['action'] && $_POST['action'] === 'deactivate') {
        try {
            $chatOps->setActivateToken($_POST['tokenid'], false);
        } catch (\Exception $e) {
            $errormsg = 'Error during insert the new token';
        }
    } elseif ($_POST['action'] && $_POST['action'] === 'activate') {
        try {
            $chatOps->setActivateToken($_POST['tokenid'], true);
        } catch (\Exception $e) {
            $errormsg = 'Error during insert the new token';
        }
    } elseif ($_POST['action'] && $_POST['action'] === 'delete') {
        try {
            $chatOps->deleteToken($_POST['tokenid']);
        } catch (\Exception $e) {
            $errormsg = 'Error during insert the new token';
        }
    }
}

$path = __DIR__;
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$tpl->assign('tokens', $chatOps->getListToken(true));
$tpl->assign('errormsg', $errormsg);

$tpl->display('tokens.ihtml');
