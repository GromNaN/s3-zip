<?php

namespace GromNaN\S3Zip;

use GromNaN\S3Zip\Input\InputInterface;

class File
{
    private InputInterface $input;

    private string $name;
    private int $offset;
    private int $length;
    private array $options;

    /**
     * @internal
     */
    public function __construct(InputInterface $input, array $options)
    {
        $this->input = $input;
        $this->options = $options;
        $this->name = $options['name'];
        $this->offset = $options['offset'];
        $this->length = $options['end_offset'] - $options['offset'] + 1;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIndex(): int
    {
        return $this->options['index'];
    }

    /**
     * Reads and extract file contents
     */
    public function getContents($length = 0): string
    {
        return gzinflate($this->fetch(), $length);
    }

    /**
     * Reads compressed file contents.
     * Binary result can be sent as gzipped HTTP response.
     */
    public function fetch(): string
    {
        $chunk = $this->input->fetch($this->offset, $this->length, 'reading file '.$this->name);

        $headerSize = 30
            +unpack('v', $chunk, 26)[1]
            +unpack('v', $chunk, 28)[1];

        return substr($chunk, $headerSize);
    }
}
