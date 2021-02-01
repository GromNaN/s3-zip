# Work in progress

https://github.com/gromnan/s3-zip/pull/1

# Partial ZIP Reader 

Having a ZIP archive hosted on AWS S3 or an HTTP(S) server?
How to list files in the archive or read only 1 file without downloading the whole archive?

This packages use the `Range: bytes=%d-%d` header to download only the necessary chunks for listing files or reading a single file.

## Installation

Use [composer](https://getcomposer.org/) to install `gromnan/s3-zip`.

```bash
composer require gromnan/s3-zip
```

## Usage

```php
use AsyncAws\S3\S3Client;
use GromNaN\S3Zip\Archive;
use GromNaN\S3Zip\Input\S3Input;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\Log\Logger;

$logger = new Logger();
$httpClient = HttpClient::create()->setLogger($logger);
$s3 = new S3Client([/* AWS Config */], null, $httpClient, $logger);

$filename = 's3://my-bucket/path/to/archive.zip';

$input = new S3Input($s3, $filename);
$input->setLogger($logger);

$archive = new Archive($input);

// Get the list for file names in the archive
var_dump($archive->getFileNames());

// Downloads and extracts the contents of a single file
echo $archive->getFile('path/to/file/in/archive.txt')->getContents();
```

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](https://choosealicense.com/licenses/mit/)


## References

* https://en.wikipedia.org/wiki/Zip_(file_format)
* https://pkware.cachefly.net/webdocs/casestudies/APPNOTE.TXT
* https://docs.fileformat.com/compression/zip/
* https://github.com/maennchen/ZipStream-PHP/blob/master/src/File.php
* https://github.com/janakaud/zip-ninja/tree/master/readers
