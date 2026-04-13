<?php
// app/Jobs/GenerateSeriesJob.php
namespace App\Jobs;

use App\Models\WebSeries;
use App\Services\ModelsLabService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateSeriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    protected $series;

    public function __construct(WebSeries $series)
    {
        $this->series = $series;
    }

    public function handle(ModelsLabService $modelsLab)
    {
        $controller = app(WebSeriesController::class);
        $reflection = new \ReflectionMethod($controller, 'processGenerationWithAI');
        $reflection->setAccessible(true);
        $reflection->invoke($controller, $this->series);
    }
}