<?php

declare(strict_types=1);

namespace Laminas\Filter\File;

use Laminas\Filter\FilterInterface;

use function file_exists;
use function is_string;

/**
 * @psalm-type OptionsSet = array{
 *     match?: non-empty-string,
 *     target_directory?: non-empty-string,
 *     rename_to?: string,
 *     overwrite?: bool,
 *     randomize?: bool
 * }
 * @psalm-type Options = list<OptionsSet>
 * @implements FilterInterface<string>
 */
class RenameList implements FilterInterface
{
    /** @var Rename[] */
    private array $filters;

    /**
     * @param Options $options
     */
    public function __construct(array $options = [])
    {
        $this->filters = [];

        foreach ($options as $option) {
            $this->filters[] = new Rename($option);
        }
    }

    public function filter(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        if (! file_exists($value)) {
            return $value;
        }

        foreach ($this->filters as $filter) {
            if ($filter->matches($value)) {
                return $filter->filter($value);
            }
        }

        return $value;
    }

    public function __invoke(mixed $value): mixed
    {
        return $this->filter($value);
    }
}
