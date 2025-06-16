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
        Schema::table('events', function (Blueprint $table) {
            // Menjamo tip kolone event_start u string (VARCHAR 255)
            $table->string('event_start')->change();
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            // Vraćamo nazad u dateTime ako je potrebno rollback
            $table->dateTime('event_start')->change();
        });
    }

};