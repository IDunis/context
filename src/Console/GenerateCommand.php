<?php

namespace Idunis\EventSauce\Console;

use EventSauce\EventSourcing\CodeGeneration\CodeDumper;
use EventSauce\EventSourcing\CodeGeneration\YamlDefinitionLoader;
use Idunis\EventSauce\Exceptions\CodeGenerationFailed;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

final class GenerateCommand extends Command
{
    protected $signature = 'eventsauce:generate';

    protected $description = 'Generate commands and events for aggregate roots.';

    protected $filesystem;

    /**
     * GenerateCommand constructor.
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
    }

    public function handle(): void
    {
        $this->info('Start generating code...');

        collect(config('eventsauce.code_generation'))
            ->reject(function (array $config) {
                return is_null($config['input_yaml_file']);
            })
            ->each(function (array $config) {
                $this->generateCode($config['input_yaml_file'], $config['output_file']);
            });

        $this->info('All done!');
    }

    /**
     * @param string $inputFile
     * @param string $outputFile
     * @throws CodeGenerationFailed
     */
    private function generateCode(string $inputFile, string $outputFile)
    {
        if (! file_exists($inputFile)) {
            throw CodeGenerationFailed::definitionFileDoesNotExist($inputFile);
        }

        $loadedYamlContent = (new YamlDefinitionLoader())->load($inputFile);
        $phpCode = (new CodeDumper())->dump($loadedYamlContent);

        $this->filesystem->put($outputFile, $phpCode);

        $this->warn("Written code to `{$outputFile}`");
    }
}
