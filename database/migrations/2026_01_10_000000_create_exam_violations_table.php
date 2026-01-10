<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('submission_id')->nullable()->constrained('submissions')->onDelete('cascade');
            $table->string('token')->index();
            $table->string('violation_type'); // tab_switch, fullscreen_exit, keyboard_shortcut, context_menu
            $table->text('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('violated_at');
            $table->timestamps();

            $table->index(['token', 'violation_type']);
            $table->index('violated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_violations');
    }
};
