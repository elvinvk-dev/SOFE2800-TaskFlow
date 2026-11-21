
// Get the HTML elements

const taskForm = document.getElementById("taskForm");
const taskInput = document.getElementById("taskInput");
const taskCategory = document.getElementById("taskCategory");
const taskDeadline = document.getElementById("taskDeadline");
const taskList = document.getElementById("taskList");
const emptyMessage = document.getElementById("emptyMessage");

// Load saved tasks from localStorage
let tasks = JSON.parse(localStorage.getItem("taskflowTasks") || "[]");

// Current filter
let currentFilter = "all";

// Save tasks
function saveTasks() {
    localStorage.setItem("taskflowTasks", JSON.stringify(tasks));
}

// Update dashboard statistics
function updateDashboard() {
    let total = tasks.length;

    let completed = tasks.filter(task => task.completed).length;

    let pending = total - completed;

    document.getElementById("totalTasks").textContent = total;
    document.getElementById("completedTasks").textContent = completed;
    document.getElementById("pendingTasks").textContent = pending;

    // Calculate completion percentage
    let percentage = 0;

    if (total > 0) {
        percentage = Math.round((completed / total) * 100);
    }

    document.getElementById("progressFill").style.width =
        percentage + "%";

    document.getElementById("progressText").textContent =
        percentage + "% Completed";
}

// Display tasks on the page
function displayTasks() {
    taskList.innerHTML = "";

    // Filter tasks based on selected filter
    let filteredTasks = tasks.filter(task => {
        if (currentFilter === "completed") {
            return task.completed;
        }

        if (currentFilter === "pending") {
            return !task.completed;
        }

        return true;
    });

    // Show message when there are no tasks
    if (filteredTasks.length === 0) {
        emptyMessage.style.display = "block";
    } else {
        emptyMessage.style.display = "none";
    }

    filteredTasks.forEach(task => {
        const li = document.createElement("li");
        li.className = "task-item";
        li.dataset.deadline = task.deadline;
        li.dataset.completed = task.completed;

        if (task.completed) {
            li.classList.add("completed");
        }

        // Task information
        const info = document.createElement("div");
        info.className = "task-info";

        const title = document.createElement("strong");
        title.textContent = task.title;

        const details = document.createElement("small");
        details.textContent =
            task.category + " | Due: " + task.deadline;

        info.appendChild(title);
        info.appendChild(details);

        // Task buttons
        const actions = document.createElement("div");
        actions.className = "task-actions";

        // Complete button
        const completeButton = document.createElement("button");
        completeButton.className = "complete-btn";

        if (task.completed) {
            completeButton.textContent = "Undo";
        } else {
            completeButton.textContent = "Complete";
        }

        completeButton.addEventListener("click", function() {
            toggleTask(task.id);
        });

        // Edit button
        const editButton = document.createElement("button");
        editButton.className = "edit-btn";
        editButton.textContent = "Edit";

        editButton.addEventListener("click", function() {
            editTask(task.id);
        });

        // Delete button
        const deleteButton = document.createElement("button");
        deleteButton.className = "delete-btn";
        deleteButton.textContent = "Delete";

        deleteButton.addEventListener("click", function() {
            deleteTask(task.id);
        });

        actions.appendChild(completeButton);
        actions.appendChild(editButton);
        actions.appendChild(deleteButton);

        li.appendChild(info);
        li.appendChild(actions);

        taskList.appendChild(li);
    });

    updateDashboard();
}

// Add a task
taskForm.addEventListener("submit", function(event) {
    event.preventDefault();

    let title = taskInput.value.trim();
    let category = taskCategory.value;
    let deadline = taskDeadline.value;

    if (title === "" || deadline === "") {
        alert("Please enter a task name and deadline.");
        return;
    }

    let newTask = {
        id: crypto.randomUUID(),
        title: title,
        category: category,
        deadline: deadline,
        completed: false
    };

    tasks.push(newTask);

    saveTasks();
    displayTasks();

    taskForm.reset();
});

// Mark task completed or pending
function toggleTask(id) {
    let task = tasks.find(task => task.id === id);

    if (task) {
        task.completed = !task.completed;
        saveTasks();
        displayTasks();
    }
}

// Edit an existing task
function editTask(id) {
    let task = tasks.find(task => task.id === id);

    if (!task) {
        return;
    }

    let newTitle = prompt("Edit task name:", task.title);

    if (newTitle === null) {
        return;
    }

    newTitle = newTitle.trim();

    if (newTitle === "") {
        alert("Task name cannot be empty.");
        return;
    }

    task.title = newTitle;

    saveTasks();
    displayTasks();
}

// Delete a task
function deleteTask(id) {
    let confirmDelete = confirm("Are you sure you want to delete this task?");

    if (confirmDelete) {
        tasks = tasks.filter(task => task.id !== id);

        saveTasks();
        displayTasks();
    }
}

// Filter buttons
const filterButtons = document.querySelectorAll(".filters button");

filterButtons.forEach(button => {
    button.addEventListener("click", function() {
        currentFilter = button.dataset.filter;

        filterButtons.forEach(btn => {
            btn.classList.remove("active");
        });

        button.classList.add("active");

        displayTasks();
    });
});

// Display saved tasks when the page opens
displayTasks();
