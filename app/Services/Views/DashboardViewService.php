<?php

namespace App\Services\Views;

use App\Factories\CacheArticleSourcesFactory;
use App\Helpers\RedisHelper;
use App\Services\Internal\CacheAllArticleSourcesService;

class DashboardViewService
{
    public function process(){
        $articlesData = RedisHelper::get(CacheAllArticleSourcesService::ALL_SOURCES_DATA_MAPPED_CACHE_PREFIX) ?? [];
        $mappedAuthors = RedisHelper::get(CacheAllArticleSourcesService::ALL_AUTHORS_CACHE_PREFIX) ?? [];
        $mappedSourceNames = RedisHelper::get(CacheAllArticleSourcesService::ALL_SOURCES_NAMES_CACHE_PREFIX) ?? [];

        return [
            'topics' => CacheArticleSourcesFactory::TOPICS,
            'articles' => $articlesData,
            'authors' => $mappedAuthors,
            'sources' => $mappedSourceNames
        ];
    }
}
