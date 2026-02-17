<?php
namespace Opengerp\System\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

use OpenApi\Generator;

class CreateOpenapiYamlCommand extends Command
{

    protected static $defaultName = 'openapi-create-yaml';
    protected static $defaultDescription = 'Crea openapi yaml';

    protected string $path_src; // example lib/Gerp

    protected string $path_dest; // destination
    protected string $root; // prefix root path

    public function __construct($root, $path_src, $path_dest = 'public/doc/openapi.yaml')
    {
        $this->root = $root;
        $this->path_src = $path_src;
        $this->path_dest = $path_dest;

        parent::__construct();

    }

    public function requirePhp(string $minVersion = '8.1.0'): void
    {
        if (version_compare(PHP_VERSION, $minVersion, '<')) {
            fwrite(STDERR, "PHP richiesto >= {$minVersion}. Versione attuale: " . PHP_VERSION . "\n");
            exit(2);
        }
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $path = $this->path_src;
        $this->requirePhp('8.1.0');

        // Directory da scansionare
        $scanDir = $this->root . $path;
        if (!is_dir($scanDir)) {
            $output->writeln("<error>Directory {$scanDir} not found</error>");
            return Command::FAILURE;
        }

        // Output

        $outFile = $this->root . $this->path_dest;

        $outDir = dirname($outFile);

        // Crea cartella output se manca
        if (!is_dir($outDir) && !mkdir($outDir, 0775, true) && !is_dir($outDir)) {
            fwrite(STDERR, "Impossibile creare la directory: {$outDir}\n");
            $output->writeln("<error>Directory {$scanDir} not created</error>");
            return Command::FAILURE;
        }

        try {

            $openapi = (new Generator())->generate([$scanDir]); // invece di Generator::scan(...)


            $spec = json_decode($openapi->toJson(), true);

            // ordina alfabeticamente gli schemas
            if (isset($spec['components']['schemas']) && is_array($spec['components']['schemas'])) {
                ksort($spec['components']['schemas'], SORT_NATURAL | SORT_FLAG_CASE);
            }

            //  ordina anche i tags
            if (isset($spec['tags']) && is_array($spec['tags'])) {
                usort($spec['tags'], fn($a, $b) => strcasecmp($a['name'] ?? '', $b['name'] ?? ''));
            }

            // scrivi YAML ordinato
            $yaml = Yaml::dump($spec, 20, 2, Yaml::DUMP_OBJECT_AS_MAP);


            $yaml = preg_replace('/(ApiKeyAuth|BearerAuth):\s*\{\s*\}/', '$1: []', $yaml);


            if ($yaml === '' || $yaml === null) {
                throw new \RuntimeException("Spec OpenAPI vuoto: controlla le annotazioni/attributes.");
            }

            $bytes = file_put_contents($outFile, $yaml);
            if ($bytes === false) {
                throw new \RuntimeException("Scrittura fallita su: {$outFile}");
            }

            $output->writeln("OpenAPI scandir {$scanDir}");
            $output->writeln("OpenAPI generato ({$bytes} bytes)");
            $output->writeln("Salvato in: {$outFile}");


            return Command::SUCCESS;


        } catch (\Throwable $e) {
            $msg = "Errore generazione OpenAPI: " . $e->getMessage();

            $output->writeln("<error>$msg</error>");
            return Command::FAILURE;
        }


    }


}
