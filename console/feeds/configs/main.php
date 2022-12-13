<?php

require_once 'dirs.php';

return [
    'template' => realpath(__DIR__.'/../templates/template.xml'),
    
    'templateYML' => realpath(__DIR__.'/../templates/template-yml.xml'),

    'feedsDir' => FEEDS_DIR,

    'facebookFeed' => FEEDS_DIR.DIRECTORY_SEPARATOR.'facebook.xml',

    'googleFeed' => FEEDS_DIR.DIRECTORY_SEPARATOR.'google.xml',
    
    'ymlFeed' => FEEDS_DIR.DIRECTORY_SEPARATOR.'ym.yml',

    'csvFeed' => FEEDS_DIR.DIRECTORY_SEPARATOR.'feed.csv',
    
    'csvGoogleFeed' => FEEDS_DIR.DIRECTORY_SEPARATOR.'google.csv',

    'iBlockId' => 5,

    'local' => require 'local/local_config.php',
];
