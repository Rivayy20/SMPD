<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use Illuminate\Database\Seeder;

/**
 * Data contoh katalog untuk development (opsional).
 *
 * Tidak dipanggil oleh DatabaseSeeder. Jalankan manual:
 *     php artisan db:seed --class=CatalogDemoSeeder
 *
 * Data dibuat dengan factory (nama acak), jadi jangan dipakai sebagai data produksi.
 */
class CatalogDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('CatalogDemoSeeder dilewati di environment production.');

            return;
        }

        $categories = collect(['Fiksi', 'Nonfiksi', 'Sains', 'Teknologi', 'Sejarah', 'Agama', 'Anak-anak', 'Referensi'])
            ->map(fn (string $name) => Category::firstOrCreate(['name' => $name]));

        $publishers = Publisher::factory()->count(5)->create();
        $authors = Author::factory()->count(15)->create();

        Book::factory()
            ->count(30)
            ->state(fn () => [
                'category_id' => $categories->random()->id,
                'publisher_id' => $publishers->random()->id,
            ])
            ->create()
            ->each(fn (Book $book) => $book->authors()->attach($authors->random(rand(1, 3))->pluck('id')));
    }
}
