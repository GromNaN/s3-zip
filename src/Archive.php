<?php


namespace GromNaN\S3Zip;

use GromNaN\S3Zip\Input\InputInterface;

class Archive
{
    private InputInterface $input;
    private array $filesByIndex;
    private array $filesByName;

    public function __construct(InputInterface $input)
    {
        $this->input = $input;
        $this->initCentralDirectory();
    }

    private function initCentralDirectory()
    {
        $this->filesByIndex = [];
        $this->filesByName = [];

        $size = $this->input->length();

        $eocd = $this->input->fetch($size - 22, 22, "end of central directory (EOCD)");

        if (unpack('V', $eocd, 0)[1] !== 0x06054b50) {
            throw new \RuntimeException('Not a ZIP archive (or contains a comment, not supported).');
        }

        $cd_start = unpack('V', $eocd, 16)[1];
        $cd_size = unpack('V', $eocd, 12)[1];

        $cd = $this->input->fetch($cd_start, $cd_size, "central directory (CD)");

        $i = 0;
        $index = 0;
        $files = [];
        while ($i < $cd_size) {
            if (unpack('V', substr($cd, $i, 4))[1] !== 0x02014b50) {
                throw new \RuntimeException('Not a central directory file header');
            }
            $fileNameLength = unpack('v', $cd, $i+28)[1];
            $extraFieldLength = unpack('v', $cd, $i+30)[1];
            $commentLength = unpack('v', $cd, $i+32)[1];

            $files[$index] = [
                'index' => $index,
                'crc' => unpack('V', $cd, $i+16)[1],
                'comp_size' => unpack('V', $cd, $i+20)[1],
                'size' => unpack('V', $cd, $i+24)[1],
                'mtime' => unpack('V', $cd, $i+12)[1], // @fixme
                'offset' => unpack('V', $cd, $i+42)[1],
                'name' => substr($cd, $i+46, $fileNameLength),
                'extra' => substr($cd, $i+46+$fileNameLength, $extraFieldLength),
                'comment' => substr($cd, $i+46+$fileNameLength+$extraFieldLength, $commentLength),
                'compression_method' => unpack('v', $cd[$i+10].$cd[$i+11])[1]
            ];
            if (0 !== $index) {
                $files[$index-1]['end_offset'] = $files[$index]['offset'];
            }

            $i += 46+$fileNameLength+$extraFieldLength+$commentLength;
            $index++;
        }

        if (0 !== count($files)) {
            $files[$index-1]['end_offset'] = $cd_start;
        }

        foreach ($files as $index => $fileOptions) {
            $file = new File($this->input, $fileOptions);
            $this->filesByIndex[$file->getIndex()] = $this->filesByName[$file->getName()] = $file;
        }
    }

    public function getFileNames(): array
    {
        return array_keys($this->filesByName);
    }

    public function getFiles(): array
    {
        return $this->filesByIndex;
    }

    public function getFile(string $name): File
    {
        if (array_key_exists($name, $this->filesByName)) {
            return $this->filesByName[$name];
        }

        throw new \InvalidArgumentException(sprintf('File "%s" does not exists', $name));
    }
}
