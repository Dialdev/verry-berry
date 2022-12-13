<?php

namespace Natix\Component;

use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\DB\SqlExpression;
use Natix\Data\Bitrix\Finder\Iblock\IblockFinder;
use Natix\Helpers\EnvironmentHelper;
use Psr\Log\LoggerInterface;

/**
 * Базовый компонент карточки товара.
 * Умеет определять тип товара:
 * - set - карточка с букетами-комплектами
 * - other - карточка с остальными типами товаров (простой, товар с ТП)
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class CatalogElementRouter extends CommonComponent
{
    protected $needModules = [
        'iblock',
    ];
    
    protected $cacheTemplate = false;
    
    /** @var string|null */
    private $elementCode;
    
    /** @var IblockFinder */
    private $iblockFinder;
    
    /** @var LoggerInterface */
    private $logger;
    
    public function __construct($component = null)
    {
        parent::__construct($component);
        
        $this->iblockFinder = $this->getContainer()->get(IblockFinder::class);
        $this->logger = $this->getContainer()->get(LoggerInterface::class);
    }

    protected function configurate()
    {
        $this->setElementCode($this->request->get('ELEMENT_CODE'));
        
    }

    protected function executeMain()
    {
        $this->templatePage = $this->resolveRoute();
    }

    /**
     * @return string|null
     */
    public function getElementCode(): ?string
    {
        return $this->elementCode;
    }

    /**
     * @param string|null $elementCode
     */
    public function setElementCode(?string $elementCode): void
    {
        $this->elementCode = $elementCode;
    }
    
    protected function catchException(\Exception $exception, $notifier = null)
    {
        $this->logger->error(
            sprintf(
                'В компоненте %s выброшено исключение с ошибкой: %s. BackTtace: %s',
                $this->getName(),
                $exception->getMessage(),
                $exception->getTraceAsString()
            )
        );
        
        $this->set404();
    }

    /**
     * Возвращает шаблон страницы для отображения карточки товара
     * @return string
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     */
    private function resolveRoute(): string
    {
        if (!$this->getElementCode()) {
            throw new \RuntimeException('product symbol code not transferred');
        }
        
        $element = ElementTable::query()
            ->setSelect([
                'SECTION_CODE' => 'IBLOCK_SECTION.CODE',
                'PARENT_SECTION_CODE' => 'PARENT_SECTION.CODE',
            ])
            ->setFilter([
                '=IBLOCK_ID' => $this->iblockFinder->catalog(),
                '=CODE' => $this->getElementCode(),
            ])
            ->registerRuntimeField('PARENT_SECTION', [
                'data_type' => SectionTable::class,
                'reference' => [
                    '=this.IBLOCK_SECTION.IBLOCK_SECTION_ID' => 'ref.ID',
                    '=ref.IBLOCK_ID' => new SqlExpression('?i', $this->iblockFinder->catalog())
                ],
                'join_type' => 'left',
            ])
            ->setLimit(1)
            ->setCacheTtl(3600)
            ->exec()
            ->fetch();
        
        if (!$element['SECTION_CODE']) {
            throw new \RuntimeException(sprintf(
                'по символьному коду товара "%s" не удалось найти символьный код раздела',
                $this->getElementCode()
            ));
        }
        
        $config = EnvironmentHelper::getParam('catalogElement')['templates'];
        
        $template = 'other';
        
        if (isset($config[$element['SECTION_CODE']]) || isset($config[$element['PARENT_SECTION_CODE']])) {
            $template = $config[$element['SECTION_CODE']] ?? $config[$element['PARENT_SECTION_CODE']];
        }

        // Подключение SEO элемента инфоблока
        /** @var \CMain $APPLICATION */
        global $APPLICATION;

        $iterator = \CIBlockElement::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => $this->iblockFinder->catalog(),
                'CODE' => $this->getElementCode(),
            ],
            false,
            ['nTopCount' => 1],
            ['ID', 'NAME']
        );
               
        if ($item = $iterator->Fetch()) {
            $element_id = (int)$item['ID'];
            $element_name = $item['NAME'];
        }

        $iblock_id = $this->iblockFinder->catalog();
        
        if (!empty($element_id) && $element_id > 0) {
            $iblockElementSeoValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($iblock_id, $element_id);
            $META_ELEMENT  = $iblockElementSeoValues->getValues();
    
            $APPLICATION->SetPageProperty("title", $META_ELEMENT['ELEMENT_META_TITLE']);
            $APPLICATION->SetPageProperty("description", $META_ELEMENT['ELEMENT_META_DESCRIPTION']);
            $APPLICATION->SetPageProperty("keywords", $META_ELEMENT['ELEMENT_META_KEYWORDS']);

            // $APPLICATION->AddChainItem($element_name);
        }
        
        

        return $template;
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
}
