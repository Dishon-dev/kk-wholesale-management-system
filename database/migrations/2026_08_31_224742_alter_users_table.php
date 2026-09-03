<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('store_id')
                ->nullable()
                ->after('branch_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('phone',30)
                ->nullable()
                ->after('email');

            $table->boolean('is_active')
                ->default(true)
                ->after('password');

            $table->timestamp('last_login_at')
                ->nullable()
                ->after('remember_token');

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();

            $table->dropColumn([
                'last_login_at',
                'is_active',
                'phone'
            ]);

            $table->dropConstrainedForeignId('store_id');
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};
