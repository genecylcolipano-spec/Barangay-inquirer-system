<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('announcements')) {
            return;
        }

        if (!Schema::hasColumn('announcements', 'created_by')) {
            // only add the foreign key when the users table actually exists
            if (Schema::hasTable('users')) {
                Schema::table('announcements', function (Blueprint $table) {
                    $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('cascade')->after('content');
                });
            }
        }

        if (!Schema::hasColumn('announcements', 'is_published')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->boolean('is_published')->default(0)->after('created_by');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('announcements')) {
            return;
        }

        if (Schema::hasColumn('announcements', 'is_published')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->dropColumn('is_published');
            });
        }

        if (Schema::hasColumn('announcements', 'created_by')) {
            Schema::table('announcements', function (Blueprint $table) {
                // Try to drop the foreign key first if it exists
                try {
                    $table->dropForeign(['created_by']);
                } catch (\Throwable $e) {
                    // ignore
                }

                $table->dropColumn('created_by');
            });
        }
    }
};
