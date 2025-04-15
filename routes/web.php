<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $res = \Illuminate\Support\Facades\Process::run("sudo -n /bin/mkdir -p /etc/supervisor/conf.d/minecraft");

    if($res->successful()) {
        return $res->output();
    } else {
        return $res->errorOutput();
    }

    return view('welcome');
});
