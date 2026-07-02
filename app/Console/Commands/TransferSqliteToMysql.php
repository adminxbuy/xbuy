<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransferSqliteToMysql extends Command
{
    protected $signature = 'db:transfer-sqlite';
    protected $description = 'Transfer all data from local SQLite to the configured MySQL database';

    public function handle()
    {
        $this->info('Starting database transfer from SQLite to MySQL...');

        // 1. Get all tables from sqlite_master
        $tables = DB::connection('local_sqlite')
            ->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' AND name != 'migrations'");

        if (empty($tables)) {
            $this->error('No tables found in local SQLite database.');
            return Command::FAILURE;
        }

        // Disable foreign key constraints on destination (MySQL)
        Schema::disableForeignKeyConstraints();

        foreach ($tables as $tableRow) {
            $tableName = $tableRow->name;
            $this->comment("Transferring table: {$tableName}...");

            // Fetch records from sqlite
            $records = DB::connection('local_sqlite')->table($tableName)->get();
            $count = $records->count();

            if ($count === 0) {
                $this->info("Table {$tableName} is empty. Skipping.");
                continue;
            }

            // Clear destination table
            DB::table($tableName)->truncate();

            // Insert records in chunks
            $data = json_decode(json_encode($records->toArray()), true);
            
            // Insert in chunks of 500 records
            foreach (array_chunk($data, 500) as $chunk) {
                DB::table($tableName)->insert($chunk);
            }

            $this->info("Successfully transferred {$count} records for {$tableName}.");
        }

        // Enable foreign key constraints
        Schema::enableForeignKeyConstraints();

        $this->info('Database transfer completed successfully!');
        return Command::SUCCESS;
    }
}
