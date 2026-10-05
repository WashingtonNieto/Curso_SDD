<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availability_settings', function (Blueprint $table) {
            $table->id();
            $table->string('modality', 20)->unique();
            $table->unsignedSmallInteger('session_duration')->default(50);
            $table->boolean('break_enabled')->default(false);
            $table->unsignedSmallInteger('break_minutes')->default(10);
            $table->time('day_start')->default('09:00:00');
            $table->time('day_end')->default('14:00:00');
            $table->boolean('needs_review')->default(false);
            $table->timestamps();
        });

        Schema::create('availability_slots', function (Blueprint $table) {
            $table->id();
            $table->string('modality', 20);
            $table->unsignedTinyInteger('weekday');
            $table->time('start_time');
            $table->unique(['modality', 'weekday', 'start_time']);
        });

        Schema::create('vacation_periods', function (Blueprint $table) {
            $table->id();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacation_periods');
        Schema::dropIfExists('availability_slots');
        Schema::dropIfExists('availability_settings');
    }
};
