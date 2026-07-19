<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_notes', function (Blueprint $table) {
            $table->id();
            $table->string('message_id', 100);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->text('content');
            $table->string('type', 30)->default('note'); // note, call_log, follow_up, quote_note
            $table->boolean('is_internal')->default(true);
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();

            $table->index('message_id');
            $table->index('user_id');
            $table->index('is_pinned');
            $table->index('type');

            $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_notes');
    }
};
