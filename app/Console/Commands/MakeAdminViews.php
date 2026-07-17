<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeAdminViews extends Command
{
    protected $signature = 'make:admin-views';
    protected $description = 'Generate admin view folders and blade files';

    public function handle()
    {
        $basePath = resource_path('views/admin');

        $structure = [
            'layouts' => ['app.blade.php', 'sidebar.blade.php', 'header.blade.php'],
            'auth' => ['login.blade.php'],
            'dashboard' => ['index.blade.php'],
            'customers' => ['index.blade.php', 'create.blade.php', 'edit.blade.php', 'show.blade.php'],
            'bookings' => ['index.blade.php', 'all.blade.php', 'cancelled-refunds.blade.php', 'pending-confirmations.blade.php'],
            'travel-partners' => ['index.blade.php', 'create.blade.php', 'edit.blade.php'],
            'content/blog' => ['index.blade.php', 'create.blade.php', 'edit.blade.php'],
            'content/newsletter' => ['index.blade.php', 'subscribers.blade.php'],
            'settings' => ['website.blade.php', 'email.blade.php', 'payment.blade.php'],
        ];

        foreach ($structure as $folder => $files) {
            $folderPath = $basePath . '/' . $folder;
            File::ensureDirectoryExists($folderPath);

            foreach ($files as $file) {
                $filePath = $folderPath . '/' . $file;
                if (!File::exists($filePath)) {
                    File::put($filePath, "<!-- {$file} -->");
                    $this->info("Created: views/admin/{$folder}/{$file}");
                } else {
                    $this->line("Skipped (already exists): views/admin/{$folder}/{$file}");
                }
            }
        }

        $this->info('✅ All admin view folders and files generated successfully.');
        return Command::SUCCESS;
    }
}
