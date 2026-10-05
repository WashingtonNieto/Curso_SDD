<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 150)->nullable();
            $table->string('email')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('dni', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('occupation', 150)->nullable();
            $table->string('emergency_contact_name', 150)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('preferred_modality', 20)->nullable();
            $table->string('status', 20)->default('activo');
            $table->string('therapy_type', 150)->nullable();
            $table->text('reason')->nullable();
            $table->longText('notes')->nullable();
            $table->string('source', 20)->default('manual');
            $table->timestamp('privacy_signed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
