<?php

namespace GromNaN\S3Zip\Input;

interface InputInterface
{
    public function fetch(int $start, int $length, string $reason): string;

    public function length(): int;
}
