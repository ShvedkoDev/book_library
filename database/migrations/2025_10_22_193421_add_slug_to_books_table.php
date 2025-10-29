<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Book;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add slug column
        Schema::table('books', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
        });

        // Generate slugs for existing books
        $books = Book::all();
        foreach ($books as $book) {
            $slug = Str::slug($book->title);

            // Ensure uniqueness by appending ID if slug already exists
            $originalSlug = $slug;
            $counter = 1;
            while (Book::where('slug', $slug)->where('id', '!=', $book->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $book->slug = $slug;
            $book->save();
        }

        // Make slug unique and indexed after populating
        Schema::table('books', function (Blueprint $table) {
            $table->string('slug')->unique()->change();
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropColumn('slug');
        });
    }
};
