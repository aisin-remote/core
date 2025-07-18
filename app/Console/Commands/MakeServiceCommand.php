<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name : The name of the service}
                           {--force : Overwrite existing service}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $force = $this->option('force');

        $this->createService($name, $force);
    }

    /**
     * Create service class file.
     */
    protected function createService(string $name, bool $force): void
    {
        // Ensure the name has Service suffix
        if (!Str::endsWith($name, 'Service')) {
            $name .= 'Service';
        }

        // Create directory if not exists
        $directory = app_path('Services');
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        // Handle namespaces and paths
        $name = str_replace('/', '\\', $name);
        $segments = explode('\\', $name);
        $className = array_pop($segments);

        $subNamespace = count($segments) ? '\\' . implode('\\', $segments) : '';
        $subDirectory = count($segments) ? DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $segments) : '';

        // Make sure sub-directory exists
        if (!empty($subDirectory)) {
            if (!File::exists($directory . $subDirectory)) {
                File::makeDirectory($directory . $subDirectory, 0755, true);
            }
        }

        $filePath = $directory . $subDirectory . DIRECTORY_SEPARATOR . $className . '.php';

        // Check if file already exists
        if (File::exists($filePath) && !$force) {
            $this->error("Service [{$name}] already exists!");
            return;
        }

        // Create file
        $stub = $this->getStub();
        $stub = str_replace('{{namespace}}', 'App\\Services' . $subNamespace, $stub);
        $stub = str_replace('{{class}}', $className, $stub);

        File::put($filePath, $stub);

        $this->info("Service [{$name}] created successfully.");
    }

    /**
     * Get service stub content.
     */
    protected function getStub(): string
    {
        return <<<'STUB'
<?php

namespace {{namespace}};

class {{class}}
{
      /**
     * Create a new service instance.
     */
    public function __construct()
    {
          //
    }
}
STUB;
    }
}
