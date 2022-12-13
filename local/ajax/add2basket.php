<?php
/**
 * Скрипт добавляет товар в корзину по ajax-запросу
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */

use Bitrix\Currency\CurrencyManager;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Web\Json;
use Bitrix\Sale;
use Natix\Service\Catalog\Bouquets\Entity\BerryEntity;
use Natix\Service\Catalog\Bouquets\Service\Factory\SetFactory;
use Natix\Service\Tools\Catalog\ProductTypeChecker;
use Psr\Log\LoggerInterface;

define('BX_BUFFER_USED', true);
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('NO_AGENT_STATISTIC', true);
define('STOP_STATISTICS', true);
define('SITE_ID', 's1');

if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
}

/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

$prepareScriptName = ltrim(
    str_replace(
        dirname($_SERVER['DOCUMENT_ROOT']),
        '',
        __FILE__
    ),
    DIRECTORY_SEPARATOR
);

/** @var LoggerInterface $logger */
$logger = \Natix::$container->get(LoggerInterface::class);

$loggerContext = [
    'script_name' => $prepareScriptName,
];

$result = [
    'STATUS' => '',
    'MESSAGE' => '',
];

try {
    foreach (['iblock', 'catalog', 'sale'] as $module) {
        if (!Loader::includeModule($module)) {
            throw new LoaderException(sprintf('unable to include module %s', $module));
        }
    }

    $request = Application::getInstance()->getContext()->getRequest();
    $productId = (int)$request->getQuery('id');
    $quantity = $request->getQuery('quantity') ? (int)$request->getQuery('quantity') : 1;

    if (!$productId) {
        throw new RuntimeException('не указан id товара');
    }

    $basket = Sale\Basket::loadItemsForFUser(
        Sale\Fuser::getId(),
        Context::getCurrent()->getSite()
    );

    $arProps = [];

    try {
        /** @var ProductTypeChecker $productTypeChecker */
        $productTypeChecker = \Natix::$container->get(ProductTypeChecker::class);
        
        if ($productTypeChecker->isTypeSet($productId)) {
            /** @var SetFactory $setFactory */
            $setFactory = \Natix::$container->get(SetFactory::class);

            $setEntity = $setFactory->buildById($productId);


            if ($setEntity->isExistSize()) {
                $arProps[] = [
                    'NAME' => 'Размер',
                    'CODE' => 'SIZE',
                    'VALUE' => $setEntity->getSize()->getName(),
                    'SORT' => 100,
                ];
            }

            if ($setEntity->isExistBerries()) {
                $berryNames = [];

                /** @var BerryEntity $berryEntity */
                foreach ($setEntity->getBerries()->getIterator() as $berryEntity) {
                    $berryNames[] = $berryEntity->getCardName();
                }

                $arProps[] = [
                    'NAME' => 'Доп.ягоды',
                    'CODE' => 'BERRIES',
                    'VALUE' => implode(', ', $berryNames),
                    'SORT' => 200,
                ];
            }

            if ($setEntity->isExistPacking()) {
                $arProps[] = [
                    'NAME' => 'Упаковка',
                    'CODE' => 'PACKING',
                    'VALUE' => $setEntity->getPacking()->getCardName(),
                    'SORT' => 300,
                ];
            }
        }

    } catch (\Exception $exception) {
        $logger->error(
            sprintf(
                'Ошибка в сервисе букетов: %s',
                $exception->getMessage()
            ),
            $loggerContext + ['product_id' => $productId]
        );
    }

    if ($item = $basket->getExistsItem('catalog', $productId, $arProps)) {

        $item->setField('QUANTITY', $item->getQuantity() + $quantity);

    } else {

        $iblockElement = ElementTable::getRow([
            'select' => [
                'IBLOCK_ID',
                'XML_ID',
                'IBLOCK_XML_ID' => 'IBLOCK.XML_ID',
            ],
            'filter' => [
                '=ID' => $productId,
            ],
        ]);

        $item = $basket->createItem('catalog', $productId);

        $item->setFields([
            'PRODUCT_ID' => $productId,
            'QUANTITY' => $quantity,
            'CURRENCY' => CurrencyManager::getBaseCurrency(),
            'LID' => Context::getCurrent()->getSite(),
            'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider',
            'CATALOG_XML_ID' => $iblockElement['IBLOCK_XML_ID'],
            'PRODUCT_XML_ID' => $iblockElement['XML_ID'],
        ]);

        $property = $item->getPropertyCollection();

        $property->setProperty($arProps);
    }

    $basket->save();
    $basket->refreshData();

    $result['STATUS'] = 'SUCCESS';
    $result['QUANTITY'] = $item->getQuantity();
    $result['PRICE'] = $item->getPrice();
    $result['BASKET_QUANTITY'] = array_sum($basket->getQuantityList());
    $result['MESSAGE'] = 'Товар успешно добавлен в корзину';

} catch (\Exception $e) {
    $result['STATUS'] = 'ERROR';
    $result['MESSAGE'] = ($errorMessage = sprintf('Ошибка добавления товара в корзину: %s', $e->getMessage()));

    $logger->error($errorMessage, $loggerContext + ['product_id' => $productId]);
}

ob_start();
/** @global CMain $APPLICATION */
$APPLICATION->IncludeFile(SITE_DIR . '/local/include/basket_small.php');
$result['SMALL_BASKET'] = ob_get_clean();

header('Content-Type: application/json');
echo Json::encode($result);
exit;
