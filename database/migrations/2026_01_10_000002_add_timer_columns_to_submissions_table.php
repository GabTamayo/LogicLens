<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('user_id')->comment('When the student started the activity timer');
            $table->timestamp('ending_at')->nullable()->after('started_at')->comment('When the activity timer expires (set once when started)');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'ending_at']);
        });
    }
};
