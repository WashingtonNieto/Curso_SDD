<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->date('session_date');
            $table->string('title');
            $table->longText('content')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('clinical_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinical_entry_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name');
            $table->string('mime', 100);
            $table->unsignedInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_attachments');
        Schema::dropIfExists('clinical_entries');
    }
};
