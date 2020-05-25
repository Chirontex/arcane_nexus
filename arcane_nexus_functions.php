<?php
/**
 *    Arcane Nexus, plugin for WordPress
 * 
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
function an_universal()
{

    if (isset($_POST['arcane_nexus_action'])) {

        global $model;

        switch ($_POST['arcane_nexus_action']) {
            case 'get_all':
                $get_nexus = $model->get_nexus();

                if ($get_nexus) $result = ['code' => '0', 'message' => 'Success.', 'data' => $get_nexus];
                else $result = ['code' => '1', 'message' => 'The answer is empty.'];
                break;

            case 'get':
                if (isset($_POST['arcane_nexus_id'])) {

                    $get_nexus = $model->get_nexus((int)$_POST['arcane_nexus_id']);

                    if ($get_nexus) $result = ['code' => '0', 'message' => 'Success.', 'data' => $get_nexus];
                    else $result = ['code' => '1', 'message' => 'The answer is empty.'];

                } else $result = ['code' => '-2', 'message' => 'Missing some required POST-arguments.'];
                break;

            case 'delete':
                if (isset($_POST['arcane_nexus_id'])) {

                    $get_nexus = $model->get_nexus((int)$_POST['arcane_nexus_id']);

                    $delete_nexus = $model->delete_nexus((int)$_POST['arcane_nexus_id']);

                    if ($get_nexus && $delete_nexus) {

                        unlink(plugin_dir_path(__FILE__).'code/'.$get_nexus[$_POST['arcane_nexus_id']]['file'].'.php');
                        
                        $result = ['code' => '0', 'message' => 'Success.', 'data' => true];
                    
                    } else $result = ['code' => '2', 'message' => 'Database query failure.'];

                } else $result = ['code' => '-2', 'message' => 'Missing some required POST-arguments.'];
                break;
            
            default:
                $result = ['code' => '-10', 'message' => 'Invalid value of \'arcane_nexus_action\'.'];
                break;
        }

    } else $result = ['code' => '-1', 'message'=> 'Missing base POST-argument \'arcane_nexus_action\'.'];

    return $result;

}

function an_nexus_execute($content)
{

    global $values;

    if (file_exists(plugin_dir_path(__FILE__).'code/'.$values['file'].'.php')) {

        ob_start();

        require_once 'code/'.$values['file'].'.php';

        $nexus_output = ob_get_clean();

        switch ($values['position']) {
            case 'before':
                $result = $nexus_output.$content;
                break;
    
            case 'replace':
                $result = $nexus_output;
                break;
    
            case 'after':
                $result = $content.$nexus_output;
                break;
                        
            default:
                $result = $content;
                break;
        }

    } else $result = $content;

    return $result;

}
