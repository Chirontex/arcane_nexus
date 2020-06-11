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

$arcane_nexus_hash = password_hash(ARCANE_NEXUS_PASSWORD, PASSWORD_DEFAULT);
?>
<link type="text/css" rel="stylesheet" href="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/wp-content/plugins/<?= ARCANE_NEXUS_DIRECTORY ?>/_css/an_styles.css">
<?php

if (file_exists(plugin_dir_path(__FILE__).'_css/bootstrap.min.css')) echo '<link type="text/css"  rel="stylesheet" href="'.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/wp-content/plugins/'.ARCANE_NEXUS_DIRECTORY.'/_css/bootstrap.min.css">'."\n";
else echo '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">'."\n";
?>
<script src="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/wp-content/plugins/<?= ARCANE_NEXUS_DIRECTORY ?>/_js/an_script.js"></script>
<?php

if (file_exists(plugin_dir_path(__FILE__).'_js/jquery-3.5.1.js')) echo '<script src="'.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/wp-content/plugins/'.ARCANE_NEXUS_DIRECTORY.'/_js/jquery-3.5.1.js"></script>'."\n";
else echo '<script src="https://code.jquery.com/jquery-3.5.1.js"></script>'."\n";
?>
<script>
var site_address = '<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>';
var an_dir = '<?= ARCANE_NEXUS_DIRECTORY ?>';
var hash_urlencoded = '<?= urlencode($arcane_nexus_hash) ?>';
</script>
<?php

$arcane_nexus_status = '';

if (password_verify(ARCANE_NEXUS_PASSWORD, $_POST['arcane_nexus_hash']) && isset($_POST['arcane_nexus_id']) && isset($_POST['arcane_nexus_uri']) && isset($_POST['arcane_nexus_position'])) {

    if ((int)$_POST['arcane_nexus_id'] === 0) {

        if (!empty($_FILES['arcane_nexus_file']['name'])) {

            do {

                $filename_length = 10;

                $arcane_nexus_filename = $anmodel->generate_random_string($filename_length);

                $filename_length += 1;

            } while (file_exists(plugin_dir_path(__FILE__).'code/'.$arcane_nexus_filename.'.php'));

            if (move_uploaded_file($_FILES['arcane_nexus_file']['tmp_name'], plugin_dir_path(__FILE__).'code/'.$arcane_nexus_filename.'.php')) {

                if ($anmodel->insert_nexus($_POST['arcane_nexus_uri'], $arcane_nexus_filename, $_POST['arcane_nexus_position'])) $arcane_nexus_status = '<p class="an_text_green">Скрипт успешно добавлен к странице!</p>';
                else $arcane_nexus_status = '<p class="an_text_red">Не удалось добавить скрипт к странице! Скорее всего, к странице с указанным URI уже добавлен другой скрипт.</p>';

            } else $arcane_nexus_status = '<p class="an_text_red">Не удалось загрузить файл скрипта!</p>';

        } else $arcane_nexus_status = '<p class="an_text_red">Не указан файл с кодом!</p>';

    } else {

        if (!empty($_FILES['arcane_nexus_file']['name'])) {

            do {

                $filename_length = 10;

                $arcane_nexus_filename = $anmodel->generate_random_string($filename_length);

                $filename_length += 1;

            } while (file_exists(plugin_dir_path(__FILE__).'code/'.$arcane_nexus_filename.'.php'));

            if (move_uploaded_file($_FILES['arcane_nexus_file']['tmp_name'], plugin_dir_path(__FILE__).'code/'.$arcane_nexus_filename.'.php')) {

                if ($anmodel->update_nexus((int)$_POST['arcane_nexus_id'], $_POST['arcane_nexus_uri'], $arcane_nexus_filename, $_POST['arcane_nexus_position'])) {

                    unlink(plugin_dir_path(__FILE__).'code/'.$nexus_array[$_POST['arcane_nexus_id']]['file'].'.php');
                    
                    $arcane_nexus_status = '<p class="an_text_green">Новый скрипт успешно добавлен к странице!</p>';
                
                } else $arcane_nexus_status = '<p class="an_text_red">Не удалось добавить новый скрипт к странице! Если вместе с загрузкой нового скрипта был изменён URI страницы, к которой он прикрепляется, то убедитесь в том, что к странице с данным URI не прикреплены другие скрипты.</p>';

            } else $arcane_nexus_status = '<p class="an_text_red">Не удалось загрузить новый скрипт!</p>';

        } else {

            if ($anmodel->update_nexus((int)$_POST['arcane_nexus_id'], $_POST['arcane_nexus_uri'], $nexus_array[$_POST['arcane_nexus_id']]['file'], $_POST['arcane_nexus_position'])) $arcane_nexus_status = '<p class="an_text_green">Настройки добавления скрипта к странице успешно изменены!</p>';
            else $arcane_nexus_status = '<p class="an_text_red">Не удалось изменить настройки добавления скрипта к странице! Меняя URI, пожалуйста, убедитесь, что страница с данным URI не имеет других прикреплённых скриптов.</p>';

        }

    }

}

$nexus_array = $anmodel->get_nexus();

?>
<div class="container-fluid">
    <h1 style="text-align: center;">Arcane Nexus</h1><br>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6" style="border-left: 1px dotted black;">
            <h4 style="text-align: center;">Настройка подключения скриптов к страницам</h4><br>
            <div class="row">
                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                    <form action="" method="POST" enctype="multipart/form-data" id="an_form">
                        <p id="an_id_select_par"><label for="an_id_select"><span class="an_text_bolder">ID подключения скрипта:</span></label><br>
                        <select id="an_id_select" name="arcane_nexus_id">
                            <option value="0" onclick="an_view_update()">Новый</option>
<?php

foreach (array_keys($nexus_array) as $id) {
    
    echo '<option value="'.$id.'" onclick="an_view_update()">'.$id.'</option>'."\n";

}

?>
                        </select><span id="an_id_select_space"> </span></p>
                        <p id="an_uri_input_par"><label for="an_uri_input"><span class="an_text_bolder">URI страницы, как в $_SERVER['REQUEST_URI']:</span></label><br>
                        <input type="text" id="an_uri_input" name="arcane_nexus_uri" size="40"></p>
                        <p><label for="an_position_select"><span class="an_text_bolder">Положение HTML-потока скрипта относительно контента страницы:</span></label><br>
                        <select id="an_position_select" name="arcane_nexus_position">
                            <option value="before">Перед</option>
                            <option value="after">После</option>
                            <option value="replace">Заменить</option>
                            <option value="mark">По метке</option>
                            <option value="off">Отключено</option>
                        </select></p>
                        <p id="an_file_upload_par"><label for="an_file_upload"><a href="#" id="an_file_upload_label" onclick="document.querySelector('#an_file_upload').click();">Укажите файл с PHP-кодом на вашем компьютере</a></label><br>
                        <input type="file" accept=".php" id="an_file_upload" name="arcane_nexus_file" onchange="an_file_upload_label_check()" hidden></p>
<?= '<input type="hidden" id="an_hash_input" name="arcane_nexus_hash" value="'.$arcane_nexus_hash.'">'."\n" ?>
                        <input type="button" class="btn btn-md btn-primary" id="an_submit_button" onclick="an_submit()" value="Сохранить">
                    </form><br>
                    <div id="an_status_block"><?= $arcane_nexus_status ?></div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                    <p>Это — админпанель для управления плагином <span class="an_text_bolder">Arcane Nexus</span>. Здесь вы можете настраивать подключение PHP-скриптов к страницам вашего сайта. Это очень просто, но следует помнить о том, что <span class="an_text_bolder">к каждой странице можно подключить только один скрипт</span>.</p>
                    <p>Плагин различает страницы по <span class="an_text_bolder">request URI</span>. Таким образом, одна и та же страница с разными GET-запросами считается плагином <span class="an_text_bolder">разными страницами</span>. Однако, есть несколько зарезервированных URI:</p>
                    <ol>
                        <li><p><span class="an_text_italic">arcane_nexus_admin</span> — админпанель плагина Arcane Nexus;</p></li>
                        <li><p><span class="an_text_italic">index</span> — главная страница сайта;</p></li>
                        <li><p><span class="an_text_italic">*</span> — все страницы сайта.</p></li>
                    </ol>
                    <p>Если к странице подключается какой-то определённый скрипт, то тот скрипт, который предназначен для всех страниц сайта (т.е. подключаемый к URI *), на ней выполняться <span class="an_text_bolder">не будет</span>.</p>
                    <p>Подключаемый скрипт выполняется в буфере, и на страницу, к которой он подключается, фактически попадает только результирующий HTML-поток, образуемый во время выполнения. Вывод HTML-потока относительно основного контента страницы должен подчиняться какой-то логике, и здесь есть четыре варианта: <span class="an_text_bolder">перед</span>, <span class="an_text_bolder">после</span>, <span class="an_text_bolder">замена</span> и <span class="an_text_bolder">вставка по метке</span> (в качестве метки используется блок <?= htmlspecialchars('<div id="mark_for_arcane_nexus"></div>') ?>). Также, через настройку положения результирующего HTML-потока можно <span class="an_text_bolder">отключить</span> исполнение скрипта на странице.</p>
                    <p>Удачи! :)</p>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6" style="border-left: 1px dotted black;">
<?php

$an_admin_script_loaded = false;

if (!empty($nexus_array)) {

    foreach ($nexus_array as $id => $values) {
        
        $trimmed_uri = trim($values['uri'], '/');

        if ($trimmed_uri === 'arcane_nexus_admin') {

            require_once plugin_dir_path(__FILE__).'code/'.$values['file'].'.php';

            $an_admin_script_loaded = true;

            break;

        }

    }

}

if (!$an_admin_script_loaded) echo '<br><br><div class="container"><p class="an_text_italic" style="text-align: center;">... Это — место для предполагаемых дополнений к плагину, которые можно подключать, указывая в качестве URI \'arcane_nexus_admin\'.</p></div>';

?>
        </div>
    </div>
</div>
<script>an_file_upload_label_check();</script>