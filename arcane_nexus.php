<?php
/**
 * Plugin Name: Arcane Nexus
 * Plugin URI: https://github.com/dmitryshumilin/arcane_nexus
 * Description: Плагин, расширяющий возможности PHP-разработчиков в работе с WordPress.
 * Version: 1.2.2
 * Author: Дмитрий Шумилин
 * Author URI: mailto://dmitri.shumilinn@yandex.ru
 */
/**
 *    Copyright (C) 2020  Dmitry Shumilin (dmitri.shumilinn@yandex.ru)
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
require_once 'ANModel.php';
require_once 'arcane_nexus_functions.php';

define('ARCANE_NEXUS_DATABASE', null);
define('ARCANE_NEXUS_DIRECTORY', substr(plugin_dir_path(__FILE__), strpos(plugin_dir_path(__FILE__), 'plugins') + 8, -1));
define('ARCANE_NEXUS_HTACCESS_PATH', substr(plugin_dir_path(__FILE__), 0, strpos(plugin_dir_path(__FILE__), 'wp-content')));

$anmodel = new ANModel(ARCANE_NEXUS_DATABASE);

if (!file_exists(plugin_dir_path(__FILE__).'_css/bootstrap.min.css')) file_put_contents(plugin_dir_path(__FILE__).'_css/bootstrap.min.css', file_get_contents('https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css'));

if (!file_exists(plugin_dir_path(__FILE__).'_js/jquery-3.5.1.js')) file_put_contents(plugin_dir_path(__FILE__).'_js/jquery-3.5.1.js', file_get_contents('https://code.jquery.com/jquery-3.5.1.js'));

add_action('admin_menu', function() {

    add_menu_page('Arcane Nexus', 'Arcane Nexus', 8, plugin_dir_path(__FILE__).'arcane_nexus_admin.php');

});

if (!file_exists(plugin_dir_path(__FILE__).'key')) {

    if ($anmodel->installation()) {

        file_put_contents(plugin_dir_path(__FILE__).'key', $anmodel->generate_random_string());

        if (!file_exists(plugin_dir_path(__FILE__).'code')) mkdir(plugin_dir_path(__FILE__).'code');

    }

}

$generate_htaccess = $anmodel->generate_htaccess(ARCANE_NEXUS_DIRECTORY);

if (file_exists(ARCANE_NEXUS_HTACCESS_PATH.'.htaccess')) {

    $exist_htaccess = file_get_contents(ARCANE_NEXUS_HTACCESS_PATH.'.htaccess');

    if (!strpos($exist_htaccess, $generate_htaccess)) file_put_contents(ARCANE_NEXUS_HTACCESS_PATH.'.htaccess', $exist_htaccess."\n".$generate_htaccess);

} else file_put_contents(ARCANE_NEXUS_HTACCESS_PATH.'.htaccess', $generate_htaccess);

define('ARCANE_NEXUS_PASSWORD', file_get_contents(plugin_dir_path(__FILE__).'key'));

add_action('rest_api_init', function() {

    register_rest_route('arcane_nexus', '/request', [
        'methods' => 'POST',
        'callback' => 'an_universal',
        'permission_callback' => function() {

            $result = password_verify(ARCANE_NEXUS_PASSWORD, $_POST['arcane_nexus_hash']);

            return $result;

        }
    ]);

});

$nexus_array = $anmodel->get_nexus();

if (!empty($nexus_array)) {

    $nexus_execute_on_this_page = false;

    foreach ($nexus_array as $id => $values) {
        
        $trimmed_uri = trim($values['uri'], '/');

        if ($_SERVER['REQUEST_URI'] == '/') $server_request = 'index';
        else $server_request = trim($_SERVER['REQUEST_URI'], '/');

        if ($trimmed_uri !== 'arcane_nexus_admin' && $trimmed_uri === $server_request) {

            add_filter('the_content', 'an_nexus_execute', 0);

            $nexus_execute_on_this_page = true;
            break;

        }

        if ($values['uri'] === '*') $nexus_common = $id;

    }

    if (!$nexus_execute_on_this_page && isset($nexus_common)) {

        $values = [
            'uri' => $nexus_array[$nexus_common]['uri'],
            'position' => $nexus_array[$nexus_common]['position'],
            'file' => $nexus_array[$nexus_common]['file']
        ];

        add_filter('the_content', 'an_nexus_execute', 0);

        $nexus_execute_on_this_page = true;

    }

}
