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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
            $table->date('complaint_date');
            $table->text('problem_description');
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->integer('quantity_complained')->default(1);
            $table->string('photo');
            $table->text('lost_inspection')->nullable();
            $table->text('occured')->nullable();
            $table->date('date_of_closed')->nullable();
            $table->timestamps();   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
