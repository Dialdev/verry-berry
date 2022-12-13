<?php

namespace Natix\Module\Api\Service\Sale\Order\Bonus;

use Bitrix\Currency\CurrencyManager;
use Natix\Helpers\StringHelper;

/**
 * Сервис работы с бонусами пользователя (внутренним счётом)
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class BonusService
{
    /**
     * Возвращает информацию по доступным бонусам пользователя
     * 
     * @param int $userId
     *
     * @return array
     */
    public function getUserBonuses(int $userId): array
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('Не передан идентификатор пользователя');
        }
        
        $bonuses = \CSaleUserAccount::GetByUserID($userId, CurrencyManager::getBaseCurrency());
        
        return [
            'ID' => $bonuses['ID'] ? (int)$bonuses['ID'] : 0,
            'USER_ID' => $bonuses['USER_ID'] ? (int)$bonuses['USER_ID'] : 0,
            'CURRENT_BUDGET' => $bonuses['CURRENT_BUDGET'] ? (float)$bonuses['CURRENT_BUDGET'] : 0.0,
            'CURRENT_BUDGET_FORMATTED' => sprintf(
                '%s %s',
                number_format((int)$bonuses['CURRENT_BUDGET'], 0, '.', ' '),
                StringHelper::pluralForm((int)$bonuses['CURRENT_BUDGET'], ['бонус', 'бонуса', 'бонусов'])
            ),
            'LOCKED' => $bonuses['LOCKED'] ?? '',
            'DATE_LOCKED' => $bonuses['DATE_LOCKED'] ?? '',
        ];
    }
}
