# Instructions for AI Assistants

## Project Overview
**Easy DB Rest** is a Laravel-based RESTful API designed to act as a secure, dynamic, multi-DBMS SQL client. It allows external applications to execute queries on various database engines (SQLite, PostgreSQL, MySQL, SQL Server) by sending database credentials either at runtime within the request payload or via pre-configured connections stored in the database. 

The API supports:
- **Raw SQL execution** with configurable security guards to block dangerous statements (e.g., DROP, TRUNCATE).
- **Declarative Query Builder** via JSON payloads, allowing safe and structured data retrieval without writing raw SQL.
- **Dynamic Connections**, accepting connection parameters per-request or via UUID references (e.g., `X-Config-ID` header).

## AI Behavior & Limitations
- **Be Concise**: Provide direct answers without unnecessary explanations, pleasantries, or conclusions.
- **Strict Execution**: Do exactly what is requested without altering unrelated code or making assumptions.
- **Focus on Output**: Prioritize code output over long textual explanations.

## Coding Standards & Code Style
When generating, refactoring, or analyzing code for this project, you MUST strictly adhere to the coding rules defined in the @UNIVERSAL-CODE-STYLE-RULES.md file.

Additionally, you must apply the following guidelines:
- **Language**: All code (variables, methods, classes, etc.) MUST be written in English.
- **Comments**: Only DocBlocks are acceptable. Avoid inline or regular comments unless absolutely necessary to explain highly complex logic. Let the code explain itself.
- **Best Practices**: Use Clean Code principles to write clean, expressive, readable, and maintainable code.
- **SOLID**: Apply SOLID principles in the architecture and object-oriented design.
- **PHP Standards**: All PHP code must strictly follow PSR-12 recommendations.
- **Laravel Patterns**: Follow Laravel framework conventions and best practices (proper use of Controllers, Models, Form Requests, Services, Resources, etc.).
