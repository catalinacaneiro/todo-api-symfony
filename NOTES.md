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



## Task Entity → Database Mapping

Task.php                         Database / Migration
-----------------------------------------------------
$id                              id INT AUTO_INCREMENT
$title                           title VARCHAR(255) NOT NULL
$description                     description LONGTEXT DEFAULT NULL
$completed = false               completed TINYINT DEFAULT 0
$createdAt                       created_at DATETIME NOT NULL