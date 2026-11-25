
// Get the HTML elements

const taskForm = document.getElementById("taskForm");
const taskInput = document.getElementById("taskInput");
const taskCategory = document.getElementById("taskCategory");
const taskDeadline = document.getElementById("taskDeadline");
const taskList = document.getElementById("taskList");
const emptyMessage = document.getElementById("emptyMessage");

// Tasks now come from the database
let tasks = [];

// Get this user's tasks from list.php
async function loadTasks() {
    const response = await fetch("tasks/list.php");
    const data = await response.json();

    if (data.success) {
        tasks = data.tasks.map(task => ({
            ...task,
            completed: task.status === "completed"
        }));
    }

    displayTasks();
      
}

// Nothing to save in the browser anymore
function saveTasks() {}

// Current filter
let currentFilter ="all";
let currentCategory = "all";

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
        if (currentCategory !== "all" && task.category !==currentCategory) {
            return false;
        }

        if (currentFilter === "completed") {
            return task.completed;
        }
        
        if (currentFilter === "pending"){
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
            (task.category || "No category") +  " | Due: " + (task.deadline || "No deadline");

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

    if (typeof reminderRefresh ==="function"){
        reminderRefresh();

    }
}

// Add a task
taskForm.addEventListener("submit", async function(event) {
    event.preventDefault();

    let title = taskInput.value.trim();
    let category = taskCategory.value;
    let deadline = taskDeadline.value;

    if (title === "" || deadline === "") {
        alert("Please enter a task name and deadline.");
        return;
    }

   const response = await fetch("tasks/add.php",{
    method: "POST",
    headers :{"Content-Type": "application/json"},
    body: JSON.stringify({title: title, category: category, deadline: deadline})
    })
     const data = await response.json();
    if(!data.success){
        alert(data.message);
        return;
    }
    loadTasks();
   

    taskForm.reset();
});

// Mark task completed or pending
// Mark task completed or pending (saved in the database)
async function toggleTask(id) {
    const response = await fetch("tasks/complete.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: id })
    });
    const data = await response.json();

    if (!data.success) {
        alert(data.message);
        return;
    }

    loadTasks();
}

// Edit an existing task
// Edit an existing task (saved in the database)
async function editTask(id) {
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

    const response = await fetch("tasks/edit.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: id, title: newTitle })
    });
    const data = await response.json();

    if (!data.success) {
        alert(data.message);
        return;
    }

    loadTasks();
}

// Delete a task
// Delete a task (from the database)
async function deleteTask(id) {
    let confirmDelete = confirm("Are you sure you want to delete this task?");

    if (!confirmDelete) {
        return;
    }

    const response = await fetch("tasks/delete.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: id })
    });
    const data = await response.json();

    if (!data.success) {
        alert(data.message);
        return;
    }

    loadTasks();
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
// Category filter buttons
const categoryButtons = document.querySelectorAll(".category-filters button");
for (let btn of categoryButtons){
    btn.addEventListener("click", function(){
        currentCategory = btn.dataset.category;
 for (let b of categoryButtons) {
            b.classList.remove("active");
        }
        btn.classList.add("active");
                displayTasks();
       
    });
}

// Display saved tasks when the page opens
loadTasks();
