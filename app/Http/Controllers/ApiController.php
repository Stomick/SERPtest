<?php

namespace App\Http\Controllers;

use App\RestClient;
use App\RestClientException;
use App\Tasks;
use App\UserRequest;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    private $respClient;

    function __construct()
    {
        try {
            $this->respClient = new RestClient('https://api.dataforseo.com/', null, 'challenger16@rankactive.info', 'Pt82h5yFh35tjF23fgF25');
        } catch (RestClientException $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

    }

    public function getAllTasks()
    {

        try {
            return response()->json(UserRequest::all());
            //get tasks one by one
        } catch (RestClientException $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

    }

    public function createRequest(Request $request)
    {
        $body = $request->post('Req');
        $my_unq_id = mt_rand(0, 30000000); //your unique ID. we will return it with all results. you can set your database ID, string, etc.
        $post_array[$my_unq_id] = [
            "se_language" => "English"
        ];
        if (is_array($body) || key_exists('keyword', $body) || key_exists('region', $body) || key_exists('search', $body)) {
            foreach ($body as $k => $v) {
                if ($v == null) {
                    return response()->json(['error' => "Empty $k"]);
                } else {
                    switch ($k) {
                        case 'keyword':
                            $post_array[$my_unq_id]['key'] = mb_convert_encoding(trim(strip_tags($v)), "UTF-8");
                            break;
                        case 'region':
                            $post_array[$my_unq_id]['loc_name_canonical'] = trim(strip_tags($v));
                            break;
                        case 'search':
                            $post_array[$my_unq_id]['se_name'] = trim(strip_tags($v));
                            break;
                    }
                }
            }
            try {
                // POST /v2/live/srp_tasks_post/$data
                // $tasks_data must by array with key 'data'
                $result = $this->respClient->post('v2/srp_tasks_post', ['data' => $post_array]);
                if ($result['status'] == 'ok') {
                    $reqv = new UserRequest();
                    $reqv->fill($result['results'][$my_unq_id]);
                    $reqv->save();
                }
                return response()->json(['success' => 'ok', $result]);
                //do something with post results
            } catch (RestClientException $e) {
                return response()->json(['error' => $e->getMessage()]);
            }
        }

    }

    public function getRequestInfo(Request $request)
    {
        $get = $request->query();
        if ($get['id'] && $get['req_id']) {
            $serp_result = $this->respClient->get('v2/srp_tasks_get/' . $get['id']);
            if ($serp_result['status'] == 'ok' && isset($serp_result['results']['organic'])) {
                Tasks::query()->where('req_id', '=', $get['req_id'])->delete();
                foreach ($serp_result['results']['organic'] as $k => $v) {
                    $task = new Tasks();
                    $task->fill($v);
                    $task->req_id = $get['req_id'];
                    $task->save();
                }
                if ($ur = UserRequest::query()->find($get['req_id'])->getModel()) {
                    $ur->filled = 1;
                    $ur->update();
                }
            }
            return $task;
        }
        return response()->json(['error' => 'Not set id task']);
    }

    public function getTaskInfo(Request $request)
    {
        if ($get = $request->get('id')) {
                return response()->json(Tasks::query()->where('task_id', '=', $get)->get());
        }
        return response()->json(['error' => 'Not set id task']);
    }

}
