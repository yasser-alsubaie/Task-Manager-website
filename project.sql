CREATE DATABASE tasksWEB_project;
USE tasksWEB_project;

CREATE TABLE users(
	user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR (50) NOT NULL,
    email VARCHAR (100) NOT NULL,
    password VARCHAR (50) NOT NULL
);

CREATE TABLE projects(
	project_id INT PRIMARY KEY AUTO_INCREMENT,
    project_name VARCHAR (100) NOT NULL,
    description TEXT,
    created_at DATE,
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);


CREATE TABLE tasks(
	task_id INT PRIMARY KEY AUTO_INCREMENT,
    task_title VARCHAR(100) NOT NULL,
    task_description TEXT,
    task_status VARCHAR(30) NOT NULL,
    due_date DATE,
    project_id INT,
    FOREIGN KEY (project_id) REFERENCES projects(project_id)
);