<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Matrix;

use Illuminate\Contracts\Translation\Translator;

/**
 * The label a dimension value shows in the matrix; empty values get the translated "(empty)" label.
 */
final readonly class MatrixLabelFormatter
{
    public function __construct(private Translator $translator) {}

    public function format(mixed $value): string
    {
        if ($value === null || $value === '') {
            return (string) $this->translator->get('cubes::messages.empty');
        }

        return is_scalar($value) ? (string) $value : (string) json_encode($value);
    }
}
