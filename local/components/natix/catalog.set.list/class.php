<?php

namespace Natix\Component;

use Bitrix\Iblock\Component\Tools;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
use Bitrix\Main\HttpRequest;
use Maximaster\Tools\Twig\TemplateEngine;
use Natix\Data\Bitrix\Finder\FinderEmptyValueException;
use Natix\Data\Bitrix\Finder\Iblock\IblockFinder;
use Natix\Module\Api\Http\Response\ResponseInterface;
use Natix\Module\Api\Http\Response\SuccessResponse;
use Natix\Service\Catalog\Bouquets\Collection\SetEntityCollection;
use Natix\Service\Catalog\Bouquets\Dto\SetQueryParamsDto;
use Natix\Service\Catalog\Bouquets\Exception\BerriesFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\BouquetFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\ImageFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\PackingFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\PriceFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\SetFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\SizeFactoryException;
use Natix\Service\Catalog\Bouquets\Service\Factory\SetFactory;
use Natix\UI\PageNavigation;
use Psr\Log\LoggerInterface;

/**
 * Компонент списка товаров-комплектов букетов
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class CatalogSetList extends CommonComponent
{
    /**
     * @var array
     */
    protected $needModules = [
        'iblock',
        'catalog',
    ];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var IblockFinder
     */
    private $iblockFinder;

    /**
     * @var SetFactory
     */
    private $setFactory;

    /**
     * @var SetEntityCollection
     */
    private $setEntityCollection;

    /**
     * @var int
     */
    private $sectionId;

    /**
     * @var array
     */
    private $section;

    /**
     * @var array
     */
    private $filter;

    /**
     * @var string
     */
    private $sortField;

    /**
     * @var string
     */
    private $sortOrder;

    /**
     * @var PageNavigation|null
     */
    private $pagination;

    /**
     * @param null $component
     */
    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->logger = $this->getContainer()->get(LoggerInterface::class);
        $this->iblockFinder = $this->getContainer()->get(IblockFinder::class);
        $this->setFactory = $this->getContainer()->get(SetFactory::class);
    }

    protected function configurate()
    {
        $this->arParams['CACHE_TYPE'] = 'Y';
        $this->arParams['CACHE_TIME'] = 3600;
        $this->arParams['CACHE_GROUPS'] = 'N';
        $this->arParams['SECTION_CODE'] = $this->request->get('SECTION_CODE');

        /*iif (strlen($this->arParams['SECTION_CODE']) <= 0) {
            $this->process404();
        }

        f (
            !isset($this->arParams['FILTER_NAME'])
            || empty($this->arParams['FILTER_NAME'])
            || !preg_match('/^[A-Za-z_][A-Za-z01-9_]*$/', $this->arParams['FILTER_NAME'])
        ) {
            $this->arParams['FILTER_NAME'] = 'arrFilter';
        } else {
            $this->arParams['FILTER_NAME'] = trim($this->arParams['FILTER_NAME']);
        }

        $this->filter = isset($GLOBALS[$this->arParams['FILTER_NAME']]) && is_array($GLOBALS[$this->arParams['FILTER_NAME']])
            ? $GLOBALS[$this->arParams['FILTER_NAME']]
            : [];*/
        
        $this->filter = $this->arParams['FILTER'] ?? [];

        $this->arParams['ELEMENT_PER_PAGE'] = (int) $this->arParams['ELEMENT_PER_PAGE'] ?: 6;
        $this->arParams['SORT_FIELD'] = $this->arParams['SORT_FIELD'] ?? 'SORT';
        $this->arParams['SORT_ORDER'] = $this->arParams['SORT_ORDER'] ?? 'ASC';

        $this->sortField = $this->arParams['SORT_FIELD'];
        $this->sortOrder = $this->arParams['SORT_ORDER'];

        $this->addCacheAdditionalId(Context::getCurrent()->getRequest()->getRequestUri());
    }

    /**
     * @throws FinderEmptyValueException
     * @throws ArgumentException
     * @throws BerriesFactoryException
     * @throws BouquetFactoryException
     * @throws ImageFactoryException
     * @throws PackingFactoryException
     * @throws PriceFactoryException
     * @throws SetFactoryException
     * @throws SizeFactoryException
     */
    protected function executeMain()
    {
        $this->initPagination();

        if ($this->arParams['SECTION_CODE']) {
            $this->sectionId = $this->getSetIdBySetCode($this->arParams['SECTION_CODE']);

            array_unshift($this->filter, [
                '=IBLOCK_SECTION_ID' => $this->sectionId,
            ]);
            
            $this->arResult['section'] = $this->section;
        }

        $this->setEntityCollection = $this->setFactory->buildByParams(
            $this->getSetQueryParamsDto(),
            $this->pagination
        );

        $this->arResult['list'] = SetEntityCollection::toState($this->setEntityCollection);

        $this->arResult['nav'] = $this->getNavParams();
    }

    /**
     * Возвращает id раздела ИБ с комплектами (букеты) по его коду
     * @param string $setCode
     * @return int
     * @throws FinderEmptyValueException
     */
    protected function getSetIdBySetCode(string $setCode): int
    {
        if (strlen($setCode) <= 0) {
            throw new \InvalidArgumentException('$setCode должен быть не пустой строкой');
        }

        $section = SectionTable::query()
            ->setSelect(['ID', 'NAME', 'DESCRIPTION'])
            ->setFilter([
                '=IBLOCK_ID' => $this->iblockFinder->catalog(),
                '=CODE' => $setCode,
            ])
            ->setLimit(1)
            ->exec()
            ->fetch();

        if (!isset($section['ID']) || $section['ID'] <= 0) {
            throw new \RuntimeException(sprintf(
                'По коду "%s" не найден раздел в каталоге товаров',
                $setCode
            ));
        }

        $this->section = $section;

        return (int)$section['ID'];
    }

    /**
     * Возвращает DTO для параметров запроса коллекции комплектов
     * @return SetQueryParamsDto
     */
    protected function getSetQueryParamsDto()
    {
        return new SetQueryParamsDto(
            $this->filter ?? null,
            $this->sortField ?? null,
            $this->sortOrder ?? null,
            $this->pagination->getLimit(),
            $this->pagination->getOffset()
        );
    }

    protected function process404()
    {
        Tools::process404('', true, true, true);
    }

    /**
     * Обработка ошибок в карточке товара
     *
     * @param \Exception $exception
     * @param null $notifier
     */
    protected function catchException(\Exception $exception, $notifier = null)
    {
        $currentPage = Context::getCurrent()->getRequest()->getRequestedPage();

        $this->logger->error(
            sprintf(
                'Ошибка в карточке товара на странице %s: %s',
                $currentPage,
                $exception->getMessage()
            ),
            ['func_name' => __METHOD__]
        );
    }

    /**
     * Init page navigation
     */
    public function initPagination()
    {
        $this->pagination = new PageNavigation('page');

        $this->pagination->allowAllRecords(false)
            ->setPageSize($this->arParams['ELEMENT_PER_PAGE'])
            ->initFromUri();
    }

    protected function getNavParams(): array
    {
        return [
            'id' => $this->pagination->getId(),
            'pageSizes' => $this->pagination->getPageSizes(),
            'pageSize' => $this->pagination->getPageSize(),
            'pageCount' => $this->pagination->getPageSize() > 0
                ? ceil($this->pagination->getRecordCount() / $this->pagination->getPageSize())
                : 0,
            'recordCount' => $this->pagination->getRecordCount(),
            'currentPage' => $this->pagination->getCurrentPage(),
            'allowAll' => $this->pagination->allRecordsAllowed(),
            'allRecords' => $this->pagination->allRecordsShown(),
        ];
    }

    public function returnDatas(): void
    {
        /** @var \CMain $APPLICATION */
        global $APPLICATION;

        $APPLICATION->SetTitle($this->section['NAME']);

        $APPLICATION->AddChainItem($this->section['NAME']);

        parent::returnDatas();
    }

    /**
     * Отвечает за ajax-подгрузку подборки товаров на главной странице
     * @param HttpRequest $request
     * @return ResponseInterface
     * @throws ArgumentException
     * @throws BerriesFactoryException
     * @throws BouquetFactoryException
     * @throws FinderEmptyValueException
     * @throws ImageFactoryException
     * @throws PackingFactoryException
     * @throws PriceFactoryException
     * @throws SetFactoryException
     * @throws SizeFactoryException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function loadMainSelection(HttpRequest $request): ResponseInterface
    {
        $params = $request->toArray();

        $this->arParams = $params['PARAMS'];

        $this->configurate();

        $this->executeMain();

        $html = TemplateEngine::getInstance()->getEngine()->render(
            'natix:catalog.set.list:main.selection',
            ['result' => $this->arResult]
        );
        
        $response = new SuccessResponse(
            ['html' => $html],
            200
        );
        
        return $response;
    }
}
