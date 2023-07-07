<?php

declare(strict_types=1);

namespace Blinq\Synth;

use Blinq\Synth\Livewire\ApproveWrite;
use Blinq\Synth\Livewire\AttachFiles;
use Blinq\Synth\Livewire\Chat;
use Blinq\Synth\Livewire\Modify;
use Blinq\Synth\Livewire\SynthSystemStats;
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

        Livewire::component('synth-chat', Chat::class);
        Livewire::component('synth-modify', Modify::class);
        Livewire::component('synth-attach-files', AttachFiles::class);
        Livewire::component('synth-system-stats', SynthSystemStats::class);
        Livewire::component('synth-approve-write', ApproveWrite::class);

    }
}
