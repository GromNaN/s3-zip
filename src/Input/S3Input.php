<?php

namespace GromNaN\S3Zip\Input;

use AsyncAws\S3\S3Client;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class S3Input implements InputInterface
{
    use LoggerAwareTrait;

    private S3Client $s3;
    private string $filename;
    private string $bucket;
    private string $key;

    public function __construct(S3Client $s3, string $filename)
    {
        $this->s3 = $s3;
        $this->filename = $filename;

        $parts = parse_url($this->filename);

        if ('s3' !== $parts['scheme']) {
            throw new \InvalidArgumentException('Filename is not an S3 url.');
        }

        $this->bucket = $parts['host'];
        $this->key = substr($parts['path'], 1);
        $this->logger = new NullLogger();
    }

    public function fetch(int $start, int $length, string $reason): string
    {
        $end = $start + $length - 1;

        $this->logger->info(sprintf('Fetching bytes %d-%d from %s on %s as %s.', $start, $end, $this->key, $this->bucket, $reason));

        $res = $this->s3->getObject([
            'Bucket' => $this->bucket,
            'Key' => $this->key,
            'Range' => sprintf('bytes=%d-%d', $start, $end)
        ]);
        $res->resolve();

        return $res->getBody()->getContentAsString();
    }

    public function length(): int
    {
        $res = $this->s3->headObject([
            'Bucket' => $this->bucket,
            'Key' => $this->key,
        ]);
        $res->resolve();

        return (int) $res->getContentLength();
    }
}
