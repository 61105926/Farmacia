<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 50)->nullable()->after('email');
            $table->string('document_number', 50)->nullable()->after('phone');
            // Branch FK will be added later when branches table is created
            $table->unsignedBigInteger('branch_id')->nullable()->after('document_number');
            $table->string('avatar')->nullable()->after('branch_id');
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active')->after('avatar');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->integer('failed_login_attempts')->default(0)->after('last_login_ip');
            $table->boolean('must_change_password')->default(false)->after('failed_login_attempts');
            $table->softDeletes()->after('updated_at');

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'document_number',
                'branch_id',
                'avatar',
                'status',
                'last_login_at',
                'last_login_ip',
                'failed_login_attempts',
                'must_change_password',
                'deleted_at'
            ]);
        });
    }
};
