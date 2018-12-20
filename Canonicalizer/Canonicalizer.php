<?php

declare(strict_types=1);

namespace Pintushi\Bundle\UserBundle\Canonicalizer;

final class Canonicalizer implements CanonicalizerInterface
{
    public function canonicalize(?string $string): ?string
    {
        return null === $string ? null : mb_convert_case($string, MB_CASE_LOWER, mb_detect_encoding($string));
    }
}
