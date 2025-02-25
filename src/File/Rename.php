<?php

declare(strict_types=1);

namespace Laminas\Filter\File;

use Laminas\Filter\Exception;
use Laminas\Filter\FilterInterface;

use function file_exists;
use function fnmatch;
use function is_dir;
use function is_string;
use function is_writable;
use function pathinfo;
use function realpath;
use function rename;
use function sprintf;
use function uniqid;
use function unlink;

/**
 * @psalm-type Options = array{
 *     match?: non-empty-string,
 *     target_directory?: non-empty-string,
 *     rename_to?: string,
 *     overwrite?: bool,
 *     randomize?: bool
 * }
 * @implements FilterInterface<string>
 */
final class Rename implements FilterInterface
{
    private string $match;
    private string $target;
    private string $renameTo;
    private bool $overwrite;
    private bool $randomize;

    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $this->match     = $options['match'] ?? '*';
        $this->target    = $options['target_directory'] ?? '*';
        $this->renameTo  = $options['rename_to'] ?? '*';
        $this->overwrite = $options['overwrite'] ?? false;
        $this->randomize = $options['randomize'] ?? false;

        if ($this->target !== '*') {
            if (! is_dir($this->target)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'The target directory "%s" does not exist',
                    $this->target
                ));
            }

            if (! is_writable($this->target)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'The target directory "%s" is not writable',
                    $this->target
                ));
            }
        }
    }

    /**
     * @throws Exception\InvalidArgumentException If the target file already exists.
     */
    private function renameFile(string $sourceFilePath): string
    {
        $file = $this->getFileName($sourceFilePath);

        if ($file === $sourceFilePath) {
            return $file;
        }

        if ($this->overwrite && file_exists($file)) {
            unlink($file);
        }

        if (file_exists($file)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '"File "%s" could not be renamed to "%s"; target file already exists',
                $sourceFilePath,
                realpath($file)
            ));
        }

        $result = rename($sourceFilePath, $file);

        if ($result !== true) {
            throw new Exception\RuntimeException(
                sprintf(
                    "File '%s' could not be renamed. "
                    . "An error occurred while processing the file.",
                    $sourceFilePath
                )
            );
        }

        return $file;
    }

    /**
     * @throws Exception\RuntimeException
     */
    public function filter(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        if (! file_exists($value)) {
            return $value;
        }

        if (! $this->matches($value)) {
            return $value;
        }

        return $this->renameFile($value);
    }

    public function matches(string $file): bool
    {
        return fnmatch($this->match, $file);
    }

    private function getFileName(string $file): string
    {
        $fileInfo = pathinfo($file);

        $targetName = $this->renameTo === '*' ? $fileInfo['basename'] : $this->renameTo;
        $targetDir  = $this->target === '*' ? $fileInfo['dirname'] : $this->target;

        $target = $targetDir . '/' . $targetName;

        if ($this->randomize) {
            $info      = pathinfo($target);
            $newTarget = $info['dirname'] . '/' . $info['filename'] . uniqid('_');
            if (isset($info['extension'])) {
                $newTarget .= '.' . $info['extension'];
            }
            $target = $newTarget;
        }

        return $target;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
