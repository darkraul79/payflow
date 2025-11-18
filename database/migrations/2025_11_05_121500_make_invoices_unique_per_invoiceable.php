<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Clean duplicates: keep the latest invoice per (invoiceable_type, invoiceable_id)
        if (Schema::hasTable('invoices')) {
            $duplicates = DB::table('invoices')
                ->select('invoiceable_type', 'invoiceable_id', DB::raw('COUNT(*) as cnt'))
                ->groupBy('invoiceable_type', 'invoiceable_id')
                ->having('cnt', '>', 1)
                ->get();

            foreach ($duplicates as $dup) {
                $idsToKeep = DB::table('invoices')
                    ->where('invoiceable_type', $dup->invoiceable_type)
                    ->where('invoiceable_id', $dup->invoiceable_id)
                    ->orderByDesc('created_at')
                    ->limit(1)
                    ->pluck('id');

                DB::table('invoices')
                    ->where('invoiceable_type', $dup->invoiceable_type)
                    ->where('invoiceable_id', $dup->invoiceable_id)
                    ->whereNotIn('id', $idsToKeep)
                    ->delete();
            }

            // Add a unique index if not exists (DB driver-aware)
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'sqlite') {
                $indexes = collect(DB::select("PRAGMA index_list('invoices')"))->pluck('name')->all();
            } else {
                $indexes = collect(DB::select('SHOW INDEX FROM invoices'))->pluck('Key_name')->all();
            }
            $hasIndex = in_array('invoices_invoiceable_unique', $indexes, true);

            if (! $hasIndex) {
                Schema::table('invoices', function (Blueprint $table) {
                    $table->unique(['invoiceable_type', 'invoiceable_id'], 'invoices_invoiceable_unique');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                // Drop the unique index if it exists
                $table->dropUnique('invoices_invoiceable_unique');
            });
        }
    }
};
