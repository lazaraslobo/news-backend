<?php

namespace App\Factories;

use App\Services\Internal\CacheAllArticleSourcesService;

class CacheArticleSourcesFactory
{
    public const TOPICS = [
        "politics", "sports", "technology", "AI", "stock market"
    ];

    public static function create(): CacheAllArticleSourcesService
    {

        return new CacheAllArticleSourcesService(self::TOPICS);
    }
}
