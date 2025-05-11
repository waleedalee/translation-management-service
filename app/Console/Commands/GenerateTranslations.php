<?php

namespace App\Console\Commands;

use App\Models\Translation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:generate {count=100000} {--chunk=1000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a large number of translation records for performance testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->argument('count');
        $chunkSize = (int) $this->option('chunk');
        
        $this->info("Generating {$count} translation records...");
        
        // Display progress bar
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        
        // Temporarily disable key uniqueness check
        DB::statement('SET SESSION unique_checks=0');
        DB::statement('SET SESSION foreign_key_checks=0');
        
        // Use database transactions for better performance
        DB::beginTransaction();
        
        try {
            // Generate records in chunks to avoid memory issues
            $chunks = ceil($count / $chunkSize);
            
            for ($i = 0; $i < $chunks; $i++) {
                $currentChunkSize = min($chunkSize, $count - ($i * $chunkSize));
                
                if ($currentChunkSize <= 0) {
                    break;
                }
                
                $records = Translation::factory()->count($currentChunkSize)->make();
                
                // Insert in chunks
                $dataToInsert = [];
                foreach ($records as $record) {
                    $dataToInsert[] = [
                        'key' => $record->key,
                        'locale' => $record->locale,
                        'content' => $record->content,
                        'tags' => json_encode($record->tags),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                
                // Use chunk insert for better performance
                DB::table('translations')->insert($dataToInsert);
                
                // Update progress bar
                $bar->advance($currentChunkSize);
            }
            
            DB::commit();
            
            // Re-enable constraints
            DB::statement('SET SESSION unique_checks=1');
            DB::statement('SET SESSION foreign_key_checks=1');
            
            $bar->finish();
            $this->newLine();
            $this->info("Successfully generated {$count} translation records!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Re-enable constraints
            DB::statement('SET SESSION unique_checks=1');
            DB::statement('SET SESSION foreign_key_checks=1');
            
            $this->error("An error occurred: " . $e->getMessage());
        }
    }
} 