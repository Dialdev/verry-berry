<?php

namespace Natix\Component;

use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Web\Uri;
use Natix\Data\Bitrix\Finder\Iblock\IblockFinder;
use Natix\Helpers\EnvironmentHelper;
use Natix\Service\Catalog\Bouquets\Collection\SizeEntityCollection;
use Natix\Service\Catalog\Bouquets\Entity\SizeEntity;
use Natix\Service\Catalog\Bouquets\Service\Factory\SizeFactory;

/**
 * Компонент фильтра каталога по размерам
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class CatalogFilterSize extends CommonComponent
{
    const SIZE_PARAM_NAME = 'size';

    protected $needModules = [
        'iblock',
    ];

    /**
     * @var IblockFinder
     */
    private $iblockFinder;

    /**
     * @var SizeFactory
     */
    private $sizeFactory;

    /**
     * @var int
     */
    private $activeSizeId;

    /**
     * @var SizeEntityCollection
     */
    private $sizeEntityCollection;

    /**
     * @var string
     */
    private $sectionCode;

    /**
     * @param null $component
     */
    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->iblockFinder = $this->getContainer()->get(IblockFinder::class);
        $this->sizeFactory = $this->getContainer()->get(SizeFactory::class);
        $this->sizeEntityCollection = $this->getContainer()->get(SizeEntityCollection::class);
    }

    /**
     * @return string
     */
    public function getSectionCode(): string
    {
        return $this->sectionCode;
    }

    /**
     * @param string $sectionCode
     */
    public function setSectionCode(string $sectionCode): void
    {
        $this->sectionCode = $sectionCode;
    }

    protected function configurate(): void
    {
        $this->arParams['CACHE_TYPE'] = 'Y';
        $this->arParams['CACHE_TIME'] = 3600;
        $this->arParams['CACHE_GROUPS'] = 'N';
        $this->arParams['SIZE_PARAM_NAME'] = self::SIZE_PARAM_NAME;

        if ($this->request->get(self::SIZE_PARAM_NAME) !== null) {
            $this->activeSizeId = (int)$this->request->get(self::SIZE_PARAM_NAME);

            global $arrFilter;
            $arrFilter['=PROPERTY_SIZE'] = $this->activeSizeId;
        }

        $this->setSectionCode($this->request->get('SECTION_CODE'));

        $this->addCacheAdditionalId($this->activeSizeId);
        $this->addCacheAdditionalId($this->getSectionCode());
    }

    protected function executeMain(): void
    {
        if (!$this->isShowSizeFilter()) {
            return;
        }

        $this->sizeEntityCollection = $this->sizeFactory->buildAllSizes();
        $this->arResult['sizes'] = SizeEntityCollection::toState($this->sizeEntityCollection);

        $uri = new Uri($this->request->getRequestUri());
		
		$uri->deleteParams(array("price_from","price_to"));

        /** @var SizeEntity $sizeEntity */
        foreach ($this->sizeEntityCollection->getIterator() as $sizeEntity) {
            $sizeId = $sizeEntity->getId();
            $sizeName = $sizeEntity->getName();
            $sizesFilterConfig = EnvironmentHelper::getParam('sizes')['filter'];

            $this->arResult['sizes'][$sizeId]['active'] = ($sizeId === $this->activeSizeId);
            $this->arResult['sizes'][$sizeId]['filter_name'] = $sizesFilterConfig[$sizeName] ?: $sizeName;
            $this->arResult['sizes'][$sizeId]['url'] = sprintf('?%s=%s', self::SIZE_PARAM_NAME, $sizeId);

            $uri->addParams([
                self::SIZE_PARAM_NAME => $sizeId,
            ]);
            $this->arResult['sizes'][$sizeId]['url'] = $uri->getUri();
        }
    }

    /**
     * Проверяет, нужно ли показывать фильтр по размеру
     *
     * @return bool
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     */
    private function isShowSizeFilter(): bool
    {
        $section = SectionTable::query()
            ->setSelect([
                'CODE',
                'PARENT_SECTION_CODE' => 'PARENT_SECTION.CODE',
            ])
            ->setFilter([
                '=IBLOCK_ID' => $this->iblockFinder->catalog(),
                '=CODE' => $this->getSectionCode()
            ])
            ->setLimit(1)
            ->setCacheTtl(3600)
            ->exec()
            ->fetch();

        if (!$section['CODE']) {
            throw new \RuntimeException(sprintf(
                'по символьному коду раздела "%s" не удалось найти раздел',
                $this->getSectionCode()
            ));
        }

        $config = EnvironmentHelper::getParam('catalogElement')['templates'];
        $template = $config[$section['CODE']] ?? $config[$section['PARENT_SECTION_CODE']];
        
        return $template === 'set';
    }
}
