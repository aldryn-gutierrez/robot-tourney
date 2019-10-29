<?php

namespace App\Libraries\Spreadsheets;

use App\Contracts\SpreadsheetHelperContract;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractSpreadsheetHelper implements SpreadsheetHelperContract
{
    /**
     * Get CSV Mime Types
     *
     * @return array
     */
    public function getCSVMimeTypes()
    {
        return [
            'text/csv',
            'text/x-csv',
            'text/comma-separated-values',
            'text/x-comma-separated-values',
            'application/csv',
            'text/plain',
        ];
    }

    /**
     * Get Excel Mime Types
     *
     * @return array
     */
    public function getExcelMimeTypes()
    {
        return [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'application/excel',
            'application/x-excel',
            'application/x-msexcel',
        ];
    }

    /**
     * Create a Spreadsheet Reader from Filestream
     *
     * @param  UploadedFile  $filestream
     * @param  bool    $formatDates
     * @return void
     */
    abstract function createReaderFromStream(UploadedFile $filestream, $formatDates = true);

    /**
     * Get Sheets from Reader
     *
     * @param  string  $filestream
     * @param  mixed   $reader
     * @return mixed
     */
    abstract function getSheets($filestream, $reader);

    /**
     * Collate data inside Sheets to an Array
     *
     * @param  mixed  $sheets
     * @return mixed
     */
    abstract function convertSheetsToArray($sheets);

    /**
     * Close Reader
     *
     * @param  mixed  $reader
     * @return mixed
     */
    abstract function closeReader($reader);
}
