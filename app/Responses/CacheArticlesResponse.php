<?php

namespace App\Responses;

class CacheArticlesResponse
{
    public const NEWS_API_TYPE = 'news-api';
    public const NEW_YORK_TIMES_API_TYPE = 'NYT-api';
    public const GUARDIAN_API_TYPE = 'guardian-api';

    public const DATE_FORMAT = 'Y-m-d';

    public function getResponse(array $response, string $type, string $topic = ''): array
    {
        switch ($type) {
            case self::NEWS_API_TYPE:
                return $this->formatResponse(
                    $response['body']['articles'] ?? [],
                    $topic,
                    'Open News',
                    fn($item) => [
                        'source' => $item['source']['name'] ?? '',
                        'title' => $item['title'] ?? '',
                        'description' => $item['description'] ?? '',
                        'url' => $item['url'] ?? '',
                        'imageSrc' => $item['urlToImage'] ?? '',
                        'publishedAt' => $item['publishedAt'] ?? '',
                        'content' => $item['content'] ?? '',
                        'author' => $item['author'] ?? $item['source']['name'] ?? 'Unknown'
                    ]
                );
            case self::NEW_YORK_TIMES_API_TYPE:
//                dd(json_encode($response['body']['response']['docs'][0]));
                return $this->formatResponse(
                    $response['body']['response']['docs'] ?? [],
                    $topic,
                    'The New York Times',
                    fn($item) => [
                        'source' => $item['source'] ?? '',
                        'title' => $item['abstract'] ?? '',
                        'description' =>  $item['lead_paragraph'] ?? '',
                        'url' => $item['web_url'] ?? '',
                        'imageSrc' => 'https://www.nytimes.com/' . ($item['multimedia']['0']['url'] ?? ''),
                        'publishedAt' => $item['pub_date'] ?? '',
                        'content' => $item['fields']['bodyText'] ?? '',
                        'author' => $item['byline']['original'] ?? $item['source'] ?? 'Unknown'
                    ]
                );

            case self::GUARDIAN_API_TYPE:
                return $this->formatResponse(
                    $response['body']['response']['results'] ?? [],
                    $topic,
                    'Guardians',
                    fn($item) => [
                        'source' => $item['pillarName'] ?? '',
                        'title' => $item['webTitle'] ?? '',
                        'description' =>  $item['fields']['bodyText'] ?? '',
                        'url' => $item['webUrl'] ?? '',
                        'imageSrc' => $item['fields']['thumbnail'] ?? '',
                        'publishedAt' => $item['fields']['lastModified'] ?? '',
                        'content' => $item['fields']['bodyText'] ?? '',
                        'author' => $item['fields']['byline'] ?? $item['pillarName'] ?? 'Unknown'
                    ]
                );
            default:
                return [];
        }
    }

    private function formatResponse(array $items, string $topic, string $apiName, callable $mapItem): array
    {
        $result = [];
        $authors = [];
        $sources = [];

        collect($items)->each(function ($item) use (&$result, &$authors, &$sources, $topic, $apiName, $mapItem) {
            $mappedItem = $mapItem($item);
            $author = $mappedItem['author'];

            // Organize result by topic and author
            $result[$topic][$author][] = array_merge($mappedItem, [
                'publishedAt' => (new \DateTime($mappedItem['publishedAt']))->format(self::DATE_FORMAT),
                'topic' => $topic,
                'whichApi' => $apiName,
            ]);

            // Track authors and sources
            $sources[$mappedItem['source']] = ($sources[$mappedItem['source']] ?? 0) + 1;
            $authors[$author] = ($authors[$author] ?? 0) + 1;
        });

        return [
            'results' => $result,
            'authors' => $authors,
            'sources' => $sources,
        ];
    }

    private function getNYTApiResponse(array $response, string $topic): array
    {
        // NYT API response formatting logic can go here
        return []; // Placeholder for now
    }
}
