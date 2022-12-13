<?php

namespace Natix\Service\EntityProcessingQueue\Exception;

/**
 * Исключение выбрасывается при попытке поставить в очередь не уникальную запись
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class QueueDuplicateRecordException extends \Exception
{
}
