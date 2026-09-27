<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpks', function (Blueprint $table) {
            $table->id();
            $table->string('no_reg')->unique();
            $table->string('registration_number')->nullable()->index();
            $table->string('name');
            $table->string('kan_schema');
            $table->text('scope')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('status')->default('ACTIVE')->index();
            $table->date('certificate_date')->nullable();
            $table->date('expired_at')->nullable()->index();
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->text('pic_notes')->nullable();
            $table->string('drive_url')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpks');
    }
};
