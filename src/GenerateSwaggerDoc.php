<?php

namespace Laxity7\LaravelSwagger;

use Illuminate\Console\Command;

final class GenerateSwaggerDoc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swagger:generate
                            {--format=json : The format of the output, current options are json and yaml}
                            {--f|filter= : Filter to a specific route prefix, such as /api or /v2/api}
                            {--o|output= : Output file to write the contents to, defaults to stdout, for example: swagger.json}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generates a swagger documentation file for this application';

    public function __construct(
        private Generator $generator
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $filter = $this->option('filter') ?: null;
        $file = $this->option('output') ?: null;
        $format = $this->option('format') ?: 'json';

        $docs = $this->generator->setRouteFilter($filter)->generate();
        $formattedDocs = (new FormatterManager($docs, $format))->format();

        if (!$file) {
            $this->line($formattedDocs);
            return;
        }

        if (!is_writable(dirname($file))) {
            $this->error('Cannot write to file: '.$file);
            return;
        }

        file_put_contents($file, $formattedDocs);
        $this->line('Swagger documentation generated successfully.');
    }
}
