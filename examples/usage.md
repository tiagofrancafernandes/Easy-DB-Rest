# Easy DB Rest - Integration Examples

This document provides examples of how to interact with the **Easy DB Rest API** from various environments and languages.

---

## 1. Using cURL

### Execute a Raw SQL Query (Runtime Configuration)
```bash
curl -X POST http://127.0.0.1:8000/api/query \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer Your-Auth-Token-Here" \
    -d '{
        "type": "raw",
        "query": "SELECT * FROM users WHERE active = 1 LIMIT 10",
        "connection": {
            "driver": "pgsql",
            "host": "127.0.0.1",
            "port": 5432,
            "database": "my_database",
            "username": "my_user",
            "password": "my_password_in_base64"
        }
    }'
```

### Execute a Declarative Query Builder (Persisted Configuration)
```bash
curl -X POST http://127.0.0.1:8000/api/query \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer Your-Auth-Token-Here" \
    -H "X-Config-ID: 1" \
    -d '{
        "type": "select",
        "table": "orders",
        "query": [
            {
                "method": "where",
                "args": ["total", ">", 100]
            },
            {
                "method": "limit",
                "args": [5]
            }
        ]
    }'
```

### Create a Snippet
```bash
curl -X POST http://127.0.0.1:8000/api/snippets \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer Your-Auth-Token-Here" \
    -d '{
        "name": "reports/monthly_revenue.sql",
        "content": "SELECT SUM(total) FROM orders WHERE created_at >= '\''2024-01-01'\'';",
        "public_content_slug": "jan-2024-revenue",
        "public_content_password": "securepassword",
        "tags": ["sql", "finance", "report"]
    }'
```

---

## 2. Using JavaScript (Fetch API)

### Execute a Query Builder
```javascript
async function fetchTopOrders() {
    const payload = {
        type: "select",
        table: "orders",
        query: [
            { method: "select", args: ["id", "total"] },
            { method: "orderByDesc", args: ["total"] },
            { method: "limit", args: [10] }
        ]
    };

    try {
        const response = await fetch('http://127.0.0.1:8000/api/query', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': 'Bearer Your-Auth-Token-Here',
                'X-Config-ID': '1' // Using persisted connection ID 1
            },
            body: JSON.stringify(payload)
        });

        const result = await response.json();
        console.log("Top Orders:", result.data);
    } catch (error) {
        console.error("Error executing query:", error);
    }
}

fetchTopOrders();
```

### Access a Public Snippet (Plain Text)
```javascript
async function getSnippet() {
    const userId = "019dc662-8772-710c-99f5-468843f153b7";
    const slug = "jan-2024-revenue";
    const password = "securepassword";

    const response = await fetch(`http://127.0.0.1:8000/api/snippets/${userId}/${slug}?password=${password}&text`);
    const sql = await response.text();
    console.log("SQL Content:", sql);
}
```

---

## 3. Using PHP (GuzzleHTTP)

### Execute Raw SQL (Runtime Configuration)
```php
<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

$client = new Client([
    'base_uri' => 'http://127.0.0.1:8000/api/',
    'timeout'  => 10.0,
]);

try {
    $response = $client->post('query', [
        'headers' => [
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer Your-Auth-Token-Here',
        ],
        'json' => [
            'type' => 'raw',
            'query' => 'SELECT id, email FROM users LIMIT 3',
            'connection' => [
                'driver'   => 'mysql',
                'host'     => '127.0.0.1',
                'port'     => 3306,
                'database' => 'forge',
                'username' => 'forge',
                'password' => 'secret'
            ]
        ]
    ]);

    $data = json_decode($response->getBody()->getContents(), true);
    print_r($data['data']);

} catch (RequestException $e) {
    echo "Request failed: " . $e->getMessage();
}
```

---

## 4. Connection & Team Management

### Share a Connection with a Team
```bash
curl -X POST http://127.0.0.1:8000/api/connections/{uuid}/share \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer Your-Auth-Token-Here" \
    -d '{
        "team_id": "019dc662-d29a-730c-9876-468843f153b7",
        "permission": "view"
    }'
```

### Create a Team and Add a Member
```bash
# 1. Create the team
curl -X POST http://127.0.0.1:8000/api/teams \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer Your-Auth-Token-Here" \
    -d '{
        "name": "DevOps Experts"
    }'

# 2. Add a member
curl -X POST http://127.0.0.1:8000/api/teams/{team_uuid}/members \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer Your-Auth-Token-Here" \
    -d '{
        "user_id": 5
    }'
```

### Create a PostgreSQL Connection via URL
```bash
curl -X POST http://127.0.0.1:8000/api/connections \
    -H "Accept: application/json" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer Your-Auth-Token-Here" \
    -d '{
        "name": "Data Warehouse",
        "driver": "pgsql",
        "url": "postgresql://dw_user:secret@dw-host:5433/dw_database",
        "search_path": "analytics,public",
        "sslmode": "require",
        "tags": ["dw", "reporting", "pgsql"]
    }'
```
