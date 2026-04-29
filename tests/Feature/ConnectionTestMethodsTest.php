<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Connection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConnectionTestMethodsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function itTestsValidSqliteConnection(): void
    {
        $connection = Connection::factory()->create([
            'driver' => 'sqlite',
            'database' => database_path('database.sqlite'),
        ]);

        $result = $connection->test();

        $this->assertTrue($result);
    }

    #[Test]
    public function itReturnsFalseForInvalidSqliteDatabase(): void
    {
        $connection = Connection::factory()->create([
            'driver' => 'sqlite',
            'database' => '/non/existent/path/to/database.sqlite',
        ]);

        $result = $connection->test(catch: true);

        $this->assertFalse($result);
    }

    #[Test]
    public function itSafelyTestsConnectionWithoutThrowingException(): void
    {
        $connection = Connection::factory()->create([
            'driver' => 'sqlite',
            'database' => '/non/existent/path/to/database.sqlite',
        ]);

        $result = $connection->safeTest();

        $this->assertFalse($result);
    }

    #[Test]
    public function itTestsConnectionWithOverrides(): void
    {
        $connection = Connection::factory()->create([
            'driver' => 'sqlite',
            'database' => database_path('database.sqlite'),
        ]);

        $result = $connection->test(
            overrides: ['database' => database_path('database.sqlite')]
        );

        $this->assertTrue($result);
    }

    #[Test]
    public function itValidatesPasswordOverrideInTest(): void
    {
        $connection = Connection::factory()->create([
            'driver' => 'sqlite',
            'database' => database_path('database.sqlite'),
        ]);

        // Test with string password override (should pass through validation)
        $result = $connection->test(
            overrides: ['password' => 'some-password']
        );

        // SQLite doesn't use password, so should still be true
        $this->assertTrue($result);
    }

    #[Test]
    public function itHandlesCustomClosureInTestCatch(): void
    {
        $connection = Connection::factory()->create([
            'driver' => 'sqlite',
            'database' => '/non/existent/path/to/database.sqlite',
        ]);

        $exceptionThrown = null;

        $result = $connection->test(
            catch: function ($exception) use (&$exceptionThrown) {
                $exceptionThrown = $exception;
            }
        );

        $this->assertFalse($result);
        $this->assertNotNull($exceptionThrown);
    }

    #[Test]
    public function itReturnsFalseWhenCatchClosureIsTrue(): void
    {
        $connection = Connection::factory()->create([
            'driver' => 'sqlite',
            'database' => '/non/existent/path/to/database.sqlite',
        ]);

        $result = $connection->test(catch: true);

        $this->assertFalse($result);
    }

    #[Test]
    public function itThrowsExceptionInDebugModeWhenTestFails(): void
    {
        config(['app.debug' => true]);

        $connection = Connection::factory()->create([
            'driver' => 'sqlite',
            'database' => '/non/existent/path/to/database.sqlite',
        ]);

        $this->expectException(\Exception::class);

        $connection->test(catch: null);
    }

    #[Test]
    public function itReturnsFalseInProductionModeWhenTestFails(): void
    {
        config(['app.debug' => false]);
        config(['app.env' => 'production']);

        $connection = Connection::factory()->create([
            'driver' => 'sqlite',
            'database' => '/non/existent/path/to/database.sqlite',
        ]);

        $result = $connection->test(catch: null);

        $this->assertFalse($result);
    }

    #[Test]
    public function itTreatsBooleanFalseAsFalseInCatch(): void
    {
        $connection = Connection::factory()->create([
            'driver' => 'sqlite',
            'database' => '/non/existent/path/to/database.sqlite',
        ]);

        // When catch is false, exceptions are re-thrown in debug mode
        $this->expectException(\Exception::class);

        $connection->test(catch: false);
    }

    #[Test]
    public function itSilentlyHandlesClosureExceptionInTest(): void
    {
        $connection = Connection::factory()->create([
            'driver' => 'sqlite',
            'database' => '/non/existent/path/to/database.sqlite',
        ]);

        $result = $connection->test(
            catch: function ($exception) {
                throw new \RuntimeException('Closure error');
            }
        );

        // Even if closure throws, should return false
        $this->assertFalse($result);
    }
}
