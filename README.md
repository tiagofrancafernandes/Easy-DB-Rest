# Easy DB Rest

> **Easy DB Rest APP** is a Laravel-based RESTful API designed to act as a secure, dynamic, and stateful/stateless multi-DBMS SQL client.

It allows external applications to execute queries on various database engines (SQLite, PostgreSQL, MySQL, SQL Server) by sending database credentials either at runtime within the request payload or via pre-configured connections stored in the database.

### Key Features
- **Raw SQL Execution**: Execute raw SQL with configurable security guards to block dangerous statements (e.g., `DROP`, `TRUNCATE`).
- **Declarative Query Builder**: Execute safe and structured data retrieval via JSON payloads without writing raw SQL.
- **Dynamic Connections**: Accepts connection parameters per-request or via UUID references (e.g., `X-Config-ID` header).

---

## 📚 Examples

Integration examples (cURL, JavaScript, PHP) and ready-to-use HTTP requests (see [requests.http](examples/requests.http)) can be found in the `examples/` directory.

---

## 🚀 How to Run

```bash
git clone <repo>
cd project
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

---

## 🧪 Running Tests

```bash
php artisan test
```
