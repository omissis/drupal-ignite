<?php

namespace Test;

class CsvFileIterator implements \Iterator
{
    protected $key;
    protected $file;
    protected $length;
    protected $current;
    protected $delimiter;
    protected $enclosure;
    protected $skipFirstLine;

    public function __construct($file, $length = 0, $delimiter = ',', $enclosure = '"', $skipFirstLine = false)
    {
        $this->key  = 0;
        $this->file = fopen($file, 'r');

        $this->length    = $length;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;

        $this->skipFirstLine = $skipFirstLine;
    }

    public function __destruct()
    {
        fclose($this->file);
    }

    public function rewind()
    {
        rewind($this->file);
        $this->current = fgetcsv($this->file, $this->length, $this->delimiter, $this->enclosure);
        $this->key = 0;

        if ($this->skipFirstLine) {
            $this->next();
        }
    }

    public function valid()
    {
        return !feof($this->file);
    }

    public function key()
    {
        return $this->key;
    }

    public function current()
    {
        return $this->current;
    }

    public function next()
    {
        $this->current = fgetcsv($this->file, $this->length, $this->delimiter, $this->enclosure);
        $this->key++;
    }
}
