<?php

namespace App\Http\Controllers;

use App\Providers\ProjectService;
use App\Providers\TaskService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller {

    /** CREATE*/



    /**
     * Creating task
     *
     * @param Request $request     Request with form data
     * @return RedirectResponse    Redirection to task list
     */
    public function taskCreatePost(Request $request): RedirectResponse {
        $name = $request->input('name');
        $project_value = $request->input('project_id');
        $project_id = !empty($project_value) ? (int)$project_value : null;

        TaskService::createTask(
            $name,
            $project_id
        );

        return redirect()->route('taskList');
    }

    /**
     * Form for creating task
     *
     * @return View          Returning blade file
     */
    public function taskCreate(): View {
        return view('taskCreate', [
            'task'     => null,
            'projects' => ProjectService::getProjects(),
        ]);
    }










    /** READ */



    /**
     * Fetching tasks
     *
     * @param array $params                  Array of parameters
     *  param array $params['project_id']    Project id
     * @return Collection
     */
    public function fetchData(array $params): Collection {
        $project_id = !empty($params['project_id']) ? (int)($params['project_id']) : null;

        return TaskService::getTasks($project_id);
    }

    /**
     * Listing task list
     *
     * @return View         Returning blade file
     */
    public function taskList(): View {
        return view('tasks', [
            'tasks'    => TaskService::getTasks(),
            'projects' => ProjectService::getProjects(),
        ]);
    }










    /** UPDATE */


    /**
     * Updating task
     *
     * @param Request $request     Request with form data
     * @return RedirectResponse    Redirection to task list
     */
    public function taskUpdate(Request $request): RedirectResponse {
        $id            = (int)$request->input('task_id');
        $name          = $request->input('name');
        $project_value = $request->input('project_id');
        $project_id    = !empty($project_value) ? (int)$project_value : null;

        $updates       = [
            'name'       => $name,
            'project_id' => $project_id,
        ];

        TaskService::updateTask(
            $id,
            $updates
        );

        return redirect()->route('taskList');
    }

    /**
     * Changing order of tasks
     *
     * @param array $params
     *  param int   $params['order_old']
     *  param int   $params['order_new']
     *
     * @return bool
     */
    public function changeOrder(array $params): bool {
        $order_old          = intval($params['order_old']);
        $order_new          = intval($params['order_new']);
        return TaskService::reorderTasks($order_old, $order_new);
    }

    /**
     * Form for editing task
     *
     * @param integer $task_id          Task id
     * @return View                     Returning blade file
     */
    public function taskEdit(int $task_id): View {
        $task = TaskService::getTaskById($task_id);
        return view('taskCreate',
            [
                'task' => $task,
                'projects' => ProjectService::getProjects(),
            ]
        );
    }











    /** DELETE */



    /**
     * Deleting of task
     *
     * @param array $params              Array with parameters
     *  param array $params['task_id']   Task id
     * @return void
     */
    public function deleteTask(array $params): void {
        $task_id = (int)$params['task_id'];

        TaskService::deleteTaskById($task_id);
    }
}