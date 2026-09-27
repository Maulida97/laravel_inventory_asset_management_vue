<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class DatabaseConnectionTest extends TestCase
{
    /**
     * Test successful MySQL database connection and active schema.
     */
    public function test_can_connect_to_mysql_database(): void
    {
        config(['database.connections.mysql.database' => 'inventory_asset_db']);
        
        $pdo = DB::connection('mysql')->getPdo();
        $this->assertNotNull($pdo);

        $databaseName = DB::connection('mysql')->getDatabaseName();
        $this->assertEquals('inventory_asset_db', $databaseName);

        $result = DB::connection('mysql')->select('SELECT 1 as is_connected');
        $this->assertEquals(1, $result[0]->is_connected);
    }

    /**
     * Test successful Redis connection.
     */
    public function test_can_connect_to_redis(): void
    {
        $ping = Redis::ping();
        $this->assertTrue($ping === true || $ping === 'PONG' || $ping === '+PONG');
    }
}