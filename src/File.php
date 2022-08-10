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

    public function getContents($length = 0): string
    {
        $chunk = $this->input->fetch($this->offset, $this->length, 'reading file '.$this->name);

        $headerSize = 30
            +unpack('v', $chunk, 26)[1]
            +unpack('v', $chunk, 28)[1];

        return gzinflate(substr($chunk, $headerSize), $length);
    }

    /**
     * @return resource
     */
    public function getUncompressedStream()
    {
        return $this->input->fetchStream($this->offset, $this->length, 'streaming file '.$this->name);
    }
}
