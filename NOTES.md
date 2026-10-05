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


## POST /tasks
Implemented POST /tasks using a Symfony controller, a small input DTO, Symfony Validator and Doctrine. Valid tasks are persisted with persist() and written to the database with flush(). A successful creation returns HTTP 201. Invalid empty or whitespace-only titles return HTTP 422 and are not saved.
Manually verified that a created task can subsequently be retrieved through GET /tasks.

### PATCH /tasks/{id}

Implemented partial updates for title, description, and completed.
Omitted fields and createdAt are preserved. Description can be cleared with null.

Manual tests confirmed successful updates (200), invalid empty titles (422),
empty objects (400), and missing tasks (404).
A subsequent GET confirmed the saved changes and that the invalid title was not saved.










### Git branch correction

The POST implementation was committed on `feature-get-tasks` but had not been merged into the branch used for PATCH or pushed to the remote.

The existing POST commit was cherry-picked into `feature-patch-task`. Uncommitted PATCH work was preserved using a stash, including untracked files.

Verified that GET, POST, and PATCH are present and that the full test suite and container lint pass.

Removed an accidental `-.` prefix from CreateTaskControllerTest.php that prevented the test file from loading.

