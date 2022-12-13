<?php

namespace Natix\Service\Component;

/**
 * Фабрика для сущности с доп. информацией о компоненте
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class InfoFabric
{
    private $classMap;

    private $settingsMap;

    /**
     * InfoFabric constructor.
     * @param ClassMap $classMap
     * @param SettingsMap $settingsMap
     */
    public function __construct(ClassMap $classMap, SettingsMap $settingsMap)
    {
        $this->classMap = $classMap;
        $this->settingsMap = $settingsMap;
    }

    /**
     * Получает сущность с информацией о компоненте по его коду
     * @param string $componentName
     * @return InfoEntity
     */
    public function getByComponentName(string $componentName): InfoEntity
    {
        $infoEntity = new InfoEntity($componentName);
        $className = $this->classMap->getClassNameByComponentName($componentName);
        if ($className) {
            $infoEntity->setComponentClassExist(true);
            $infoEntity->setComponentClass($className);
        } else {
            $infoEntity->setComponentClassExist(false);
        }

        $componentSettings = $this->settingsMap->getSettingsByComponentName($componentName);
        $infoEntity->setAvailableForApi($componentSettings['API_PERMISSION'] ?? false);
        return $infoEntity;
    }
}
