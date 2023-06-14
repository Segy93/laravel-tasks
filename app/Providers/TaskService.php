<?php


namespace App\Providers;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service for managing tasks
 */
class TaskService {
    /** CREATE */


    /**
     * Task creation
     *
     * @param string   $name         Name of the task
     * @param int|null $project_id   Project id
     * @return int
     */
    public static function createTask(string $name, ?int $project_id = null): int {
        $task = new Task();
        $task->name = $name;
        $task->priority = Task::max('id') + 1;
        $task->project_id = $project_id;

        $task->save();

        return $task->id;
    }












    /** READ */



    /**
     * Getting tasks (all or for specific project)
     * depending on function parameter
     *
     * @param integer|null $project_id
     * @return Collection
     */
    public static function getTasks(?int $project_id = null): Collection {
        $tasks = Task::orderBy('priority');
        if ($project_id) {
            $tasks->where('project_id', $project_id);
        }

        return $tasks->get();
    }

    /**
     * Finding task with specific id
     *
     * @param integer $id    Task id
     * @return Task
     */
    public static function getTaskById(int $id): Task {
        return Task::find($id);
    }

    /**
     * Returning task by priority
     *
     * @param integer $priority
     * @return Task
     */
    private static function getTaskByPriority(int $priority): Task {
        return Task::where('priority', $priority)->first();
    }










    /** UPDATE */


    /**
     * Updating task
     *
     * @param integer $id                     Id of task which data is updating
     * @param array   $updates                Array of paramaters
     *  param string  $updates['name']        Name
     *  param int     $updates['project_id']  Project id
     * @return void
     */
    public static function updateTask(int $id, array $updates): void {
        $task = self::getTaskById($id);

        if (array_key_exists('name', $updates)) {
            $task->name = $updates['name'];
        }

        if (array_key_exists('project_id', $updates)) {
            $task->project_id = $updates['project_id'];
        }

        $task->save();
    }

    /**
     * Changing order of task priorities
     *
     * @param integer $order_old
     * @param integer $priority
     * @return bool
     */
    public static function reorderTasks(int $order_old, int $priority): bool {
        $return = false;
        $task = self::getTaskByPriority($order_old);
        if ($task->priority !== $priority) {
            $increment  = $task->priority > $priority ? 1 : -1;
            $dir        = $task->priority > $priority ? 'desc' : 'asc';
            $range      = $task->priority > $priority ? [$priority, $task->priority] : [$task->priority + 1, $priority];

            $task->priority = 0;
            $task->save();

            $tasks = Task::whereBetween('priority', $range)
                ->orderBy('priority', $dir)
                ->get()
            ;

            foreach ($tasks as $i) {
                $i->priority += $increment;
                $i->save();
            }

            $task->priority = $priority;
            $task->save();
            $return = true;
        }
        return $return;
    }





    /** DELETE */



    /**
     * Deleting specific task by id
     *
     * @param integer $id   Task id
     * @return void
     */
    public static function deleteTaskById(int $id): void {
        $task = self::getTaskById($id);
        $task->delete();
    }
}
