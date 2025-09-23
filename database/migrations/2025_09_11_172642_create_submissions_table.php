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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\ActivityLink::class)->constrained()->onDelete('cascade');
            $table->string('student_name');
            $table->string('student_email');
            $table->string('student_no');
            $table->string('file_path');
            $table->timestamps();
            $table->unique(['activity_link_id', 'student_email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
