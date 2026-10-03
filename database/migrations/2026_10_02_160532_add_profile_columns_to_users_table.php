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
        $table->renameColumn('name', 'nama');

        $table->string('alamat')->nullable();
        $table->string('no_ktp')->nullable();
        $table->string('no_hp')->nullable();
        $table->string('no_rm', 25)->nullable();
        $table->enum('role', ['admin', 'dokter', 'pasien']);
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn([
            'alamat',
            'no_ktp',
            'no_hp',
            'no_rm',
            'role',
        ]);

        $table->renameColumn('nama', 'name');
    });
}
};
