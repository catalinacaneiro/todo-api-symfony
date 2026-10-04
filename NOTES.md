### Documentation – Database & Task Model

- Configured Symfony and Doctrine to connect to a local MySQL database using `DATABASE_URL` in `.env.local`.
- Used `.env.local` to keep local database credentials out of Git.
- Created the `todo_symfony` database through Doctrine and verified the connection.
- Decided to use a Doctrine `Task` entity instead of manually writing SQL queries.
- The Task model contains: `id`, `title`, `description`, `completed`, and `createdAt`.
- `createdAt` will be set automatically by the backend because the server is responsible for knowing when a task is created.
- The database schema will later be created using a Doctrine migration.