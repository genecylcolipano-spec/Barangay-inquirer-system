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
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('tag')->default('info')->after('content'); // today, featured, success, warning, etc.
            $table->string('priority')->default('normal')->after('tag'); // high, normal, low
            $table->string('category')->default('general')->after('priority'); // maintenance, feature, policy, event, etc.
            $table->date('announcement_date')->nullable()->after('category'); // custom date for display
            $table->boolean('show_on_homepage')->default(false)->after('announcement_date'); // show in homepage latest announcements
            $table->integer('display_order')->default(0)->after('show_on_homepage'); // order for homepage display
            $table->string('icon')->nullable()->after('display_order'); // fontawesome icon class
            $table->text('excerpt')->nullable()->after('icon'); // short summary for homepage
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['tag', 'priority', 'category', 'announcement_date', 'show_on_homepage', 'display_order', 'icon', 'excerpt']);
        });
    }
};
