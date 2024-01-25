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

namespace Sigwin\YASSG\Context;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 */
final readonly class LocaleContext
{
    public const LOCALE = 'sigwin_locale';
    public const LOCALE_FALLBACK = 'sigwin_locale_fallback';

    public function __construct(private RequestStack $requestStack, private TranslatorInterface $translator)
    {
    }

    public function getLocale(): array
    {
        $request = $this->requestStack->getMainRequest();
        if ($request === null) {
            if ($this->translator instanceof \Symfony\Component\Translation\Translator === false) {
                // TODO: remove with Symfony 6.x being lowest
                throw new \LogicException();
            }
            $locale = $fallbackLocale = $this->translator->getLocale();
        } else {
            $locale = $request->getLocale();
            $fallbackLocale = $request->getDefaultLocale();
        }

        return [self::LOCALE => $locale, self::LOCALE_FALLBACK => $fallbackLocale];
    }
}
