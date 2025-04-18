<?php

namespace App\Jobs;

use Filament\Actions\Exports\Enums\Contracts\ExportFormat;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Exports\Exporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CustomExportCompletion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public bool $deleteWhenMissingModels = true;

    protected Exporter $exporter;

    public function __construct(
        protected Export $export,
        protected array $columnMap,
        protected array $formats = [],
        protected array $options = [],
    ) {
        $this->exporter = $this->export->getExporter($this->columnMap, $this->options);
    }

    public function handle(): void
    {
        $this->export->touch('completed_at');

        // Tidak ada notifikasi otomatis di sini
        // Karena kamu ingin mengontrol notifikasi secara manual di AppServiceProvider
    }
}
