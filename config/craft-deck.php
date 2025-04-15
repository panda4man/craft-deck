<?php

return [
    'user' => 'minecraft',
    'worlds_path' => '/Users/aclinton/Desktop/minecraft',
    'supervisor_config' => '/etc/supervisor/conf.d/minecraft',
    'commands' => [
        'mv' => '/bin/mv',
        'mkdir' => '/bin/mkdir',
    ],
    'jars' => [
        'disk' => 'local',
        'path' => 'jars'
    ]
];
