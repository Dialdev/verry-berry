<?php

namespace Natix\Component;

/**
 * Базовый компонент страницы оформления заказа.
 * Умеет определять типы страницы:
 * - make - страница оформления
 * - success - страница «спасибо»
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class SaleOrderRouter extends CommonComponent
{
    protected $needModules = [
        'iblock',
    ];

    public function executeMain()
    {
        $urlParams = $this->getUrlData();

        $getParameters = $this->request->toArray();

        $this->templatePage = $this->resolveRoute($urlParams, $getParameters);

        $this->setVariablesAlias($this->templatePage, $urlParams, $getParameters);
    }

    /**
     * Возвращает URL в виде массива, очищенный от пустых значений
     *
     * @return array
     */
    private function getUrlData()
    {
        $requestURL = $this->request->getRequestedPage();

        $requestURL = str_replace('index.php', '', $requestURL);

        $urlParams = explode('/', $requestURL);

        $urlParams = array_map('trim', $urlParams);

        $urlParams = array_filter($urlParams);

        return array_values($urlParams);
    }

    /**
     * В зависимости от параметров URL возвращает шаблон страницы
     *
     * @param array $urlParams
     * @param array $getParameters
     * @return string
     */
    private function resolveRoute(array $urlParams, array $getParameters): string
    {
        $i = count($urlParams);

        if (!isset($getParameters['ORDER_ID']))
            return 'make';

        if ($i === 3) {
            if (isset($getParameters['ERROR']))
                $pageTemplate = 'error';
            else
                $pageTemplate = 'success';
        }
        else {
            $pageTemplate = '404';
            $this->set404();
        }

        return $pageTemplate;
    }

    /**
     * Показывает 404 страницу
     */
    private function set404()
    {
        \Bitrix\Iblock\Component\Tools::process404(
            '123',
            true,
            true,
            true
        );
    }

    /**
     * @param string $templatePage
     * @param array  $urlParams
     * @param array  $getParameters
     */
    private function setVariablesAlias(string $templatePage, array $urlParams, array $getParameters)
    {
        $result = [];

        foreach ($getParameters as $key => $param)
            $result[$key] = $param;

        $this->arResult['VARIABLES'] = $result;
    }
}
