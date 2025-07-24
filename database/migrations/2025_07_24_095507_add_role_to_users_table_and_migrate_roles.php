<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; 

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'is_admin')) {
        DB::table('users')->where('is_admin', true)->update(['role' => 'admin']);
    }
        if (Schema::hasColumn('users', 'is_seller')) { 
        DB::table('users')->where('is_seller', true)->where('is_admin', false)->update(['role' => 'seller']);
    }

    Schema::table('users', function (Blueprint $table) {
        if (Schema::hasColumn('users', 'is_admin')) {
            $table->dropColumn('is_admin');
        }
        if (Schema::hasColumn('users', 'is_seller')) {
            $table->dropColumn('is_seller');
        }
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('role');
            $table->boolean('is_seller')->default(false)->after('is_admin');
        });

        DB::table('users')->where('role', 'admin')->update(['is_admin' => true, 'is_seller' => true]);
        DB::table('users')->where('role', 'seller')->update(['is_seller' => true]);
        DB::table('users')->where('role', 'user')->update(['is_admin' => false, 'is_seller' => false]);

        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};