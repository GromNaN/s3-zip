<?php


namespace GromNaN\S3Zip\Input;

interface InputInterface
{
    public function fetch(int $start, int $length, string $reason): string;

    /**
     * @return resource
     */
    public function fetchStream(int $start, int $length, string $reason);

    public function length(): int;
}
