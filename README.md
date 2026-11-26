# SOFE2800-TaskFlow

A collaborative task management web application developed for SOFE 2800 – Web Programming.

## Project Overview

TaskFlow is a student-focused task management website designed to help users organize their responsibilities, manage deadlines, and track their productivity. The application provides a simple, responsive, and interactive dashboard where users can manage their daily tasks.

## Features

- User registration and login
- Secure, account-based task storage
- Add, edit, delete, and complete tasks
- Undo completed tasks
- Organize tasks by School, Work, and Personal categories
- Assign deadlines to tasks
- Filter tasks by completion status and category
- Dashboard displaying total, completed, and pending tasks
- Dynamic progress bar
- Deadline reminders
- Confetti animation upon task completion
- Responsive design for different screen sizes
- User logout functionality

## Technologies Used

| Technology | Purpose |
|---|---|
| HTML | Website structure and content |
| CSS | Styling, layouts, and responsive design |
| JavaScript | User interactions and dynamic updates |
| PHP | Server-side functionality |
| MySQL | User and task data storage |
| XAMPP | Local development environment |
| phpMyAdmin | Database management |
| Canvas Confetti | Task completion animation |
| Git and GitHub | Version control and team collaboration |

## Development Concepts

The project incorporates several web development concepts covered throughout SOFE 2800:

- DOM manipulation
- JavaScript event handling
- AJAX and Fetch API
- JSON data exchange
- CRUD operations
- PHP session management and authentication
- Relational databases
- CSS Flexbox and Grid
- Media queries and responsive design
- CSS transitions and hover effects
- Frontend and backend integration

## Installation and Setup

### Prerequisites

- XAMPP
- Git or GitHub Desktop
- A modern web browser

### Step 1: Download the Repository

Clone or download the SOFE2800-TaskFlow repository from GitHub.

Place the project folder inside:

`C:\xampp\htdocs\SOFE2800-TaskFlow`

### Step 2: Start XAMPP

Open the XAMPP Control Panel and start the following services:

- Apache
- MySQL

Ensure both services are running before continuing.

### Step 3: Set Up the Database

1. Open your web browser.
2. Navigate to `http://localhost/phpmyadmin`.
3. Select the Import tab.
4. Choose the `database/schema.sql` file from the project folder.
5. Click Import to create the TaskFlow database.

This step only needs to be completed during the initial setup.

### Step 4: Run the Application

Open the following URL in your browser:

`http://localhost/SOFE2800-TaskFlow/register.php`

Create an account, log in, and begin managing your tasks.

## Project Structure

- `index.php` – Main task management dashboard
- `login.php` – User login page
- `register.php` – User registration page
- `logout.php` – User logout functionality
- `css/` – Website styling and responsive design
- `js/tasks.js` – Task management and dashboard interactions
- `js/reminders.js` – Task deadline reminders
- `tasks/` – PHP endpoints for task operations
- `includes/` – Shared backend and authentication functionality
- `database/schema.sql` – Database structure

## Team Collaboration

TaskFlow was developed collaboratively by a team of five students. Each member contributed to different areas of the application, including frontend development, backend development, database management, authentication, task functionality, reminders, and testing.

GitHub branches and version control were used to allow team members to work independently before integrating their contributions into the main project.

## Challenges and Solutions

Throughout development, the team addressed several technical challenges, including:

- Integrating the frontend with the PHP and MySQL backend.
- Maintaining consistent styling across the application.
- Debugging JavaScript event handling and task completion animations.
- Ensuring task information remained persistent between sessions.
- Maintaining responsive layouts for different screen sizes.
- Integrating contributions from multiple team members.


## Course Information

**Course:** SOFE 2800 – Web Programming

**Institution:** Ontario Tech University

**Project:** Final Group Project

**Team Size:** 5 members

## Acknowledgements

This project was developed as part of the SOFE 2800 Web Programming course to demonstrate practical knowledge of frontend development, backend integration, database management, and collaborative software development.
