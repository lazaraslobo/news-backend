<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Services\Internal\GetArticlesService;
use App\Services\Views\DashboardViewService;
use Illuminate\Http\Request;

class ArticlesController extends Controller
{
    public function getAllArticles(
        Request $request,
        DashboardViewService $service
    ){
        return JsonResponseHelper::success($service->process());
    }
}
