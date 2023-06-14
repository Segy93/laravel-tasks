<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta name="csrf-token" id = "csrf-token" content="{{ csrf_token() }}" />
    </head>
    <body>
        <form
            action = "{{ $task ? route('taskUpdate') : route('taskCreatePost') }}"
            class  = "task_create__form"
            id     = "task_create__form"
            method = "post"
        >
            @csrf <!-- {{ csrf_field() }} -->
            @if ($task)
                <input type = "hidden" name = "task_id" value = "{{ $task->id }}">
            @endif
            <label
                class = "task_create__form_label"
                for   = "task_create__form_input_name"
            >
                Name:
            </label>
            <input
                class     = "task_create__form_input task_create__form_input_name"
                id        = "task_create__form_input_name"
                maxlength = "255"
                minlenght = "3"
                name      = "name"
                required
                type      = "text"
                value     = "{{ $task ? $task->name : '' }}"
            />
            <select
                class = "task_create__form_list"
                id    = "task_create__form_list"
                name  = "project_id"
            >
                <option value = "">No project</option>
                @foreach ($projects as $project)
                    <option
                        @if ($task && $task->project_id === $project->id)
                            selected
                        @endif
                        value = "{{ $project->id }}"
                    >
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
        </form>
        <input
            class = "task_create__form_input task_create__form_input_submit"
            form  = "task_create__form"
            id    = "task_create__form_input_submit"
            type  = "submit"
            value = "{{ $task ? 'Change' : 'Create' }}"
        />
    </body>
</html>
