<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('domain_id')
                ->references('id')
                ->on('domains')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->dateTime('issue_date');
            $table->dateTime('expiry_date');
            $table->text('private_key');
            $table->text('certificate');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
