<?php

namespace AFM\AdvancedLogger\Tests\Unit;

use AFM\AdvancedLogger\Services\LoggerService;
use AFM\AdvancedLogger\Models\LogEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoggerServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LoggerService $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = app(LoggerService::class);
    }

    /** @test */
    public function it_can_log_info_message()
    {
        $this->logger->info('Test message');

        $this->assertDatabaseHas('advanced_logs', [
            'level' => 'info',
            'message' => 'Test message',
        ]);
    }

    /** @test */
    public function it_can_log_with_category()
    {
        $this->logger->category('auth')
            ->info('User logged in', ['user_id' => 123]);

        $this->assertDatabaseHas('advanced_logs', [
            'level' => 'info',
            'category' => 'auth',
            'message' => 'User logged in',
        ]);
    }

    /** @test */
    public function it_can_log_with_context()
    {
        $context = ['user_id' => 123, 'ip' => '192.168.1.1'];
        
        $this->logger->context($context)
            ->error('Test error');

        $log = LogEntry::where('level', 'error')->first();
        
        $this->assertEquals($context, $log->context);
    }

    /** @test */
    public function it_can_log_exception()
    {
        $exception = new \Exception('Test exception');
        
        $this->logger->exception($exception, ['context' => 'test']);

        $log = LogEntry::where('level', 'error')->first();
        
        $this->assertEquals('Exception', $log->exception_class);
        $this->assertEquals('Test exception', $log->exception_message);
        $this->assertNotEmpty($log->stack_trace);
    }

    /** @test */
    public function it_sanitizes_sensitive_data()
    {
        $context = [
            'password' => 'secret123',
            'api_token' => 'abc123',
            'user_id' => 123,
        ];
        
        $this->logger->info('Test with sensitive data', $context);

        $log = LogEntry::where('level', 'info')->first();
        
        $this->assertEquals('[REDACTED]', $log->context['password']);
        $this->assertEquals('[REDACTED]', $log->context['api_token']);
        $this->assertEquals(123, $log->context['user_id']);
    }

    /** @test */
    public function it_can_get_logs_with_filters()
    {
        // Create test logs
        LogEntry::create([
            'level' => 'error',
            'category' => 'auth',
            'message' => 'Login failed',
            'context' => [],
        ]);

        LogEntry::create([
            'level' => 'info',
            'category' => 'api',
            'message' => 'API request',
            'context' => [],
        ]);

        $errorLogs = $this->logger->getLogs(['level' => 'error']);
        
        $this->assertCount(1, $errorLogs->items());
        $this->assertEquals('error', $errorLogs->items()[0]->level);
    }

    /** @test */
    public function it_can_get_statistics()
    {
        // Create test logs
        LogEntry::create(['level' => 'error', 'category' => 'auth', 'message' => 'Error 1', 'context' => []]);
        LogEntry::create(['level' => 'error', 'category' => 'api', 'message' => 'Error 2', 'context' => []]);
        LogEntry::create(['level' => 'info', 'category' => 'auth', 'message' => 'Info 1', 'context' => []]);

        $stats = $this->logger->getStats();

        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(2, $stats['by_level']['error']);
        $this->assertEquals(1, $stats['by_level']['info']);
        $this->assertEquals(2, $stats['by_category']['auth']);
        $this->assertEquals(1, $stats['by_category']['api']);
    }

    /** @test */
    public function it_can_export_logs_as_json()
    {
        LogEntry::create([
            'level' => 'info',
            'category' => 'test',
            'message' => 'Test message',
            'context' => ['key' => 'value'],
        ]);

        $json = $this->logger->exportLogs([], 'json');
        $data = json_decode($json, true);

        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertEquals('Test message', $data[0]['message']);
    }

    /** @test */
    public function it_can_clear_logs()
    {
        // Create test logs
        LogEntry::create(['level' => 'debug', 'category' => 'test', 'message' => 'Debug 1', 'context' => []]);
        LogEntry::create(['level' => 'info', 'category' => 'test', 'message' => 'Info 1', 'context' => []]);

        $deleted = $this->logger->clearLogs(['level' => 'debug']);

        $this->assertEquals(1, $deleted);
        $this->assertDatabaseMissing('advanced_logs', ['level' => 'debug']);
        $this->assertDatabaseHas('advanced_logs', ['level' => 'info']);
    }
}
