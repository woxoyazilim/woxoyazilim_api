<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── messages ──
        if (!Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->string('id', 100)->primary();
                $table->string('name');
                $table->string('email');
                $table->string('phone', 50)->nullable();
                $table->string('company')->nullable();
                $table->string('subject')->nullable();
                $table->text('message');
                $table->string('status', 50)->default('new');
                $table->timestamps();
            });
        }

        // ── demo_requests ──
        if (!Schema::hasTable('demo_requests')) {
            Schema::create('demo_requests', function (Blueprint $table) {
                $table->string('id', 100)->primary();
                $table->string('name');
                $table->string('email');
                $table->string('phone', 50)->nullable();
                $table->string('company')->nullable();
                $table->string('software_name')->nullable();
                $table->string('software_slug')->nullable();
                $table->string('employee_count', 50)->nullable();
                $table->string('service_id', 100)->nullable();
                $table->text('message')->nullable();
                $table->string('status', 50)->default('pending');
                $table->timestamps();
            });
        }

        // ── chat_leads ──
        if (!Schema::hasTable('chat_leads')) {
            Schema::create('chat_leads', function (Blueprint $table) {
                $table->string('id', 100)->primary();
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone', 50)->nullable();
                $table->json('messages')->nullable();
                $table->string('source', 50)->default('chatbot');
                $table->timestamps();
            });
        }

        // ── services ──
        if (!Schema::hasTable('services')) {
            Schema::create('services', function (Blueprint $table) {
                $table->string('id', 100)->primary();
                $table->string('title');
                $table->string('slug')->nullable();
                $table->string('short_description', 500)->nullable();
                $table->text('description')->nullable();
                $table->json('content')->nullable();
                $table->string('icon', 100)->nullable();
                $table->string('image', 500)->nullable();
                $table->string('category', 100)->nullable();
                $table->json('technologies')->nullable();
                $table->json('features')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->string('seo_title')->nullable();
                $table->string('seo_description', 500)->nullable();
                $table->string('seo_keywords', 500)->nullable();
                $table->timestamps();
            });
        }

        // ── pages ──
        if (!Schema::hasTable('pages')) {
            Schema::create('pages', function (Blueprint $table) {
                $table->string('id', 100)->primary();
                $table->string('slug')->unique();
                $table->string('title');
                $table->json('content')->nullable();
                $table->timestamps();
            });
        }

        // ── page_content ──
        if (!Schema::hasTable('page_content')) {
            Schema::create('page_content', function (Blueprint $table) {
                $table->string('slug', 100)->primary();
                $table->json('content')->nullable();
            });
        }

        // ── landing_pages ──
        if (!Schema::hasTable('landing_pages')) {
            Schema::create('landing_pages', function (Blueprint $table) {
                $table->string('id', 100)->primary();
                $table->string('region')->nullable();
                $table->string('service_title')->nullable();
                $table->string('slug')->nullable();
                $table->string('h1')->nullable();
                $table->string('meta_title')->nullable();
                $table->string('meta_description', 500)->nullable();
                $table->json('content')->nullable();
                $table->string('hero_subtitle', 500)->nullable();
                $table->string('cta_text')->nullable();
                $table->string('cta_url', 500)->nullable();
                $table->json('faq')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // ── references_portfolio: eksik sütun ──
        if (Schema::hasTable('references_portfolio') && !Schema::hasColumn('references_portfolio', 'screenshot_requested_at')) {
            Schema::table('references_portfolio', function (Blueprint $table) {
                $table->timestamp('screenshot_requested_at')->nullable()->after('screenshot_updated_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_pages');
        Schema::dropIfExists('page_content');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('services');
        Schema::dropIfExists('chat_leads');
        Schema::dropIfExists('demo_requests');
        Schema::dropIfExists('messages');

        if (Schema::hasColumn('references_portfolio', 'screenshot_requested_at')) {
            Schema::table('references_portfolio', function (Blueprint $table) {
                $table->dropColumn('screenshot_requested_at');
            });
        }
    }
};
