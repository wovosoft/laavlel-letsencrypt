<?php

namespace Wovosoft\LaravelTypescript\Commands;

use Illuminate\Console\Command;
use Wovosoft\LaravelTypescript\LaravelTypescript;

class TypescriptModelTransformer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'typescript:transform-models';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @throws \Exception
     */
    public function handle(): void
    {
        $transformer = new LaravelTypescript(
            outputPath: resource_path("js/types/models.d.ts"),
            sourceDir: app_path("Models")
        );
        $transformer->run();
    }
}
