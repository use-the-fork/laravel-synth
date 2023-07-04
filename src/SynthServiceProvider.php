<?php

namespace Blinq\Synth;

use Blinq\Synth\Commands\SynthCommand;
use Blinq\Synth\Controllers\SynthController;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SynthServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        // Inlcude the Helpers/* files
        foreach (glob(__DIR__.'/Helpers/*.php') as $file) {
            require_once $file;
        }
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('synth')
            ->hasConfigFile()
            // ->hasViews()
            // ->hasMigration('create_synth_table')
            ->hasCommand(SynthCommand::class);
    }

    public function boot(): void
    {
        parent::boot();

        $this->app->singleton(
            abstract: SynthController::class,
            concrete: fn () => new SynthController(
                mainMenu: new MainMenu(),
                modules: new Modules(),
                functions: [
                    'save_files' => new Functions\SaveFilesFunction(),
                    'need_documentation' => new Functions\NeedClassFunction(),
                    'need_class' => new Functions\NeedDocumentationFunction(),
                ],
            ),
        );
    }
}
