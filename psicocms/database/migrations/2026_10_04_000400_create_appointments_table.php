<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('modality', 20);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->unsignedSmallInteger('break_minutes')->default(0);
            $table->string('status', 20)->default('confirmada');
            $table->string('source', 20)->default('web');
            $table->text('reason')->nullable();
            $table->text('internal_notes')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->uuid('public_token')->unique();
            $table->timestamp('seen_at')->nullable();
            $table->timestamps();
            $table->index('starts_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
