<?php

return [
    'maximaster' => [
        'value' => [
            'tools' => [
                'twig' => [
                    'debug' => true, //включает режим отладки (доступен dump)
                    'cache' => $_SERVER['DOCUMENT_ROOT'] . '/../var/runtime/cache/twig',
                ],
            ],
        ],
    ],
];
