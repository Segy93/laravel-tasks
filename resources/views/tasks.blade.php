<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta name="csrf-token" id = "csrf-token" content="{{ csrf_token() }}" />
    </head>
    <body>
        <form
            action = ""
            class  = "tasks__form"
            method = "post"
        >
            @csrf <!-- {{ csrf_field() }} -->
            <select class = "tasks__dropdown">
                <option value = "">No project</option>
                @foreach ($projects as $project)
                    <option
                        value = "{{ $project->id }}"
                    >
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
        </form>
        <a
            class = "tasks__link"
            href  = "{{ route('taskCreate') }}"
        >
            Create new task
        </a>
        <ul class = "tasks__list">
            @foreach ($tasks as $task)
                <li>
                    {{ $task->name }}
                    <a href = "{{ route('taskEdit', ['task_id' => $task->id]) }}">
                        Edit
                    </a>
                    <form
                        action = "{{ route('taskDelete') }}"
                        class  = "tasks__delete_form"
                        method = "post"
                    >
                        @csrf <!-- {{ csrf_field() }} -->
                        <button
                            class = "tasks__delete_button"
                            name  = "task_id"
                            type  = "submit"
                            value = "{{ $task->id }}"
                        >
                            Delete
                        </button>
                    </form>
                </li>
            @endforeach
        </ul>
        <script src = "{{ asset('js/libs/underscore.min.js') }}" type = "text/javascript"></script>
        <script src = "{{ asset('js/libs/Sortable.min.js') }}" type = "text/javascript"></script>
        <script src = "{{ asset('js/MainAjax.js') }}" type = "text/javascript"></script>
        <script src = "{{ asset('js/tasks.js') }}" type = "text/javascript"></script>
        <script id = "tasks__tmpl" type = "text/html">
            <% tasks.forEach(task => { %>
                <li>
                    <%= task.name %>
                    <a href = "{{ route('taskEdit', ['task_id' => '<%= task.id %>']) }}">
                        Edit
                    </a>
                    <form
                        action = "{{ route('taskDelete') }}"
                        class  = "tasks__delete_form"
                        method = "post"
                    >
                        @csrf <!-- {{ csrf_field() }} -->
                        <button
                            class = "tasks__delete_button"
                            name  = "task_id"
                            type  = "submit"
                            value = "<%= task.id %>"
                        >
                            Delete
                        </button>
                    </form>
                </li>
            <% }); %>
        </script>
    </body>
</html>
