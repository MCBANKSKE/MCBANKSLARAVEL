<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('counties', function (Blueprint $table) {
            $table->id();
            $table->string('county_name')->unique();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('counties');
    }
};
