<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_activities', function (Blueprint $table) {
            $table->id();
            $table->string('message_id', 100);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('action', 50); // status_changed, assigned, note_added, priority_changed, erp_synced, viewed, followed_up, etc.
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();

            $table->index('message_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');

            $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_activities');
    }
};
