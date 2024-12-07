<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('ticket_number')->unique();
            $table->string('subject');
            $table->text('description');
            $table->string('priority')->default('medium'); // low, medium, high
            $table->string('status')->default('open'); // open, in_progress, closed
            $table->string('category')->nullable(); // order, product, shipping, etc.
            $table->foreignId('assigned_to')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('last_reply_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('support_tickets');
    }
};
