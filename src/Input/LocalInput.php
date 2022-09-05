<?php

namespace GromNaN\S3Zip\Input;

class LocalInput implements InputInterface
{
    private string $filename;

    /**
     * @var resource
     */
    private $handle;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
        $handle = fopen($filename, 'rb');
        if (false === $handle) {
            throw new \RuntimeException('File not found: '.$filename);
        }
        $this->handle = $handle;
    }

    public function fetch(int $start, int $length, string $reason): string
    {
        fseek($this->handle, $start);

        return fread($this->handle, $length);
    }

    public function length(): int
    {
        return fstat($this->handle)['size'];
    }
}
