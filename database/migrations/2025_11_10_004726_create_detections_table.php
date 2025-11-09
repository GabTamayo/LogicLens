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
        Schema::create('detections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('activity_link_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('submission_a_id')->constrained('submissions')->onDelete('cascade');
            $table->foreignUuid('submission_b_id')->constrained('submissions')->onDelete('cascade');
            $table->decimal('similarity_score', 5, 4);
            $table->timestamps();

            // Prevent duplicate pairs
            $table->unique(['submission_a_id', 'submission_b_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detections');
    }
};
