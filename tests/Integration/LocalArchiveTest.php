<?php

namespace GromNaN\S3Zip\Tests\Integration;

use GromNaN\S3Zip\Input\InputInterface;
use GromNaN\S3Zip\Input\LocalInput;

class LocalArchiveTest extends ArchiveTest
{
    protected function getInput(): InputInterface
    {
        $filename = $this->createArchive();

        return new LocalInput($filename);
    }
}
