<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\Stdlib\ErrorHandler;

use function array_pop;
use function explode;
use function getcwd;
use function implode;
use function is_string;
use function preg_match;
use function preg_replace;
use function realpath;
use function str_starts_with;
use function stripos;
use function substr;

use const DIRECTORY_SEPARATOR;
use const PHP_OS;

/**
 * @psalm-type Options = array{
 *     exists?: bool,
 * }
 * @implements FilterInterface<string|array<array-key, string|mixed>>
 */
final class RealPath implements FilterInterface
{
    private readonly bool $pathMustExist;

    /** @param Options $options */
    public function __construct(array $options = [])
    {
        $this->pathMustExist = $options['exists'] ?? true;
    }

    public function filter(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }
        $path = (string) $value;

        if ($this->pathMustExist) {
            return realpath($path);
        }

        ErrorHandler::start();
        $realpath = realpath($path);
        ErrorHandler::stop();
        if ($realpath !== false) {
            return $realpath;
        }

        $drive = '';
        if (stripos(PHP_OS, 'WIN') === 0) {
            $path = preg_replace('/[\\\\\/]/', DIRECTORY_SEPARATOR, $path);
            if (preg_match('/([a-zA-Z]\:)(.*)/', $path, $matches)) {
                [, $drive, $path] = $matches;
            } else {
                $cwd   = getcwd();
                $drive = substr($cwd, 0, 2);
                if (! str_starts_with($path, DIRECTORY_SEPARATOR)) {
                    $path = substr($cwd, 3) . DIRECTORY_SEPARATOR . $path;
                }
            }
        } elseif (! str_starts_with($path, DIRECTORY_SEPARATOR)) {
            $path = getcwd() . DIRECTORY_SEPARATOR . $path;
        }

        $stack = [];
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        foreach ($parts as $dir) {
            if ($dir !== '' && $dir !== '.') {
                if ($dir === '..') {
                    array_pop($stack);
                } else {
                    $stack[] = $dir;
                }
            }
        }

        return $drive . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $stack);
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
