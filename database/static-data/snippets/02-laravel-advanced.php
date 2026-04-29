<?php

declare(strict_types=1);

return [
    // =========================
    // Laravel - Advanced Queries
    // =========================
    [
        'name' => 'eloquent-relationships.php',
        'type' => 'php',
        'content' => <<<'PHP'
use App\Models\MyModel;

// Load with relationships
$items = MyModel::query()
    ->with('relationship', 'nested.relationship')
    ->whereHas('relationship', fn($q) => $q->where('status', 'active'))
    ->get();

// Lazy eager loading
$items = MyModel::all();
$items->load('relationship');
PHP,
        'public_content_slug' => 'eloquent-relationships',
        'public_content_index' => false,
    ],

    [
        'name' => 'eloquent-aggregate-functions.php',
        'type' => 'php',
        'content' => <<<'PHP'
use App\Models\MyModel;

// Count, sum, avg, min, max
$count = MyModel::count();
$sum = MyModel::sum('amount');
$average = MyModel::average('rating');
$max = MyModel::max('price');
$min = MyModel::min('stock');

// Grouping with aggregates
$grouped = MyModel::query()
    ->groupBy('category')
    ->selectRaw('category, COUNT(*) as count, SUM(amount) as total')
    ->get();
PHP,
        'public_content_slug' => 'eloquent-aggregate-functions',
        'public_content_index' => false,
    ],

    [
        'name' => 'eloquent-subqueries.php',
        'type' => 'php',
        'content' => <<<'PHP'
use App\Models\MyModel;
use Illuminate\Database\Query\Builder;

// Subquery in where
$items = MyModel::query()
    ->where('category', 'electronics')
    ->whereIn('id', function (Builder $query) {
        $query->select('item_id')
            ->from('purchases')
            ->where('user_id', 1);
    })
    ->get();

// Subquery in select
$items = MyModel::query()
    ->selectSub(function (Builder $query) {
        $query->select('price')
            ->from('products')
            ->limit(1);
    }, 'best_price')
    ->get();
PHP,
        'public_content_slug' => 'eloquent-subqueries',
        'public_content_index' => false,
    ],

    [
        'name' => 'eloquent-batch-operations.php',
        'type' => 'php',
        'content' => <<<'PHP'
use App\Models\MyModel;

// Bulk insert
MyModel::insert([
    ['name' => 'Item 1', 'price' => 100],
    ['name' => 'Item 2', 'price' => 200],
]);

// Bulk update with raw query
DB::table('my_models')
    ->where('status', 'inactive')
    ->update(['updated_at' => now()]);

// Truncate table (delete all rows)
MyModel::truncate();
PHP,
        'public_content_slug' => 'eloquent-batch-operations',
        'public_content_index' => false,
    ],

    [
        'name' => 'eloquent-scopes.php',
        'type' => 'php',
        'content' => <<<'PHP'
use App\Models\MyModel;

// Local scope definition in model:
// public function scopeActive($query)
// {
//     return $query->where('status', 'active');
// }

// Using local scope
$active = MyModel::active()->get();

// Global scope definition:
// protected static function booted()
// {
//     static::addGlobalScope('active', fn($q) => $q->where('status', 'active'));
// }

// Dynamic scope
$items = MyModel::query()
    ->where('category', 'electronics')
    ->when(request('price_min'), fn($q) => $q->where('price', '>=', request('price_min')))
    ->when(request('price_max'), fn($q) => $q->where('price', '<=', request('price_max')))
    ->get();
PHP,
        'public_content_slug' => 'eloquent-scopes',
        'public_content_index' => false,
    ],

    [
        'name' => 'laravel-db-raw-expressions.php',
        'type' => 'php',
        'content' => <<<'PHP'
use Illuminate\Support\Facades\DB;

// Raw SQL expressions
$users = DB::table('users')
    ->selectRaw('id, CONCAT(first_name, " ", last_name) as full_name')
    ->where(DB::raw('YEAR(created_at)'), '=', 2024)
    ->get();

// Raw where clauses
$items = DB::table('items')
    ->where('price', '>', 100)
    ->whereRaw('MONTH(created_at) = ?', [1])
    ->get();

// Raw joins
$results = DB::table('users')
    ->join(DB::raw('(SELECT * FROM posts WHERE status="published") as p'), 'users.id', '=', 'p.user_id')
    ->get();
PHP,
        'public_content_slug' => 'laravel-db-raw-expressions',
        'public_content_index' => false,
    ],

    [
        'name' => 'laravel-pagination.php',
        'type' => 'php',
        'content' => <<<'PHP'
use App\Models\MyModel;

// Simple pagination
$items = MyModel::paginate(15);

// Custom pagination parameters
$items = MyModel::query()
    ->where('status', 'active')
    ->paginate(
        perPage: 20,
        columns: ['id', 'name', 'email'],
        pageName: 'page',
        page: request('page', 1)
    );

// Cursor-based pagination
$items = MyModel::query()
    ->orderBy('id')
    ->cursorPaginate(15);

// Get total count
$total = $items->total();
$current = $items->currentPage();
PHP,
        'public_content_slug' => 'laravel-pagination',
        'public_content_index' => false,
    ],

    // =========================
    // Laravel - Cache
    // =========================
    [
        'name' => 'laravel-cache-operations.php',
        'type' => 'php',
        'content' => <<<'PHP'
use Illuminate\Support\Facades\Cache;

// Store value
Cache::put('key', 'value', now()->addHours(1));

// Store or retrieve
$value = Cache::remember('key', 3600, function () {
    return expensive_operation();
});

// Delete
Cache::forget('key');
Cache::flush();

// Increment/Decrement
Cache::increment('counter');
Cache::decrement('counter', 5);

// Check existence
if (Cache::has('key')) {
    $value = Cache::get('key');
}
PHP,
        'public_content_slug' => 'laravel-cache-operations',
        'public_content_index' => false,
    ],

    // =========================
    // Laravel - Jobs & Queues
    // =========================
    [
        'name' => 'laravel-dispatch-job.php',
        'type' => 'php',
        'content' => <<<'PHP'
use App\Jobs\ProcessPayment;

// Dispatch immediately
ProcessPayment::dispatch($user, $amount);

// Dispatch later
ProcessPayment::dispatch($user, $amount)
    ->delay(now()->addMinutes(10));

// Dispatch with chain
ProcessPayment::withChain([
    new ProcessPayment($user, 100),
    new SendNotification($user),
])->dispatch();

// Synchronous dispatch
ProcessPayment::dispatchSync($user, $amount);
PHP,
        'public_content_slug' => 'laravel-dispatch-job',
        'public_content_index' => false,
    ],

    // =========================
    // Laravel - Collections
    // =========================
    [
        'name' => 'laravel-collection-methods.php',
        'type' => 'php',
        'content' => <<<'PHP'
use Illuminate\Support\Collection;

$items = collect([1, 2, 3, 4, 5]);

// Map, filter, reduce
$doubled = $items->map(fn($item) => $item * 2);
$filtered = $items->filter(fn($item) => $item > 2);
$sum = $items->reduce(fn($carry, $item) => $carry + $item, 0);

// Chunk and key operations
$chunks = $items->chunk(2);
$keyed = $items->keyBy(fn($item) => "item-{$item}");

// Pluck and unique
$names = collect($users)->pluck('name');
$unique = collect($ids)->unique();

// First, last, nth
$first = $items->first();
$last = $items->last();
$nth = $items->nth(2);
PHP,
        'public_content_slug' => 'laravel-collection-methods',
        'public_content_index' => false,
    ],

    // =========================
    // Laravel - Validation
    // =========================
    [
        'name' => 'laravel-custom-validation.php',
        'type' => 'php',
        'content' => <<<'PHP'
use Illuminate\Support\Facades\Validator;

// Create validator instance
$validator = Validator::make($data, [
    'email' => 'required|email',
    'age' => 'required|integer|between:18,100',
    'username' => 'required|unique:users,username',
]);

// Custom validation rule
$validator->extend('uppercase_only', function ($attribute, $value, $parameters, $validator) {
    return $value === strtoupper($value);
});

// Custom messages
$messages = [
    'email.required' => 'Email is mandatory',
    'email.email' => 'Please provide a valid email',
];

$validator = Validator::make($data, $rules, $messages);

if ($validator->fails()) {
    return $validator->errors();
}
PHP,
        'public_content_slug' => 'laravel-custom-validation',
        'public_content_index' => false,
    ],

    // =========================
    // Laravel - Observers
    // =========================
    [
        'name' => 'laravel-model-observers.php',
        'type' => 'php',
        'content' => <<<'PHP'
// Observer class structure:
// public function creating(MyModel $model) { }
// public function created(MyModel $model) { }
// public function updating(MyModel $model) { }
// public function updated(MyModel $model) { }
// public function deleting(MyModel $model) { }
// public function deleted(MyModel $model) { }

use App\Models\MyModel;
use App\Observers\MyModelObserver;

// Register observer in model's booted() method
protected static function booted()
{
    static::observe(MyModelObserver::class);
}

// Or register in service provider
MyModel::observe(MyModelObserver::class);
PHP,
        'public_content_slug' => 'laravel-model-observers',
        'public_content_index' => false,
    ],
];
