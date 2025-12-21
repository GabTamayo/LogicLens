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
        Schema::table('submissions', function (Blueprint $table) {
            $table->text('draft_code')->nullable()->after('code_content');
            $table->text('draft_stdin')->nullable()->after('draft_code');
            $table->timestamp('draft_saved_at')->nullable()->after('draft_stdin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['draft_code', 'draft_stdin', 'draft_saved_at']);
        });
    }
};
