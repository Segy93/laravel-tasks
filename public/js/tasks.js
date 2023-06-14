const templates = {                // Templates object
    main: function() {},           // Main template
};

/**
 * init functions
 */
function init() {
    initTemplates();
    initListeners();
    initSortable();
}


/**
 * Listeners initialization
 */
function initListeners() {
    document.addEventListener('submit', submitAnything, false);
    document.querySelector('.tasks__dropdown').addEventListener('change', selectChanged, false);
}

/**
 * Templates initialization
 */
function initTemplates() {
    const html = document.getElementById("tasks__tmpl").innerHTML;
    templates.main = _.template(html);
}

function initSortable() {
    Sortable.create(document.querySelector('.tasks__list'), {
        animation: 150,
        onEnd:     function (event) {
            changeOrder(event.oldIndex + 1, event.newIndex + 1);
        }
    });
}


function render(data) {
    document.querySelector('.tasks__list').innerHTML = templates.main({
        tasks: data,
    })
}

/**
 * Any page submit
 *
 * @param {Event} event
 */
function submitAnything(event) {
    const form = event.target.closest('.tasks__delete_form');
    if (form !== null) {
        event.preventDefault();
        removeTask(form);
    }
}

/**
 * Project dropdown selected
 *
 * @param {Event} event    Event on change
 */
function selectChanged(event) {
    const project_value = event.currentTarget.value
    const project_id = project_value !== '' ? parseInt(project_value) : null;

    fetchData(project_id);
}

/**
 * Changing order of tasks
 *
 * @param {Number} order_old   Old order number
 * @param {Number} order_new   New order number
 */
function changeOrder(order_old, order_new) {
    Task.Main.Ajax(
        "TaskController",
        "changeOrder",
        {
            order_old,
            order_new,
        }
    );
}

/**
 * Attempting to delete task
 *
 * @param {HTMLElement} form  Form for deleting task
 */
function removeTask(form) {
    const task_id = parseInt(form.elements.task_id.value, 10);
    if (confirm('Are you sure you want to delete task?') === true) {
        deleteTask(form, task_id);
    }
}








/**
 * Fetching tasks
 *
 * @param {Number} project_id    Id project
 */
function fetchData(project_id) {
    Task.Main.Ajax(
        'TaskController',
        'fetchData',
        {
            project_id,
        },
        render
    );
}


/**
 * Deleting of task
 *
 * @param {HTMLElement} form  Form for deleting task
 * @param {Number} task_id    Id task
 */
function deleteTask(form, task_id) {
    Task.Main.Ajax(
        'TaskController',
        'deleteTask',
        {
            task_id,
        },
        {
            success: function() {
                form.parentElement.remove();
            }
        }
    );
}


document.addEventListener('DOMContentLoaded', init);
