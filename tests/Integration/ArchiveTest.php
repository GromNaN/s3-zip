<?php

namespace GromNaN\S3Zip\Tests\Integration;

use GromNaN\S3Zip\Archive;
use GromNaN\S3Zip\File;
use GromNaN\S3Zip\Input\InputInterface;
use GromNaN\S3Zip\Input\LocalInput;
use ZipArchive;
use PHPUnit\Framework\TestCase;

abstract class ArchiveTest extends TestCase
{
    private $filename;
    protected function setUp(): void
    {
        $this->filename = __DIR__.'/tmp/archive.zip';
        if (!is_dir(dirname($this->filename))) {
            mkdir(dirname($this->filename));
        }
        if (file_exists($this->filename)) {
            unlink($this->filename);
        }
    }

    public function testArchive()
    {
        $input = $this->getInput();
        $archive = new Archive($input);

        $files = $archive->getFiles();
        $this->assertCount(11, $files);

        $this->assertInstanceOf(File::class, $files[0]);
        $this->assertSame($files[5], $archive->getFile($files[5]->getName()));

        $contents = $files[3]->getContents();
        $compressedContents = $files[3]->fetch();
        $this->assertSame($contents, gzinflate($compressedContents));
    }

    /**
     * @return string Local file path
     */
    protected function createArchive(): string
    {
        $zip = new ZipArchive();
        $created = $zip->open($this->filename, ZipArchive::CREATE);
        $this->assertTrue($created, 'Archive created');

        srand(0);
        for ($i=0; $i<11; $i++) {
            $length = (100+$i*10);
            $contents = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)))), 1, $length);
            $name = sprintf('File %d-%s.txt', $i, str_repeat('o', $i));
            $zip->addFromString($name, $contents);
        }

        $zip->close();

        return $this->filename;
    }

    abstract protected function getInput(): InputInterface;
}
