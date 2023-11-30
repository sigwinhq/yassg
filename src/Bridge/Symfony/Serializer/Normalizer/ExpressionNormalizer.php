<?php

declare(strict_types=1);

/*
 * This file is part of the Sigwin Yassg project.
 *
 * (c) sigwin.hr
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sigwin\YASSG\Bridge\Symfony\Serializer\Normalizer;

use Sigwin\YASSG\DatabaseProvider;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class ExpressionNormalizer implements DenormalizerInterface
{
    private ExpressionLanguage $expressionLanguage;
    private DatabaseProvider $databaseProvider;

    public function __construct(ExpressionLanguage $expressionLanguage, DatabaseProvider $databaseProvider)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->databaseProvider = $databaseProvider;
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): object
    {
        /** @var string $data */
        $value = $this->expressionLanguage->evaluate(mb_substr($data, 2), [
            'provider' => $this->databaseProvider,
        ]);

        /**
         * @var class-string $type
         *
         * @phpstan-ignore-next-line
         */
        if (is_a($value, $type, false) === false) {
            throw new \LogicException(sprintf('Invalid value denormalized, %1$s expected, %2$s received', $type, get_debug_type($value)));
        }

        return $value;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \is_string($data) && str_starts_with($data, '@=');
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['object' => true];
    }
}
