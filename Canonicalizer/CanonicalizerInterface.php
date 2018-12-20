<?php

declare(strict_types=1);

namespace Pintushi\Bundle\UserBundle\Canonicalizer;

interface CanonicalizerInterface
{
    public function canonicalize(?string $string): ?string;
}
