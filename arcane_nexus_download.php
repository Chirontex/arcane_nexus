<?php

$deny = true;

if (isset($_GET['hash']) && isset($_GET['filename']) && file_exists(__DIR__.'/key')) {

    $hash = urldecode($_GET['hash']);

    if (password_verify(file_get_contents(__DIR__.'/key'), $hash) && file_exists(__DIR__.'/code/'.$_GET['filename'].'.php')) $deny = false;

}

if ($deny) header('Location: '.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/');
else {

    header("Content-Type: application; charset=utf-8");
    header("Content-Disposition: attachment; filename=".$_GET['filename'].".php");

    echo file_get_contents('code/'.$_GET['filename'].'.php');

}
