<?php

namespace App\Services\Internal;

use App\Helpers\RedisHelper;
use App\Responses\CacheArticlesResponse;
use App\Services\External\HttpService;

class CacheAllArticleSourcesService
{
    public const ALL_AUTHORS_CACHE_PREFIX = 'cache-article-topic-authors:';
    public const ALL_SOURCES_DATA_MAPPED_CACHE_PREFIX = 'cache-mapped-sources-data';
    public const ALL_SOURCES_NAMES_CACHE_PREFIX = 'cache-mapped-sources-names';

    private array $topics;

    private HttpService $httpService;

    public function __construct(array $topics)
    {
        $this->topics = $topics;
        $this->httpService = new HttpService();
    }

    public function process()
    {
        $response = [];
        $authors = [];
        $sources = [];

        foreach ($this->getSourceMappings() as $eachSource) {
            foreach ($this->topics as $eachTopic) {  // Now using injected topics
                $eachPageCount = 1;
                for ($i = 0; $i < env('ARTICLE_TOTAL_EACH_PAGES_TO_CACHE', 10); $i++) {
                    $data = $this->httpService->get($eachSource['url'], [
                        $eachSource['queryIdentifier'] => $eachTopic,
                        $eachSource['keyIdentifier'] => $eachSource['apiKey'],
                        $eachSource['whichPageIdentifier'] => $eachPageCount,
                        ...($eachSource['params'] ?? [])
                    ]) ?? [];

                    // Transform data
                    $transformedData = (new CacheArticlesResponse())->getResponse($data, $eachSource['apiType'], $eachTopic);
                    $authors = [...$authors, ...$transformedData["authors"]];
                    $sources = [...$sources, ...$transformedData["sources"]];

                    // Merge mapped data
                    foreach ($transformedData["results"] as $items) {
                        if (!isset($response[$eachTopic])) {
                            $response[$eachTopic] = [];
                        }

                        $response[$eachTopic] = array_merge($response[$eachTopic], $items);
                    }

                    $eachPageCount++;
                }
            }
        }

        if (!empty($sources) && !empty($response) && !empty($authors)) {
            RedisHelper::set(self::ALL_SOURCES_DATA_MAPPED_CACHE_PREFIX, $response, env("TWENTY_FOUR_HOURS_TTL_SECONDS"));
            RedisHelper::set(self::ALL_SOURCES_NAMES_CACHE_PREFIX, $sources, env("TWENTY_FOUR_HOURS_TTL_SECONDS"));
            RedisHelper::set(self::ALL_AUTHORS_CACHE_PREFIX, $authors, env("TWENTY_FOUR_HOURS_TTL_SECONDS"));
        }

        return $response;
    }

    private function getSourceMappings()
    {

        return [
            "news-api" => [
                "url" => "https://newsapi.org/v2/everything",
                "label" => "News API",
                "apiKey" => env("NEWS_API_KEY"),
                "queryIdentifier" => "q",
                "keyIdentifier" => "apiKey",
                "whichPageIdentifier" => 'page',
                "apiType" => CacheArticlesResponse::NEWS_API_TYPE
            ],
            "the-guardian-api" => [
                "url" => "https://content.guardianapis.com/search",
                "label" => "Guardian API",
                "apiKey" => env("THE_GUARDIAN_NEWS_API_KEY"),
                "queryIdentifier" => "q",
                "keyIdentifier" => "api-key",
                "whichPageIdentifier" => 'page',
                "apiType" => CacheArticlesResponse::GUARDIAN_API_TYPE,
                "params" => [
                    "show-fields" => "all"
                ]
            ],
            "NYT-api" => [
                "url" => "https://api.nytimes.com/svc/search/v2/articlesearch.json",
                "label" => "New York Time API",
                "apiKey" => env("NEW_YORK_TIMES_API_KEY"),
                "queryIdentifier" => "q",
                "keyIdentifier" => "api-key",
                "whichPageIdentifier" => 'page',
                "apiType" => CacheArticlesResponse::NEW_YORK_TIMES_API_TYPE,
                "params" => [
                ]
            ],
        ];
    }
}
