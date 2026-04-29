<?php

declare(strict_types=1);

return [
    // =========================
    // JavaScript - ES6+ Features
    // =========================
    [
        'name' => 'javascript-async-await.js',
        'type' => 'javascript',
        'content' => <<<'JS'
// Async function with error handling
async function fetchData(url) {
    try {
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Fetch error:', error);
        throw error;
    }
}

// Using async/await
const data = await fetchData('https://api.example.com/users');
console.log(data);
JS,
        'public_content_slug' => 'javascript-async-await',
        'public_content_index' => false,
    ],

    [
        'name' => 'javascript-array-methods.js',
        'type' => 'javascript',
        'content' => <<<'JS'
const numbers = [1, 2, 3, 4, 5];

// Map - transform each element
const doubled = numbers.map(n => n * 2);

// Filter - select elements
const evens = numbers.filter(n => n % 2 === 0);

// Reduce - accumulate values
const sum = numbers.reduce((acc, n) => acc + n, 0);

// Find - get first match
const first = numbers.find(n => n > 3);

// Some / Every - check conditions
const hasEven = numbers.some(n => n % 2 === 0);
const allPositive = numbers.every(n => n > 0);

// Includes - check presence
const has3 = numbers.includes(3);
JS,
        'public_content_slug' => 'javascript-array-methods',
        'public_content_index' => false,
    ],

    [
        'name' => 'javascript-object-operations.js',
        'type' => 'javascript',
        'content' => <<<'JS'
const user = { id: 1, name: 'John', email: 'john@example.com' };

// Object.keys, values, entries
const keys = Object.keys(user); // ['id', 'name', 'email']
const values = Object.values(user); // [1, 'John', 'john@example.com']
const entries = Object.entries(user); // [['id', 1], ['name', 'John'], ...]

// Object spread and merge
const updated = { ...user, name: 'Jane', age: 30 };
const merged = Object.assign({}, user, { role: 'admin' });

// Object.freeze - immutable
const frozen = Object.freeze(user);

// Destructuring
const { id, name, ...rest } = user;
JS,
        'public_content_slug' => 'javascript-object-operations',
        'public_content_index' => false,
    ],

    [
        'name' => 'javascript-promise-handling.js',
        'type' => 'javascript',
        'content' => <<<'JS'
// Promise.all - parallel execution
Promise.all([
    fetch('https://api.example.com/users'),
    fetch('https://api.example.com/posts'),
])
    .then(([usersRes, postsRes]) => Promise.all([usersRes.json(), postsRes.json()]))
    .then(([users, posts]) => console.log(users, posts))
    .catch(error => console.error('Error:', error));

// Promise.race - fastest resolves
Promise.race([
    fetch('url1'),
    fetch('url2'),
]).then(result => console.log('First resolved:', result));

// Promise.allSettled - all states
Promise.allSettled([promise1, promise2, promise3])
    .then(results => results.forEach(r => console.log(r.status, r.value)));
JS,
        'public_content_slug' => 'javascript-promise-handling',
        'public_content_index' => false,
    ],

    [
        'name' => 'javascript-class-definition.js',
        'type' => 'javascript',
        'content' => <<<'JS'
class User {
    constructor(id, name, email) {
        this.id = id;
        this.name = name;
        this.email = email;
    }

    // Instance method
    getFullInfo() {
        return `${this.name} (${this.email})`;
    }

    // Static method
    static create(data) {
        return new User(data.id, data.name, data.email);
    }

    // Getter
    get isActive() {
        return this.status === 'active';
    }

    // Setter
    set email(value) {
        this._email = value.toLowerCase();
    }
}

// Usage
const user = User.create({ id: 1, name: 'John', email: 'John@Example.com' });
console.log(user.getFullInfo());
JS,
        'public_content_slug' => 'javascript-class-definition',
        'public_content_index' => false,
    ],

    // =========================
    // TypeScript - Type Definitions
    // =========================
    [
        'name' => 'typescript-interfaces.ts',
        'type' => 'typescript',
        'content' => <<<'TS'
// Interface definition
interface User {
    id: number;
    name: string;
    email: string;
    age?: number; // Optional property
}

// Interface extending
interface Admin extends User {
    role: 'admin' | 'moderator';
    permissions: string[];
}

// Function with interface parameter
function getUser(user: User): string {
    return `${user.name} (${user.email})`;
}

// Object satisfying interface
const admin: Admin = {
    id: 1,
    name: 'John',
    email: 'john@example.com',
    role: 'admin',
    permissions: ['read', 'write', 'delete'],
};
TS,
        'public_content_slug' => 'typescript-interfaces',
        'public_content_index' => false,
    ],

    [
        'name' => 'typescript-generics.ts',
        'type' => 'typescript',
        'content' => <<<'TS'
// Generic function
function getItemById<T>(items: T[], id: number): T | undefined {
    return items.find((item: any) => item.id === id);
}

// Generic class
class Repository<T> {
    private items: T[] = [];

    add(item: T): void {
        this.items.push(item);
    }

    getAll(): T[] {
        return this.items;
    }

    getById(id: number): T | undefined {
        return this.items.find((item: any) => item.id === id);
    }
}

// Generic with constraints
function getValue<T extends { value: string }>(obj: T): string {
    return obj.value;
}

// Usage
const userRepo = new Repository<User>();
userRepo.add({ id: 1, name: 'John', email: 'john@example.com' });
TS,
        'public_content_slug' => 'typescript-generics',
        'public_content_index' => false,
    ],

    [
        'name' => 'typescript-type-annotations.ts',
        'type' => 'typescript',
        'content' => <<<'TS'
// Basic types
let id: number = 1;
let name: string = 'John';
let isActive: boolean = true;
let anything: any = 'anything';

// Union types
let status: 'active' | 'inactive' | 'pending' = 'active';
let value: string | number;

// Array types
let numbers: number[] = [1, 2, 3];
let strings: Array<string> = ['a', 'b', 'c'];

// Tuple
let tuple: [string, number, boolean] = ['John', 30, true];

// Enum
enum Direction {
    Up = 'UP',
    Down = 'DOWN',
    Left = 'LEFT',
    Right = 'RIGHT',
}

// Function return types
function calculate(a: number, b: number): number {
    return a + b;
}

// Function with optional parameters
function greet(name: string, greeting?: string): string {
    return `${greeting || 'Hello'}, ${name}!`;
}
TS,
        'public_content_slug' => 'typescript-type-annotations',
        'public_content_index' => false,
    ],

    [
        'name' => 'typescript-async-operations.ts',
        'type' => 'typescript',
        'content' => <<<'TS'
interface ApiResponse<T> {
    data: T;
    status: number;
    message: string;
}

interface User {
    id: number;
    name: string;
    email: string;
}

// Async function with typed response
async function fetchUser(id: number): Promise<ApiResponse<User>> {
    const response = await fetch(`/api/users/${id}`);

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    return response.json();
}

// Await typed response
async function processUser(): Promise<void> {
    try {
        const response = await fetchUser(1);
        const user: User = response.data;
        console.log(user.name);
    } catch (error) {
        console.error('Error:', error);
    }
}
TS,
        'public_content_slug' => 'typescript-async-operations',
        'public_content_index' => false,
    ],
];
