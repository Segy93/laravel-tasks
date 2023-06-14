<?php


namespace App\Providers;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service for managing projects
 */
class ProjectService {
    /** CREATE */













    /** READ */



    /**
     * Getting all projects
     *
     * @return Collection
     */
    public static function getProjects(): Collection {
        return Project::all();
    }










    /** UPDATE */












    /** DELETE */

}
