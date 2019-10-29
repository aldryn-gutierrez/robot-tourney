<?php

namespace App\Libraries\Spreadsheets;

use Box\Spout\Reader\Common\Creator\ReaderFactory;
use Box\Spout\Common\Type;
use App\Exceptions\SpreadsheetHelperException;
use File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SpoutSpreadsheetHelper extends AbstractSpreadsheetHelper
{
    /**
     * Create a Spreadsheet Reader from Filestream
     *
     * @param  string  $filestream
     * @param  bool    $formatDates
     * @return void
     */
    public function createReaderFromStream(UploadedFile $filestream, $formatDates = true)
    {
        $excelMimeTypes = $this->getExcelMimeTypes();
        $csvMimeTypes = $this->getCSVMimeTypes();

        $uploadedMimeType = $filestream->getClientMimeType();
        $reader = null;
        if (in_array($uploadedMimeType, $excelMimeTypes)) {
            $reader = ReaderFactory::createFromType(Type::XLSX);
        } else if (in_array($uploadedMimeType, $csvMimeTypes)) {
            $reader = ReaderFactory::createFromType(Type::CSV);
        }

        if (is_null($reader)) {
            throw new SpreadsheetHelperException('Invalid file type found: '.$uploadedMimeType);
        }

        $reader->setShouldFormatDates($formatDates);
        return $reader;
    }

    /**
     * Get Sheets from Reader
     *
     * @param  string  $filestream
     * @param  mixed   $reader
     * @return mixed
     */
    public function getSheets($filestream, $reader)
    {
        $reader->open($filestream);

        return $reader->getSheetIterator();
    }

    /**
     * Collate data inside Sheets to an Array
     *
     * @param  mixed  $sheets
     * @return mixed
     */
    public function convertSheetsToArray($sheets)
    {
        $data = [];
        $keys = [];
        foreach ($sheets as $sheet) {
            $rowIterator = $sheet->getRowIterator();
            foreach ($rowIterator as $key => $row) {
                $row = $row->toArray();
                if ($key == 1) {
                    $keys = $row;
                    continue;
                }

                if (empty(array_filter($row))) {
                    continue;
                }

                $dataToInsert = [];
                foreach ($row as $cellKey => $cell) {
                    $dataToInsert[$keys[$cellKey]] = $cell;
                }

                $data[] = $dataToInsert;
            }
        }

        return $data;
    }

    /**
     * Close Reader
     *
     * @param  mixed  $reader
     * @return mixed
     */
    public function closeReader($reader)
    {
        return $reader->close();
    }
}
