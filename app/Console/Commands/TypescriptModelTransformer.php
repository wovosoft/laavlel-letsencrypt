<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\User;
use Doctrine\DBAL\Exception;
use Illuminate\Console\Command;
use Wovosoft\TypescriptTransformer\TypescriptTransformer;

class TypescriptModelTransformer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:typescript-model-transformer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @throws Exception
     */
    public function handle(): void
    {
        $transformer = new TypescriptTransformer([
            User::class,
            Account::class
        ]);
        $transformer->run();

        $model = new User();
    }
}
