<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('house_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('houses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_type_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('picture')->nullable();
            $table->unsignedSmallInteger('capacity');
            $table->decimal('price_hour', 10, 2);
            $table->decimal('price_day', 10, 2);
            $table->timestamps();
        });

        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->string('addon_name');
            $table->string('picture')->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('price_hour', 10, 2);
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('house_id')->constrained()->restrictOnDelete();
            $table->dateTime('time_start');
            $table->dateTime('time_end');
            $table->decimal('total_price', 10, 2);
            $table->timestamps();

            $table->index(['house_id', 'time_start', 'time_end']);
        });

        Schema::create('bookings_addons', function (Blueprint $table) {
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('addon_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);

            $table->primary(['booking_id', 'addon_id']);
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('status_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('text')->nullable();
            $table->dateTime('review_date')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('bookings_addons');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('addons');
        Schema::dropIfExists('houses');
        Schema::dropIfExists('statuses');
        Schema::dropIfExists('house_types');
    }
};
