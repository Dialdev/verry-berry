<?php

namespace Natix\Service\Tools\Data\PhoneNumber;

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PhoneNumberService
{
    /**
     * Форматирует телефон в стандартизированный формат
     *
     * @param $phone
     * @return string
     * @throws PhoneNumberServiceException
     */
    public function format($phone): string
    {
        try {
            $phoneNumberUtil = PhoneNumberUtil::getInstance();

            $phoneNumber = $phoneNumberUtil->parse($phone, 'RU', null, true);

            $phoneNumberInternational = $phoneNumberUtil->format(
                $phoneNumber,
                PhoneNumberFormat::INTERNATIONAL
            );

            return $phoneNumberInternational;
        } catch (\Exception $e) {
            throw new PhoneNumberServiceException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
