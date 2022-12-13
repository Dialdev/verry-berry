<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

echo \Maximaster\Tools\Twig\TemplateEngine::getInstance()->getEngine()->render(
    'natix:catalog.set.list:ajax:template',
    ['result' => $arResult, 'params' => $arParams]
);
