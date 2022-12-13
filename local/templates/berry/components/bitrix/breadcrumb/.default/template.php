<?php

if (!$arResult) {
    return '';
}

return \Maximaster\Tools\Twig\TemplateEngine::getInstance()->getEngine()->render(
    'bitrix:breadcrumb:.default:breadcrumbs',
    ['result' => $arResult, 'params' => $arParams]
);
