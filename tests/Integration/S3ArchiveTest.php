<?php


namespace GromNaN\S3Zip\Tests\Integration;

use AsyncAws\S3\S3Client;
use GromNaN\S3Zip\Input\InputInterface;
use GromNaN\S3Zip\Input\S3Input;

class S3ArchiveTest extends ArchiveTest
{
    private S3Client $s3;
    private string $bucket;
    private string $key;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bucket = getenv('S3_BUCKET');
        $this->key = 'testing/archive.zip';
        $this->s3 = new S3Client([
            'endpoint' => getenv('S3_ENDPOINT'),
            'pathStyleEndpoint' => true,
        ]);
    }

    protected function tearDown(): void
    {
        $this->s3->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $this->key,
        ])->resolve();
    }

    protected function getInput(): InputInterface
    {
        $filename = $this->createArchive();

        $this->s3->putObject([
            'Bucket' => $this->bucket,
            'Key' => $this->key,
            'Body' => fopen($filename, 'r'),
        ])->resolve();

        return new S3Input($this->s3, sprintf('s3://%s/%s', $this->bucket, $this->key));
    }
}
