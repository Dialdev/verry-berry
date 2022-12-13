<?php

namespace classes;

abstract class _CsvFileBase extends _Base
{
    /**
     * Сохранить фид
     *
     * @return string
     */
    public function saveCSV(): string
    {
        $fp = fopen($file = static::$csvFile, 'w+');

        foreach ($this->csvData as $csvLineData)
            fputcsv($fp, $csvLineData, static::$csvDelimiter);

        fclose($fp);

        self::prints("CSV-файл сохранен в '$file'");

        return $file;
    }
}
