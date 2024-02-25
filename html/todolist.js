let tasks = [];

function renderTasks() {
    const todoList = document.getElementById('todoList');
    todoList.innerHTML = '';

    tasks.forEach((task, index) => {
        const li = document.createElement('li');
        li.innerHTML = `
            <span>${task.day}: ${task.text} ${task.time}</span> 
            <button class="deletebutton" onclick="deleteTask(${index})">Delete</button>
        `;
        todoList.appendChild(li);
    });
}
//<button class="editbutton" onclick="editTask(${index})">Edit</button> line 11.
function addTask() {
    const day = document.getElementById('taskDay').value;
    const text = document.getElementById('taskText').value;
    const rawTime = document.getElementById('taskTime').value;

    const time = rawTime.length === 1 ? `0${rawTime}:00` : `${rawTime}:00`;

    tasks.unshift({ day, text, time });
    renderTasks();
}

function editTask(index) {
    const task = tasks[index];
    document.getElementById('taskDay').value = task.day;
    document.getElementById('taskTime').value = task.time;
    document.getElementById('taskText').value = task.text;

    tasks.splice(index, 1);
    renderTasks();
    openAddTask(); // Changed to openAddTask() instead of openAddTaskModal()
}

function deleteTask(index) {
    tasks.splice(index, 1);
    renderTasks();
}

function populateTimeDropdown() {
    const timeDropdown = document.getElementById('taskTime');
    for (let hour = 8; hour <= 20; hour++) {
        const formattedHour = hour < 10 ? `0${hour}` : `${hour}`;
        const option = document.createElement('option');
        option.value = formattedHour;
        option.text = `${formattedHour}:00`;
        timeDropdown.add(option);
    }
}


populateTimeDropdown();

renderTasks();
