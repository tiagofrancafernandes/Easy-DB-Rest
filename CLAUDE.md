# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Easy DB Rest** is a Laravel 12-based RESTful API that acts as a secure, dynamic, multi-DBMS SQL client. It enables external applications to execute queries on various database engines (SQLite, PostgreSQL, MySQL, SQL Server) either through runtime credentials or pre-configured connections.

## Development Commands

### Setup
```bash
composer setup  # Installs dependencies, generates key, runs migrations, builds frontend
```

### Development Server
```bash
composer dev    # Starts concurrent processes: server, queue, logs, and vite
# Or individually:
php artisan serve
php artisan queue:listen --tries=1 --timeout=0
php artisan pail --timeout=0
npm run dev
```

### Testing
```bash
composer test              # Runs all tests with config:clear
php artisan test          # Standard test command
php artisan test --filter=ConnectionCrudTest  # Run specific test class
```

### Code Quality
```bash
./vendor/bin/pint         # Laravel Pint code formatter (PSR-12)
php artisan ide-helper:generate  # Generate IDE helper files
```

## Architecture

### Core Components

**Connection Management**
- `ConnectionManager`: Resolves database connections from config ID (UUID), inline credentials, or overrides
- `ConnectionConfigDto`: Immutable DTO for connection parameters
- `Connection` Model: Stores pre-configured database connections with encrypted credentials
- Supported drivers: `sqlite`, `pgsql`, `mysql`, `sqlsrv` (configurable in `config/application.php`)

**Query Execution**
- `QueryExecutorService`: Main orchestrator for query execution
- `RawQueryExecutor`: Executes raw SQL with security validation
- `BuilderQueryExecutor`: Executes declarative JSON queries using Laravel Query Builder
- `QuerySecurityGuard`: Validates queries against dangerous statements (DROP, TRUNCATE, etc.)
- `QueryPayloadDto`: Encapsulates query type (Raw/Builder), query, bindings, and execution options

**Security Layers**
1. Driver whitelist validation (`allowed_drivers` in config)
2. Dangerous statement blocking (`blocked_statements` in config)
3. Builder method whitelist (`allowed_builder_methods` in config)
4. Query timeout enforcement (`default_timeout` in config)
5. Row limit enforcement (`max_rows` in config)

### Request Flow

**Connection Resolution Order:**
1. `X-Config-ID` header → Load from database
2. `config_id` in JSON body → Load from database
3. `connection` object in JSON → Inline configuration
4. `overrides` array → Override any connection parameter

**Query Execution Modes:**
- **Raw SQL**: POST `/api/query/raw` (body contains SQL) or Content-Type: `application/sql`
- **Builder**: POST `/api/query/builder` (JSON with builder methods)
- **Auto-detect**: POST `/api/query` (auto-selects based on Content-Type)

### Directory Structure

```
app/
├── DTOs/                    # Data Transfer Objects (immutable)
├── Enums/                   # QueryType, SupportedDriver
├── Exceptions/              # Domain-specific exceptions
├── Http/
│   ├── Controllers/Api/     # API controllers
│   ├── Requests/            # Form Request validation
│   └── Resources/           # JSON response transformers
├── Models/                  # Eloquent models
└── Services/                # Business logic layer

config/application.php       # Application-specific configuration
routes/api.php              # API route definitions
tests/Feature/              # Feature tests (database integration)
tests/Unit/                 # Unit tests (isolated logic)
```

## Code Standards

**Mandatory Style Rules:**
- All code MUST follow `UNIVERSAL-CODE-STYLE-RULES.md`
- PHP MUST follow PSR-12 (enforced by Laravel Pint)
- Use `declare(strict_types=1)` in all PHP files
- Apply SOLID principles and Clean Code patterns
- Use guard clauses and early returns (no nested if/else)
- Separate logical sections with blank lines
- DocBlocks required, inline comments discouraged

**Laravel Conventions:**
- Form Requests for validation (`StoreConnectionRequest`, `QueryRequest`)
- API Resources for response transformation (`ConnectionResource`, `QueryResultResource`)
- Service layer for business logic (no business logic in controllers)
- DTOs for data transfer between layers
- Dependency Injection in constructors

## Configuration

**Security Settings** (`config/application.php`):
- `query.default_timeout`: Maximum query execution time (default: 30s)
- `query.max_rows`: Maximum rows returned (default: 1000)
- `query.block_dangerous_statements`: Global dangerous statement blocking (default: true)
- `query.blocked_statements`: Array of blocked SQL statement prefixes
- `connections.allowed_drivers`: Whitelist of permitted database drivers
- `connections.allowed_builder_methods`: Whitelist of permitted Query Builder methods

**Environment Variables** (`.env`):
- `QUERY_DEFAULT_TIMEOUT`: Override default query timeout
- `QUERY_MAX_ROWS`: Override max rows limit
- `QUERY_BLOCK_DANGEROUS`: Override dangerous statement blocking

## API Endpoints

```
GET  /api                          # API information
GET  /api/health                   # Health check
POST /api/connection/test          # Test database connection
GET  /api/connections              # List stored connections
POST /api/connections              # Create connection
GET  /api/connections/{id}         # Get connection details
PUT  /api/connections/{id}         # Update connection
DELETE /api/connections/{id}       # Delete connection
POST /api/query                    # Execute query (auto-detect mode)
POST /api/query/raw                # Execute raw SQL
POST /api/query/builder            # Execute builder query
```

## Testing Strategy

**Test Organization:**
- `tests/Feature/`: HTTP request/response tests with database
- `tests/Unit/`: Isolated unit tests without database

**Key Test Suites:**
- `ConnectionCrudTest`: CRUD operations for connection management
- `QuerySecurityTest`: Security guard validation
- `QueryExecutionTest`: Query execution with various drivers

**Database Testing:**
- Uses SQLite in-memory (`:memory:`) for speed
- Transactions rolled back after each test
- Defined in `phpunit.xml`

## AI Assistant Guidelines

**From AGENTS.md:**
- Be concise: Direct answers without unnecessary explanations
- Strict execution: Do exactly what is requested
- Focus on output: Prioritize code over long explanations
- English only: All code, variables, methods, classes in English
- Minimal comments: DocBlocks only, let code explain itself
- Follow UNIVERSAL-CODE-STYLE-RULES.md strictly (non-negotiable)
