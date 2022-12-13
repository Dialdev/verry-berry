<?php

namespace Natix\Service\Component;

/**
 * Сущность с дополнительной информацией о компоненте
 * возвращает возможность доступа к компоненту по API,
 * проверить наличие класса у компонента, получить класс
 */
class InfoEntity
{
    private $componentName = '';

    private $componentClassExist;

    private $componentClass = '';

    private $availableForApi;

    /**
     * @param string $componentName
     */
    public function __construct(string $componentName)
    {
        $this->componentName = $componentName;
    }

    /**
     * Возвращает имя компонента
     * @return string
     */
    public function getComponentName(): string
    {
        return $this->componentName;
    }

    /**
     * Проверяет - есть ли класс у компонента (должен быть зарегистрирован в классе ClassMap)
     * @return bool
     */
    public function isComponentClassExist(): bool
    {
        return $this->componentClassExist;
    }

    /**
     * Устанавливает значение, что клаасс компонента существует
     * @param bool $componentClassExist
     */
    public function setComponentClassExist(bool $componentClassExist)
    {
        $this->componentClassExist = $componentClassExist;
    }

    /**
     * Возвращает имя класса компонента (должен быть зарегистрирован в классе ClassMap)
     * @return string
     */
    public function getComponentClass(): string
    {
        return $this->componentClass;
    }

    /**
     * Устанавливает класс компонента
     * @param string $componentClass
     */
    public function setComponentClass(string $componentClass)
    {
        $this->componentClass = $componentClass;
    }

    /**
     * Возвращает доступность компонента к запросам через API (настраивается в классе SettingsMap)
     * @return bool
     */
    public function isAvailableForApi(): bool
    {
        return $this->availableForApi;
    }

    /**
     * Устанавливает - доступен ли компонент для запросов через API
     * @param bool $availableForApi
     */
    public function setAvailableForApi(bool $availableForApi)
    {
        $this->availableForApi = $availableForApi;
    }
}
