<?php

namespace Spatie\TypeScriptTransformer\Tests;

use DateTime;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\TypeScriptTransformer\Collectors\AnnotationCollector;
use Spatie\TypeScriptTransformer\OutputFormatters\ModuleOutputFormatter;
use Spatie\TypeScriptTransformer\Tests\FakeClasses\Test;
use Spatie\TypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\TypeScriptTransformer\Transformers\MyclabsEnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class IntegrationTest extends TestCase
{
    use MatchesSnapshots;

    private function getTransformerConfig(): TypeScriptTransformerConfig
    {
        return TypeScriptTransformerConfig::create()
            ->searchingPath(__DIR__ . '/FakeClasses/Integration')
            ->classPropertyReplacements([
                DateTime::class => 'string',
            ])
            ->transformers([
                MyclabsEnumTransformer::class,
                DtoTransformer::class,
            ])
            ->collectors([
                AnnotationCollector::class,
            ]);
    }

    /** @test */
    public function it_works()
    {
        $temporaryDirectory = (new TemporaryDirectory())->create();

        $transformer = new TypeScriptTransformer(
            $this->getTransformerConfig()
                ->outputFile($temporaryDirectory->path('types.d.ts'))
        );

        $transformer->transform();

        $transformed = file_get_contents($temporaryDirectory->path('types.d.ts'));

        $this->assertMatchesSnapshot($transformed);
    }

    /** @test */
    public function it_can_transform_to_es_modules()
    {
        $temporaryDirectory = (new TemporaryDirectory())->create();

        $transformer = new TypeScriptTransformer(
            $this->getTransformerConfig()
                ->outputFormatter(ModuleOutputFormatter::class)
                ->outputFile($temporaryDirectory->path('types.ts'))
        );

        $transformer->transform();

        $transformed = file_get_contents($temporaryDirectory->path('types.ts'));

        $this->assertMatchesSnapshot($transformed);
    }
}
