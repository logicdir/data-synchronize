<?php

namespace Logicdir\DataSynchronize\Providers;

use Logicdir\Base\Facades\DashboardMenu;
use Logicdir\Base\Facades\PanelSectionManager as PanelSectionManagerFacade;
use Logicdir\Base\Supports\ServiceProvider;
use Logicdir\Base\Traits\LoadAndPublishDataTrait;
use Logicdir\DataSynchronize\Commands\ClearChunksCommand;
use Logicdir\DataSynchronize\Commands\ExportCommand;
use Logicdir\DataSynchronize\Commands\ExportControllerMakeCommand;
use Logicdir\DataSynchronize\Commands\ExporterMakeCommand;
use Logicdir\DataSynchronize\Commands\ImportCommand;
use Logicdir\DataSynchronize\Commands\ImportControllerMakeCommand;
use Logicdir\DataSynchronize\Commands\ImporterMakeCommand;
use Logicdir\DataSynchronize\Commands\TestLargeExportCommand;
use Logicdir\DataSynchronize\PanelSections\ExportPanelSection;
use Logicdir\DataSynchronize\PanelSections\ImportPanelSection;
use Illuminate\Console\Scheduling\Schedule;

class DataSynchronizeServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this
            ->setNamespace('packages/data-synchronize')
            ->loadAndPublishTranslations()
            ->loadRoutes()
            ->loadAndPublishConfigurations(['data-synchronize'])
            ->loadAndPublishViews()
            ->publishAssets()
            ->registerPanelSection()
            ->registerDashboardMenu();

        if ($this->app->runningInConsole()) {
            $this->commands([
                ImporterMakeCommand::class,
                ExporterMakeCommand::class,
                ImportControllerMakeCommand::class,
                ExportControllerMakeCommand::class,
                ClearChunksCommand::class,
                ExportCommand::class,
                ImportCommand::class,
                TestLargeExportCommand::class,
            ]);

            $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
                $schedule
                    ->command(ClearChunksCommand::class)
                    ->dailyAt('00:00');
            });
        }
    }

    protected function getPath(?string $path = null): string
    {
        return __DIR__ . '/../..' . ($path ? '/' . ltrim($path, '/') : '');
    }

    protected function registerPanelSection(): self
    {
        PanelSectionManagerFacade::group('data-synchronize')->beforeRendering(function () {
            PanelSectionManagerFacade::default()
                ->register(ExportPanelSection::class)
                ->register(ImportPanelSection::class);
        });

        return $this;
    }

    protected function registerDashboardMenu(): self
    {
        DashboardMenu::default()->beforeRetrieving(function () {
            DashboardMenu::make()
                ->registerItem([
                    'id' => 'cms-packages-data-synchronize',
                    'parent_id' => 'cms-core-tools',
                    'priority' => 9000,
                    'name' => 'packages/data-synchronize::data-synchronize.tools.export_import_data',
                    'icon' => 'ti ti-package-import',
                    'route' => 'tools.data-synchronize',
                ]);
        });

        return $this;
    }
}

