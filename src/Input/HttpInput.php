<?php


namespace GromNaN\S3Zip\Input;

use AsyncAws\S3\S3Client;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpInput implements InputInterface
{
    use LoggerAwareTrait;

    private HttpClientInterface $httpClient;
    private string $filename;

    public function __construct(HttpClientInterface $httpClient, string $filename)
    {
        $this->httpClient = $httpClient;
        $this->filename = $filename;

        $parts = parse_url($this->filename);

        if ('http' !== $parts['scheme'] && 'https' !== $parts['scheme']) {
            throw new \InvalidArgumentException('Filename is not an HTTP(S) url.');
        }

        $this->logger = new NullLogger();
    }

    public function fetch(int $start, int $length, string $reason): string
    {
        $end = $start + $length - 1;

        $this->logger->info(sprintf('Fetching bytes %d-%d from %s as %s.', $start, $end, $this->filename, $reason));

        $res = $this->httpClient->request('GET', $this->filename, [
            'headers' => [
                'Range' => sprintf('bytes=%d-%d', $start, $end),
            ],
        ]);

        return $res->getContent();
    }

    /**
     * {@inheritDoc}
     */
    public function fetchStream(int $start, int $length, string $reason)
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function length(): int
    {
        $res = $this->httpClient->request('HEAD', $this->filename);
        $headers = $res->getHeaders();

        if (!isset($headers['content-length'][0]) && $headers['content-length'][0] !== 'application/zip') {
            throw new \RuntimeException(sprintf('Not a ZIP archive.'));
        }

        if (!array_key_exists('content-length', $headers)) {
            throw new \RuntimeException(sprintf('Cannot read content-length.'));
        }

        return (int) reset($headers['content-length']);
    }
}
