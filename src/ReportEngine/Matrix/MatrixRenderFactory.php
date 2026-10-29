<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Matrix;

use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixLayoutDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixRenderDto;
use Julitom3\Cubes\ReportEngine\Matrix\Walkers\BodyWalker;
use Julitom3\Cubes\ReportEngine\Matrix\Walkers\HeaderWalker;

final readonly class MatrixRenderFactory
{
    public function __construct(
        private HeaderWalker $headerWalker,
        private BodyWalker $bodyWalker,
    ) {}

    public function create(MatrixLayoutDto $layout): MatrixRenderDto
    {
        return new MatrixRenderDto(
            headers: $this->headerWalker->walk($layout),
            body: $this->bodyWalker->walk($layout),
        );
    }
}
