<?php

namespace Natix\Component;

/**
 * Компонент банера с видео на главной
 */
class MainVideoBanner extends CommonComponent
{
    private int $blockId;

    protected $needModules = [
        'iblock',
    ];

    protected $exceptionNotifier = false;

    protected function configurate()
    {
        $this->blockId = $this->arParams['BLOCK_ID'];
    }

    protected function executeMain()
    {
        if (!$this->blockId)
            throw new \RuntimeException('Не передан идентификатор блока');

        $this->arResult['BLOCK'] = $this->getBlockData();
    }


    protected function getBlockData(): ?array
    {
        $banner = \CIBlockElement::GetList(false, [
            'IBLOCK_ID' => $this->blockId,
            'ACTIVE'    => 'Y',
        ],
            false,
            ['nTopCount' => 1]
        )->GetNextElement();

        if (!$banner)
            return null;
        
        $result =  [
            'props'  => $banner->GetProperties(),
            'fields' => $banner->GetFields(),
        ];

        $result['props']['photo']['path'] = \CFile::GetPath($result['props']['photo']['VALUE']);
        
        return $result;
    }
}
