<?php

namespace classes;

/**
 * Class GoogleXML класс для работы с XML-фидом Google-а
 *
 * @package classes
 */
class GoogleXML extends FacebookXML
{
    public function __construct()
    {
        parent::__construct();

        self::createNewFeedObj();
    }
    
    /**
     * Добавить товар в фид
     *
     * @param array $productData
     * @param bool  $exclude
     */
    public function addProductToFeed(array $productData, bool $exclude = false): void
    {
        parent::addProductToFeed($productData, true);
    }

    /**
     * Сохранить фид в файл
     *
     * @param string|null $file
     * @throws \Exception
     */
    public function saveFeed(string $file = null): void
    {
        $file = $file ?: self::$config['googleFeed'];

        parent::saveFeed($file);
    }
}
