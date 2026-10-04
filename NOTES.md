### Documentation – Database & Task Model

- Configured Symfony and Doctrine to connect to a local MySQL database using `DATABASE_URL` in `.env.local`.
- Used `.env.local` to keep local database credentials out of Git.
- Created the `todo_symfony` database through Doctrine and verified the connection.
- Decided to use a Doctrine `Task` entity instead of manually writing SQL queries.
- The Task model contains: `id`, `title`, `description`, `completed`, and `createdAt`.
- `createdAt` will be set automatically by the backend because the server is responsible for knowing when a task is created.
- The database schema will later be created using a Doctrine migration.

## 
Created the Task Doctrine entity with the same fields as the vanilla PHP version. Doctrine maps the PHP object to the database structure. The entity also contains validation for the title and sets completed and createdAt automatically.

## 
Doctrine generated an additional messenger_messages table because Symfony Messenger was configured with a database transport. I reviewed this before running the migration instead of automatically accepting the generated schema.


## Endpoints - 


## GET 

GET /tasks
   ↓
Symfony Router
   ↓
Controller
   ↓
Doctrine
   ↓
Task-objekt
   ↓
JSON


### GET /tasks – AI observation

Codex implemented the `GET /tasks` endpoint and also added tests in `TaskControllerTest.php`.

The tests were not explicitly requested in my prompt --> 
--> 
## PROMT 
Implement only GET /tasks.
Requirements:
- Route: GET /tasks
- Use a Symfony controller with an attribute route.
- Use Doctrine to retrieve all Task entities.
- Order tasks by createdAt descending, then id descending, so the newest tasks appear first.
- Return HTTP 200 with JSON.
- If there are no tasks, return [].
- The JSON should contain only: id, title, description, completed, and createdAt.
Keep the implementation simple and appropriate for this small API.
Do not implement POST, PATCH, or DELETE.
Do not modify the database schema or create migrations.
After implementing it, explain what you changed and how the request flows through the code. Also tell me how I can manually test the endpoint.
## end of my promt 

 so Codex took an extra initiative. I decided to keep them because they are relevant and all tests passed.

This was a good reminder to always review AI-generated changes instead of accepting them automatically.


## GET /tasks
Implemented GET /tasks using a Symfony controller and Doctrine.
Symfony handles the route with a #[Route] attribute, and Doctrine retrieves the tasks without manually writing SQL.
Tasks are ordered by createdAt and id in descending order.
Codex also added automated tests, even though they were not explicitly requested. The tests passed successfully.
I manually tested the endpoint with curl and in the browser. It returned HTTP 200 and an empty JSON array ([]), which is expected because no tasks have been created yet.







