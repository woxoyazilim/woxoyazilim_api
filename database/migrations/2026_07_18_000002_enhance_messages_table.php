<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // CRM fields
            $table->string('priority', 20)->default('normal')->after('status');
            $table->string('assigned_to', 100)->nullable()->after('priority');
            $table->string('source', 50)->default('contact_form')->after('assigned_to');

            // Device & location fields
            $table->string('ip_address', 45)->nullable()->after('source');
            $table->string('user_agent', 500)->nullable()->after('ip_address');
            $table->string('device_type', 30)->nullable()->after('user_agent');
            $table->string('browser', 100)->nullable()->after('device_type');
            $table->string('browser_version', 50)->nullable()->after('browser');
            $table->string('os', 100)->nullable()->after('browser_version');
            $table->string('country', 100)->nullable()->after('os');
            $table->string('country_code', 10)->nullable()->after('country');
            $table->string('region', 100)->nullable()->after('country_code');
            $table->string('city', 100)->nullable()->after('region');
            $table->string('timezone', 100)->nullable()->after('city');
            $table->string('isp', 200)->nullable()->after('timezone');
            $table->string('source_url', 500)->nullable()->after('isp');
            $table->string('referrer_url', 500)->nullable()->after('source_url');
            $table->string('utm_source', 100)->nullable()->after('referrer_url');
            $table->string('utm_medium', 100)->nullable()->after('utm_source');
            $table->string('utm_campaign', 100)->nullable()->after('utm_medium');

            // Timeline fields
            $table->timestamp('follow_up_at')->nullable()->after('utm_campaign');
            $table->timestamp('read_at')->nullable()->after('follow_up_at');
            $table->timestamp('first_contacted_at')->nullable()->after('read_at');
            $table->timestamp('last_action_at')->nullable()->after('first_contacted_at');

            // ERP fields
            $table->string('erp_customer_id', 100)->nullable()->after('last_action_at');
            $table->string('erp_sync_status', 20)->default('pending')->after('erp_customer_id');
            $table->timestamp('erp_synced_at')->nullable()->after('erp_sync_status');
            $table->string('erp_synced_by', 100)->nullable()->after('erp_synced_at');

            // Indexes
            $table->index('status');
            $table->index('priority');
            $table->index('assigned_to');
            $table->index('created_at');
            $table->index('follow_up_at');
            $table->index('erp_sync_status');
            $table->index('country');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['assigned_to']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['follow_up_at']);
            $table->dropIndex(['erp_sync_status']);
            $table->dropIndex(['country']);

            $table->dropColumn([
                'priority', 'assigned_to', 'source',
                'ip_address', 'user_agent', 'device_type', 'browser', 'browser_version', 'os',
                'country', 'country_code', 'region', 'city', 'timezone', 'isp',
                'source_url', 'referrer_url', 'utm_source', 'utm_medium', 'utm_campaign',
                'follow_up_at', 'read_at', 'first_contacted_at', 'last_action_at',
                'erp_customer_id', 'erp_sync_status', 'erp_synced_at', 'erp_synced_by',
            ]);
        });
    }
};
