<?php

namespace Natix\Service\Twig\Extension;
use Natix\Service\Twig\Extension\Functions\OddEvenMod;

/**
 * Расширение регистрирует дополнительный функции для шаблонов
 */
class TemplateExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'book24_template_functions';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'createOddEvenMod',
                function(string $loopName, string $odd, string $even) {
                    return new OddEvenMod($loopName, $odd, $even);
                })
        ];
    }
}
