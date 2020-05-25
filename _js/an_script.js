function an_file_upload_label_check()
{
    var file_upload_label = document.querySelector('#an_file_upload_label');
    var file_upload = document.querySelector('#an_file_upload').value;

    if (file_upload) {

        var filename = file_upload.split('\\');

        file_upload_label.innerHTML = filename[filename.length - 1];
    } else file_upload_label.innerHTML = 'Укажите файл с PHP-кодом на вашем компьютере';
}

function an_submit()
{
    var nexus_id = document.querySelector('#an_id_select').value;

    if (nexus_id == 0) an_insert();
    else an_update();
}

function an_insert()
{
    var form = document.querySelector('#an_form');
    var status_block = document.querySelector('#an_status_block');
    var uri = document.querySelector('#an_uri_input').value;
    var file_upload = document.querySelector('#an_file_upload').value;

    status_block.innerHTML = '';

    var status = '';

    if (!uri) status = 'Укажите URI страницы, к которой подключается скрипт.<br>';

    if (!file_upload) status = status+'Укажите файл с PHP-кодом.<br>';

    if (status) status_block.innerHTML = '<p class="an_text_red">'+status+'</p>';
    else form.submit();
}

function an_update()
{
    var form = document.querySelector('#an_form');
    var status_block = document.querySelector('#an_status_block');
    var uri = document.querySelector('#an_uri_input').value;

    status_block.innerHTML = '';

    var status = '';

    if (!uri) status = 'Укажите URI страницы, к которой подключается скрипт.<br>';

    if (status) status_block.innerHTML = '<p class="an_text_red">'+status+'</p>';
    else form.submit();
}

function an_view_update()
{
    var form = document.querySelector('#an_form');
    var id = document.querySelector('#an_id_select').value;
    var uri = document.querySelector('#an_uri_input');
    var position = document.querySelector('#an_position_select');
    var file_upload_par = document.querySelector('#an_file_upload_par');
    var hash = document.querySelector('#an_hash_input').value;
    var status_block = document.querySelector('#an_status_block');
    var id_select_par = document.querySelector('#an_id_select_par');
    var space = document.querySelector('#an_id_select_space');

    if (id == 0) {
        an_uri_input_clean();
        uri = document.querySelector('#an_uri_input');
        an_file_upload_clean();
        an_file_upload_label_check();

        if (file_upload_par.getAttribute('hidden')) file_upload_par.removeAttribute('hidden');

        if (document.querySelector('#an_file_download_delete_par')) document.querySelector('#an_file_download_delete_par').parentNode.removeChild(document.querySelector('#an_file_download_delete_par'));

        if (document.querySelector('#an_nexus_delete_a')) document.querySelector('#an_nexus_delete_a').parentNode.removeChild(document.querySelector('#an_nexus_delete_a'));

    } else {
        an_disable_form();

        var request = $.ajax({
            method: "POST",
            url: site_address+"/wp-json/arcane_nexus/request",
            data: {
                arcane_nexus_hash: hash,
                arcane_nexus_action: "get",
                arcane_nexus_id: id
            },
            dataType: "json"
        });

        request.done(function(answer) {
            an_enable_form();

            if (answer['code'] == 0) {
                an_uri_input_clean();
                uri = document.querySelector('#an_uri_input');
                uri.setAttribute('value', answer['data'][id]['uri']);
                position.value = answer['data'][id]['position'];

                file_upload_par.setAttribute('hidden', true);
                an_file_upload_clean();
                an_file_upload_label_check();

                var new_buttons = document.createElement('p');

                new_buttons.setAttribute('id', 'an_file_download_delete_par');
                form.appendChild(new_buttons);
                form.insertBefore(new_buttons, file_upload_par);

                var download_and_delete = document.querySelector('#an_file_download_delete_par');

                download_and_delete.innerHTML = '<a href="'+site_address+'/wp-content/plugins/'+an_dir+'/arcane_nexus_download.php?filename='+answer['data'][id]['file']+'&hash='+hash_urlencoded+'">Скачать загруженный файл с PHP-кодом</a><br>—<br><a href="#" onclick="an_remove_file()">Удалить и загрузить новый</a>';

                var delete_nexus_button = document.createElement('a');
                delete_nexus_button.setAttribute('href', '#');
                delete_nexus_button.setAttribute('id', 'an_nexus_delete_a');
                delete_nexus_button.setAttribute('onclick', 'an_nexus_delete()');

                id_select_par.appendChild(delete_nexus_button);
                document.querySelector('#an_nexus_delete_a').innerHTML = 'Удалить';
                id_select_par.insertBefore(document.querySelector('#an_nexus_delete_a'), space.nextSibling);

            } else status_block.innerHTML = '<p class="an_text_red">'+answer['code']+': '+answer['message']+'</p>';
        });

        request.fail(function(jqXHR, textStatus) {
            an_enable_form();
            status_block.innerHTML = '<p class="an_text_red">'+textStatus+'</p>';
        });

    }
}

function an_remove_file()
{
    var download_and_delete = document.querySelector('#an_file_download_delete_par');
    var file_upload_par = document.querySelector('#an_file_upload_par');

    if (file_upload_par.getAttribute('hidden')) file_upload_par.removeAttribute('hidden');

    download_and_delete.innerHTML = '<p class="an_text_italic">Старый файл скрипта будет удалён при загрузке нового.</p>';
}

function an_disable_form()
{
    document.querySelector('#an_id_select').setAttribute('disabled', true);
    document.querySelector('#an_uri_input').setAttribute('disabled', true);
    document.querySelector('#an_position_select').setAttribute('disabled', true);
    document.querySelector('#an_position_select').setAttribute('disabled', true);
    document.querySelector('#an_file_upload').setAttribute('disabled', true);
    document.querySelector('#an_submit_button').setAttribute('disabled', true);

    if (document.querySelector('#an_file_download_delete_par')) document.querySelector('#an_file_download_delete_par').setAttribute('hidden', true);

    if (!(document.querySelector('#an_file_upload_par').getAttribute('hidden'))) document.querySelector('#an_file_upload_par').setAttribute('hidden', true);

    if (document.querySelector('#an_nexus_delete_a')) document.querySelector('#an_nexus_delete_a').setAttribute('hidden', true);
}

function an_enable_form()
{
    document.querySelector('#an_id_select').removeAttribute('disabled');
    document.querySelector('#an_uri_input').removeAttribute('disabled');
    document.querySelector('#an_position_select').removeAttribute('disabled');
    document.querySelector('#an_position_select').removeAttribute('disabled');
    document.querySelector('#an_file_upload').removeAttribute('disabled');
    document.querySelector('#an_submit_button').removeAttribute('disabled');

    if (document.querySelector('#an_file_download_delete_par')) {    
        document.querySelector('#an_file_download_delete_par').removeAttribute('hidden');
        document.querySelector('#an_nexus_delete_a').removeAttribute('hidden');

        if (document.querySelector('#an_file_download_delete_par').innerHTML == '<p class="an_text_italic">Старый файл скрипта будет удалён при загрузке нового.</p>') document.querySelector('#an_file_upload_par').removeAttribute('hidden');
    }

}

function an_file_upload_clean()
{
    var file_upload_input = document.querySelector('#an_file_upload');
    var file_upload_par = document.querySelector('#an_file_upload_par');

    file_upload_input.parentNode.removeChild(file_upload_input);

    file_upload_input = document.createElement('input');
    file_upload_input.setAttribute('type', 'file');
    file_upload_input.setAttribute('accept', '.php');
    file_upload_input.setAttribute('id', 'an_file_upload');
    file_upload_input.setAttribute('name', 'arcane_nexus_file');
    file_upload_input.setAttribute('onchange', 'an_file_upload_label_check()');
    file_upload_input.setAttribute('hidden', true);

    file_upload_par.appendChild(file_upload_input);
}

function an_nexus_delete()
{
    var id_select = document.querySelector('#an_id_select');
    var id = id_select.value;
    var hash = document.querySelector('#an_hash_input').value;
    var status_block = document.querySelector('#an_status_block');

    an_disable_form();
    
    var request = $.ajax({
        method: "POST",
        url: site_address+"/wp-json/arcane_nexus/request",
        data: {
            arcane_nexus_hash: hash,
            arcane_nexus_action: "delete",
            arcane_nexus_id: id
        },
        dataType: "json"
    });

    request.done(function(answer) {

        if (answer['code'] == 0) {

            var request_get_all = $.ajax({
                method: "POST",
                url: site_address+"/wp-json/arcane_nexus/request",
                data: {
                    arcane_nexus_hash: hash,
                    arcane_nexus_action: "get_all"
                },
                dataType: "json"
            });

            request_get_all.done(function(answer_get_all) {
                an_enable_form();

                if (answer_get_all['code'] == 0) {
                    var options = '<option value="0" onclick="an_view_update()">Новый</option>';

                    var ids = Object.keys(answer_get_all['data']);

                    for (let i = 0; i < ids.length; i++) {

                        options = options+'<option value="'+ids[i]+'" onclick="an_view_update()">'+ids[i]+'</option>';
                    }

                    id_select.innerHTML = options;
                    id_select.options[0].click();
                    status_block.innerHTML = '<p class="an_text_green">Прикрепление скрипта к странице успешно удалено!</p>';
                } else if (answer_get_all['code'] == 1) {
                    id_select.innerHTML = '<option value="0" onclick="an_view_update()">Новый</option>';
                    id_select.options[0].click();
                    status_block.innerHTML = '<p class="an_text_green">Прикрепление скрипта к странице успешно удалено!</p>';

                } else status_block.innerHTML = '<p class="an_text_red">'+answer_get_all['code']+': '+answer_get_all['message']+'</p>';
            });

            request_get_all.fail(function(jqXHR, textStatus) {
                an_enable_form();
                document.querySelector('#an_status_block').innerHTML = '<p class="an_text_red">'+textStatus+'</p>';
            });
        } else {
            an_enable_form();
            status_block.innerHTML = '<p class="an_text_red">'+answer['code']+': '+answer['message']+'</p>';
        }
    });

    request.fail(function(jqXHR, textStatus) {
        an_enable_form();
        status_block.innerHTML = '<p class="an_text_red">'+textStatus+'</p>';
    });
}

function an_uri_input_clean()
{
    var uri_input = document.querySelector('#an_uri_input');
    var uri_input_par = document.querySelector('#an_uri_input_par');

    uri_input.parentNode.removeChild(uri_input);

    uri_input = document.createElement('input');
    uri_input.setAttribute('type', 'text');
    uri_input.setAttribute('id', 'an_uri_input');
    uri_input.setAttribute('name', 'arcane_nexus_uri');
    uri_input.setAttribute('size', '40');

    uri_input_par.appendChild(uri_input);
}