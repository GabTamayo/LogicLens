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
            $table->foreignId('user_id')->nullable()->after('activity_link_id')->constrained()->onDelete('cascade');
            $table->text('code_content')->nullable()->after('student_no');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropUnique(['activity_link_id', 'student_email']);

            $table->dropColumn(['student_name', 'student_email', 'student_no', 'file_path']);

            $table->foreignId('user_id')->nullable(false)->change();
            $table->text('code_content')->nullable(false)->change();

            $table->unique(['activity_link_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropUnique(['activity_link_id', 'user_id']);

            $table->string('student_name')->after('activity_link_id');
            $table->string('student_email')->after('student_name');
            $table->string('student_no')->after('student_email');
            $table->string('file_path')->after('code_content');

            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'code_content']);

            $table->unique(['activity_link_id', 'student_email']);
        });
    }
};
