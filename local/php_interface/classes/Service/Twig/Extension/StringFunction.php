<?php

namespace Natix\Service\Twig\Extension;

/**
 * Расширени для твига, регистриует дополнительные фильтря для работы со строками
 */
class StringFunction extends \Twig_Extension
{
    public function getName()
    {
        return 'book24_filters';
    }

    public function getFilters()
    {
        return [
            /** Фильтр добавляет функцию showMatch, которая выделяет указанный текст в строке*/
            new \Twig_SimpleFilter('showMatch', function($input, $pattern) {
                return mb_eregi_replace(
                    $pattern,
                    sprintf('<strong>%s</strong>', strtolower($pattern)),
                    $input
                );
            }),
            new \Twig_SimpleFilter('htmlspecialcharsbx', function($input) {
                return htmlspecialcharsbx($input);
            })
        ];
    }
}
