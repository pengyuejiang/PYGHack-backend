<?php
namespace App\Http\Controllers;

use App\Helpers;
use App\Helpers\ErrorHelpers;
use Illuminate\Http\Request;
use App\Models\SurveyTemplate;
use Carbon\Carbon;

class SurveyTemplateController extends Controller
{
    public function register(Request $request)
    {
        $content = $this->getContent($request);

        $this->validator($content, [
            'body' => 'array'
        ]);

        $user = $request->user();

        if ($user->data['type'] != config('constant')['user_types']['authority']) {
            app(ErrorHelpers::class)->throw(1004);
        }

        return app(SurveyTemplate::class)->add([
            'owner_id' => $user->id,
            'body'=> Helpers::only($content, ['body'])
        ]);
    }

    public function view(Request $request, $id)
    {
        return app(SurveyTemplate::class)->view($id);
    }

    public function index(Request $request)
    {
        $content = $this->getContent($request);

        $this->validator($content, [
            'page' => 'required|integer',
            'per-page' => 'integer',
            'order' => 'string',
            'by' => 'string|in:desc,asc',
            'owner_id' => 'array'
        ]);

        list($page, $perPage, $order, $by) = $this->getIndexAttributes($content);

        list($templates, $count) = app(SurveyTemplate::class)->index(
            Helpers::only($content, ['owner_id']),
            $page,
            $perPage,
            $order,
            $by
        );

        return [
            'survey_templates' => $templates,
            'count' => $count
        ];
    }

    public function put(Request $request, $id)
    {
        $content = $this->getContent($request);

        $this->validator($content, [
            'body'=>'array'
        ]);

        $user = $request->user();

        if ($user->data['type'] != config('constant')['user_types']['authority']) {
            app(ErrorHelpers::class)->throw(1004);
        }

        return app(SurveyTemplate::class)->put($id, Helpers::only($content, ['body']));
    }

    public function delete(Request $request, $id)
    {
        $user = $request->user();

        if ($user->data['type'] != config('constant')['user_types']['authority']) {
            app(ErrorHelpers::class)->throw(1004);
        }

        return app(SurveyTemplate::class)->del($id);
    }
}
