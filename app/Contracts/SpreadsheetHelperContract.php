<?php

namespace App\Contracts;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface SpreadsheetHelperContract
{
    /**
     * Create a Spreadsheet Reader from Filestream
     *
     * @param  string  $filestream
     * @param  bool    $formatDates
     * @return void
     */
    public function createReaderFromStream(UploadedFile $filestream, $formatDates = true);

    /**
     * Get Sheets from Reader
     *
     * @param  string  $filestream
     * @param  mixed   $reader
     * @return mixed
     */
    public function getSheets($filestream, $reader);

    /**
     * Collate data inside Sheets to an Array
     *
     * @param  mixed  $sheets
     * @return mixed
     */
    public function convertSheetsToArray($sheets);

    /**
     * Close Reader
     *
     * @param  mixed  $reader
     * @return mixed
     */
    public function closeReader($reader);
}
