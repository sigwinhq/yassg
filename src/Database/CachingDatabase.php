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

namespace Sigwin\YASSG\Database;

use Psr\Cache\CacheItemPoolInterface;
use Sigwin\YASSG\Collection;
use Sigwin\YASSG\Context\LocaleContext;
use Sigwin\YASSG\Database;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class CachingDatabase implements Database
{
    use DatabaseTrait;

    private string $name;
    private Database $database;
    private CacheItemPoolInterface $cacheItemPool;
    private LocaleContext $localeContext;

    public function __construct(string $name, Database $database, CacheItemPoolInterface $cacheItemPool, LocaleContext $localeContext, ExpressionLanguage $expressionLanguage, array $names)
    {
        $this->name = $name;
        $this->database = $database;
        $this->cacheItemPool = $cacheItemPool;
        $this->localeContext = $localeContext;
        $this->expressionLanguage = $expressionLanguage;
        $this->names = $names;
    }

    public function count(?string $condition = null): int
    {
        $locale = $this->localeContext->getLocale()[LocaleContext::LOCALE];
        $cacheKey = $this->name.'_count_'.$locale.'_'.$condition;

        $item = $this->cacheItemPool->getItem($cacheKey);
        if ($item->isHit()) {
            /** @var int $count */
            $count = $item->get();

            return $count;
        }

        $count = $this->database->count($condition);
        $item->set($count);
        $this->cacheItemPool->save($item);

        return $count;
    }

    public function findAll(?string $condition = null, ?array $sort = null, ?int $limit = null, int $offset = 0, ?string $select = null): Collection
    {
        $locale = $this->localeContext->getLocale()[LocaleContext::LOCALE];
        $cacheKey = $this->name.'_find_'.$locale.'_'.$condition.'_'.json_encode($sort).'_'.$limit.'_'.$offset.'_'.$select;

        $item = $this->cacheItemPool->getItem($cacheKey);
        if ($item->isHit()) {
            /** @var array<string> $ids */
            $ids = $item->get();

            $storage = [];
            foreach ($ids as $id) {
                $storage[$id] = $this->get($id);
            }

            return $this->createCollection($storage);
        }

        $collection = $this->database->findAll($condition, $sort, $limit, $offset, $select);

        $ids = [];
        $cloned = clone $collection;
        foreach ($cloned as $id => $entity) {
            $ids[] = $id;
        }
        $item->set($ids);
        $this->cacheItemPool->save($item);

        return $collection;
    }

    public function get(string $id): object
    {
        return $this->database->get($id);
    }

    public function has(string $id): bool
    {
        return $this->database->has($id);
    }
}
