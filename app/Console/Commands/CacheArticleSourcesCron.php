<?php

namespace App\Console\Commands;

use App\Factories\CacheArticleSourcesFactory;
use App\Helpers\RedisHelper;
use App\Services\Internal\CacheAllArticleSourcesService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CacheArticleSourcesCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cached-articles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $articlesData = RedisHelper::get(CacheAllArticleSourcesService::ALL_SOURCES_DATA_MAPPED_CACHE_PREFIX) ?? [];
        if(empty($articlesData)) {
            \Log::info("Cache all sources CRON started at " . Carbon::now()->toDateTimeString());
            $cacheService = CacheArticleSourcesFactory::create();
            $result = $cacheService->process();
            \Log::info("Cache all sources CRON finished at " . Carbon::now()->toDateTimeString());
        }
    }
}
