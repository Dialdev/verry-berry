<?php

namespace Natix\Service\Component;

/**
 * Class SettingsMap
 * @package Natix\Service\Component
 *
 * Возвращает дополнительные настройки компонентов
 */
class SettingsMap
{
    /**
     * Возвращает настройки компонента по его названию
     * @param string $componentName
     * @return array
     */
    public function getSettingsByComponentName(string $componentName): array
    {
        return $this->getSettings()[$componentName] ?? [];
    }

    /**
     * @return array
     */
    private function getSettings(): array
    {
        return [];
    }
}
