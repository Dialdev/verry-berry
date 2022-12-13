<?php

namespace Natix\Module\Api\Service\Component;

use Bitrix\Main\Context;
use Natix\Module\Api\Exception\Component\ComponentRequestException;
use Natix\Module\Api\Http\Response\ResponseInterface;
use Natix\Service\Component\InfoFabric;

/**
 * Сервис создаёт объект компонента и вызывает его публичный метод,
 * возвращающий в виде ответа @see ResponseInterface
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class ActionService
{
    /** @var InfoFabric */
    private $infoFabric;

    public function __construct(InfoFabric $infoFabric)
    {
        $this->infoFabric = $infoFabric;
    }

    /**
     * Выполняет указанный метод компонента,
     * метод должен возвращать объект @param string $componentName
     * @param string $method
     * @return ResponseInterface
     * @throws ComponentRequestException
     */
    public function executeAction(string $componentName, string $method): ResponseInterface
    {
        $componentClassName = $this->getComponentClass($componentName);
        \CBitrixComponent::includeComponentClass($componentName);
        $componentClass = new $componentClassName();
        if (!method_exists($componentClass, $method)) {
            throw new ComponentRequestException(
                sprintf('Метод %1$s не найден в компоненте %2$s', $method, $componentName)
            );
        }
        $responseAction = $componentClass->$method(Context::getCurrent()->getRequest());
        if (!$responseAction instanceof ResponseInterface) {
            throw new ComponentRequestException('Ответ метода должен быть экземпляром класса SuccessResponse');
        }
        return $responseAction;
    }

    /**
     * Возвращает имя класса компонента
     * @param string $componentName
     * @return string
     * @throws ComponentRequestException
     */
    private function getComponentClass(string $componentName): string
    {
        $componentInfoEntity = $this->infoFabric->getByComponentName($componentName);
        if (!$componentInfoEntity->isComponentClassExist()) {
            throw new ComponentRequestException(sprintf(
                'Не найден класс у компонента %s, или он не указан в Quetzal\Service\Component\ClassMap',
                $componentName
            ));
        }
        return $componentInfoEntity->getComponentClass();
    }
}
