<?php

declare(strict_types=1);

/*
 * This file is part of the yassg project.
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
final class LocaleContext
{
    public const LOCALE = 'sigwin_locale';
    public const LOCALE_FALLBACK = 'sigwin_locale_fallback';

    private RequestStack $requestStack;
    private TranslatorInterface $translator;

    public function __construct(RequestStack $requestStack, TranslatorInterface $translator)
    {
        $this->requestStack = $requestStack;
        $this->translator = $translator;
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
