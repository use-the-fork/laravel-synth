<?php

declare(strict_types=1);

namespace Blinq\Synth;

use Blinq\Synth\Livewire\AttachFiles;
use Blinq\Synth\Livewire\ChatResponse;
use Blinq\Synth\Livewire\Synth;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SynthServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        // Inlcude the Helpers/* files
        //        foreach (glob(__DIR__ . '/Helpers/*.php') as $file) {
        //            require_once $file;
        //        }
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('synth')
            ->hasConfigFile()
            ->hasViews('synth')
            ->hasRoute('web');
        // ->hasMigration('create_synth_table')
        //->hasCommand(SynthCommand::class);
    }

    public function boot(): void
    {
        parent::boot();

        Livewire::component('synth-chat', Synth::class);
        Livewire::component('synth-attach-files', AttachFiles::class);
        Livewire::component('synth-chat-response', ChatResponse::class);

        //  Livewire::component('synth-chat', Synth::class);
        //
        //        $this->app->singleton(
        //            abstract: SynthController::class,
        //            concrete: fn () => new SynthController(
        //                mainMenu: new MainMenu(),
        //                modules: new Modules(),
        //                functions: [
        //                    'save_files' => new Functions\SaveFilesFunction(),
        //                    'need_documentation' => new Functions\NeedClassFunction(),
        //                    'need_class' => new Functions\NeedDocumentationFunction(),
        //                ],
        //            ),
        //        );
    }
}
