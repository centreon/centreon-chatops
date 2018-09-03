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

require_once realpath(__DIR__ . '/../../../bootstrap.php');
require_once realpath(__DIR__ . '/../../../config/centreon.config.php');

/* Add a centreon autoloader */
spl_autoload_register(function ($class) {
    $endFile = array('.class.php', '.php');
    $basedir = _CENTREON_PATH_ . '/www/class';
    $baseFile = str_replace('/', '_', lcfirst($class));

    foreach ($endFile as $postfix) {
        if (file_exists($basedir . '/' . $baseFile . $postfix)) {
            require_once $basedir . '/' . $baseFile . $postfix;
        }
    }
});
