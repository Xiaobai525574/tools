<?php

namespace App\Http\Services\SqlExcelService;

use App\Http\Services\sqlExcelService;

class Iterator implements \Iterator
{
    /**
     * Spreadsheet to iterate.
     * @var sqlExcelService
     */
    private $subject;

    /**
     * Current iterator position.
     *
     * @var int
     */
    private $position = 0;

    /**
     * Start position
     * @var int
     */
    private $startSheet = 0;

    /**
     * Create a new worksheet iterator.
     * Iterator constructor.
     * @param sqlExcelService $subject
     */
    public function __construct(sqlExcelService $subject, $startSheet = 0)
    {
        // Set subject
        $this->subject = $subject;
        $this->startSheet = $startSheet;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->subject);
    }

    /**
     * Rewind iterator.
     */
    public function rewind()
    {
        $this->position = $this->startSheet;
    }

    /**
     * Current Worksheet.
     * @return mixed|\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function current()
    {
        return $this->subject->getSheet($this->position);
    }

    /**
     * Current key.
     *
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Next value.
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Are there more Worksheet instances available?
     *
     * @return bool
     */
    public function valid()
    {
        return $this->position < $this->subject->getSheetCount() && $this->position >= 0;
    }
}
