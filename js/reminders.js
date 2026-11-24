const reminderSoonDays = 3;
const reminderUpcomingDays = 7;

const reminderMessages = {
    overdue: "Overdue!",
    today: "Due today!",
    soon: "Due within 3 days."
};

const reminderColors = {
    overdue: "#dc2626",
    today: "#ea580c",
    soon: "#eab308"
};

const reminderBackgrounds = {
    overdue: "#fef2f2",
    today: "#fff7ed",
    soon: "#fefce8"
};

function reminderDaysLeft(deadline) {
    const due = new Date(deadline + "T00:00:00");

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    return Math.round((due - today) / 86400000);
}

function reminderGetStatus(days, completed) {
    if (completed) {
        return "";
    }
    if (days < 0) {
        return "overdue";
    }
    if (days === 0) {
        return "today";
    }
    if (days <= reminderSoonDays) {
        return "soon";
    }
    return "";
}

function reminderMarkTasks() {
    const items = document.querySelectorAll("#taskList .task-item");

    for (let i = 0; i < items.length; i++) {
        const item = items[i];

        if (item.querySelector(".reminder-badge")) {
            continue;
        }

        const deadline = item.dataset.deadline;
        if (deadline === undefined) {
            continue;
        }

        const days = reminderDaysLeft(deadline);
        const completed = item.dataset.completed === "true";
        const status = reminderGetStatus(days, completed);

        if (status === "") {
            continue;
        }

        item.style.borderLeft = "6px solid " + reminderColors[status];
        item.style.backgroundColor = reminderBackgrounds[status];

        const badge = document.createElement("span");
        badge.className = "reminder-badge";
        badge.textContent = reminderMessages[status];
        badge.style.display = "block";
        badge.style.fontWeight = "bold";
        badge.style.color = reminderColors[status];
        item.querySelector(".task-info").appendChild(badge);
    }
}

function reminderShowUpcoming() {
    let box = document.getElementById("reminderUpcoming");

    if (box === null) {
        box = document.createElement("section");
        box.id = "reminderUpcoming";
        box.style.backgroundColor = "white";
        box.style.padding = "25px";
        box.style.marginBottom = "25px";
        box.style.borderRadius = "10px";

        const taskSection = document.querySelector(".task-section");
        taskSection.parentNode.insertBefore(box, taskSection);
    }

    box.innerHTML = "<h2>Upcoming Deadlines</h2>";
    let count = 0;

    for (let i = 0; i < tasks.length; i++) {
        const task = tasks[i];

        if (task.completed) {
            continue;
        }

        const days = reminderDaysLeft(task.deadline);
        if (isNaN(days) || days > reminderUpcomingDays) {
            continue;
        }

        const status = reminderGetStatus(days, false);
        const line = document.createElement("p");
        line.textContent = task.title + " - " + task.deadline;
        line.style.padding = "8px 12px";
        line.style.borderLeft = "6px solid #94a3b8";

        if (status !== "") {
            line.textContent = line.textContent + " (" + reminderMessages[status] + ")";
            line.style.borderLeft = "6px solid " + reminderColors[status];
            line.style.backgroundColor = reminderBackgrounds[status];
        }

        box.appendChild(line);
        count = count + 1;
    }

    if (count === 0) {
        const none = document.createElement("p");
        none.textContent = "No upcoming deadlines.";
        box.appendChild(none);
    }
}

function reminderRefresh() {
    reminderMarkTasks();
    reminderShowUpcoming();
}

document.addEventListener("DOMContentLoaded", function () {
    reminderRefresh();
});
