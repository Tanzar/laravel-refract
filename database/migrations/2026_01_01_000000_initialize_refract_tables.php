<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refract_splitters', function (Blueprint $table) {
            $table->id();
            $table->string('splitter_type');
            $table->string('model_type');
            $table->unsignedBigInteger('bands_count')->default(0);
            $table->string('encoded_params');
        });

        Schema::create('refract_params', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('raw_value');
            $table->date('date_value')->nullable();
            $table->integer('int_value')->nullable();
            $table->float('float_value')->nullable();
            $table->string('string_value')->nullable();
            $table->boolean('bool_value')->nullable();

            $table->unique(['type', 'raw_value']);
        });

        Schema::create('refract_model_bands', function(Blueprint $table) {
            $table->unsignedBigInteger('model_id');
            $table->foreignId('splitter_id')->constrained('refract_splitters');
            $table->unsignedBigInteger('band_index');
            $table->float('current_value');

            $table->primary([ 'model_id', 'splitter_id' ]);
        });

        Schema::create('refract_bands', function(Blueprint $table) {
            $table->foreignId('splitter_id')->constrained('refract_splitters');
            $table->unsignedBigInteger('band_index');
            $table->string('signature_hash');
            $table->float('current_value');

            $table->primary([ 'splitter_id', 'band_index' ]);
        });

        Schema::create('refract_bands_params', function(Blueprint $table) {
            $table->foreignId('splitter_id')->constrained('refract_splitters');
            $table->unsignedBigInteger('band_index');
            $table->foreignId('param_id')->constrained('refract_params');
            $table->string('key_name');

            $table->primary([ 'splitter_id', 'band_index', 'param_id', 'key_name' ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refract_model_bands');
        Schema::dropIfExists('refract_bands');
        Schema::dropIfExists('refract_bands_params');
        Schema::dropIfExists('refract_splitters');
        Schema::dropIfExists('refract_params');
    }
};
