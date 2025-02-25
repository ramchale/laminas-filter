<?php

declare(strict_types=1);

namespace LaminasTest\Filter\File;

use Laminas\Filter\File\RenameList;
use PHPUnit\Framework\TestCase;

use function copy;
use function file_exists;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

class RenameListTest extends TestCase
{
    private const TEST_FILE_NAME = 'test_file.txt';

    private static ?string $tmpPath             = null;
    private static ?string $tmpSubDirectoryPath = null;

    private static function getTempPath(): string
    {
        if (self::$tmpPath === null) {
            self::$tmpPath = sys_get_temp_dir() . '/' . uniqid('laminasfilter');
            mkdir(self::$tmpPath, 0775, true);
        }

        return self::$tmpPath;
    }

    private static function getTempSubDirectory(): string
    {
        if (self::$tmpSubDirectoryPath === null) {
            self::$tmpSubDirectoryPath = self::getTempPath() . '/test_dir';
            mkdir(self::$tmpSubDirectoryPath, 0775, true);
        }

        return self::$tmpSubDirectoryPath;
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$tmpSubDirectoryPath !== null) {
            rmdir(self::$tmpSubDirectoryPath);
        }

        if (self::$tmpPath !== null) {
            rmdir(self::$tmpPath);
        }
    }

    private static function createSourceFile(): void
    {
        copy(__DIR__ . '/../_files/testfile.txt', self::getTempPath() . '/' . self::TEST_FILE_NAME);
    }

    private static function cleanupSourceFile(): void
    {
        $fileToRemove = self::getTempPath() . '/' . self::TEST_FILE_NAME;
        if (file_exists($fileToRemove)) {
            unlink($fileToRemove);
        }
    }

    public function tearDown(): void
    {
        self::cleanupSourceFile();
    }

    public function testExample(): void
    {
        $oldFilePath  = self::getTempPath() . '/' . self::TEST_FILE_NAME;
        $newFileName  = 'new_file.xml';
        $newDirectory = self::getTempSubDirectory();

        self::createSourceFile();

        $filter = new RenameList([
            [
                'match'     => self::getTempPath() . '/no_match.txt',
                'rename_to' => 'failed_if_this.txt',
            ],
            [
                'match'            => $oldFilePath,
                'target_directory' => $newDirectory,
                'rename_to'        => $newFileName,
            ],
        ]);

        $input                = $oldFilePath;
        $expectedFilterResult = $newDirectory . '/' . $newFileName;

        try {
            self::assertSame($expectedFilterResult, $filter->filter($input));
            self::assertFileExists($expectedFilterResult);
        } finally {
            if (file_exists($expectedFilterResult)) {
                unlink($expectedFilterResult);
            }
        }

        $this->assertTrue(true);
    }
}
