<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('document_requests')) {
            Schema::table('document_requests', function (Blueprint $table) {
                // Add new columns if they don't exist
                if (!Schema::hasColumn('document_requests', 'full_name')) {
                    $table->string('full_name')->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('document_requests', 'address')) {
                    $table->text('address')->nullable()->after('full_name');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('document_requests')) {
            Schema::table('document_requests', function (Blueprint $table) {
                $table->dropColumn(['full_name', 'address']);
            });
        }
    }
};
