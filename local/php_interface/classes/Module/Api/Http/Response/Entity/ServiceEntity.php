<?php

namespace Natix\Module\Api\Http\Response\Entity;

/**
 * DTO c информация о сервисе, вернувшем данные
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class ServiceEntity implements ResponseEntityInterface
{
    /** @var string */
    private $name;

    private function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return static
     */
    public static function createDefault(): self
    {
        return new self('very-berry');
    }

    /**
     * Получить имя сервиса
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
        ];
    }
}
