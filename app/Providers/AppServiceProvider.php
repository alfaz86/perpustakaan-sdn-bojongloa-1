<?php

namespace App\Providers;

use App\Jobs\CustomExportCompletion;
use Filament\Actions\Exports\Jobs\ExportCompletion;
use Filament\Actions\Exports\Models\Export;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ExportCompletion::class, CustomExportCompletion::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $driver = DB::connection()->getDriverName();
        config(['database.like_operator' => $driver === 'pgsql' ? 'ilike' : 'like']);

        FilamentAsset::register([
            Js::make('custom-script', resource_path('js/custom.js')),
        ]);

        FilamentView::registerRenderHook(
            PanelsRenderHook::SIDEBAR_NAV_END,
            fn(): string => Blade::render('<livewire:action-shortcuts />'),
        );

        FilamentIcon::register([
            'panels::topbar.open-database-notifications-button' => view('filament.icons.database-notification'),
        ]);

        Export::updated(function (Export $export) {
            // hanya lanjutkan kalau export sudah selesai dan ada nama file
            if ($export->completed_at === null || !$export->file_name) {
                return;
            }

            $disk = $export->file_disk;
            $filePath = $export->file_name . '.xlsx';
            $absPath = Storage::disk($disk)->path("filament_exports/{$export->id}/{$filePath}");

            if (!file_exists($absPath)) {
                Log::warning('File Tidak Ditemukan', ['path' => $absPath, 'export_id' => $export->id]);
                return;
            }

            try {
                if (DB::getDriverName() === 'pgsql') {
                    // buka sebagai resource biner agar PDO/pg menerima sebagai bytea LOB
                    $resource = fopen($absPath, 'rb');
                    if ($resource === false) {
                        Log::warning('Gagal membuka file untuk dibaca (fopen)', ['path' => $absPath]);
                        return;
                    }

                    // update langsung via query supaya tidak memicu event lagi
                    DB::table('exports')
                        ->where('id', $export->id)
                        ->update(['file_data' => $resource]);

                    // tutup resource setelah update
                    if (is_resource($resource)) {
                        fclose($resource);
                    }
                } else {
                    // MySQL: ambil seluruh konten sebagai string
                    $binary = file_get_contents($absPath);
                    DB::table('exports')
                        ->where('id', $export->id)
                        ->update(['file_data' => $binary]);
                }

                // log ukuran file yang disimpan (opsional)
                $size = filesize($absPath) ?: null;
                Log::info('File data disimpan pada table exports', ['export_id' => $export->id, 'path' => $absPath, 'size' => $size]);
            } catch (\Throwable $e) {
                Log::error('Gagal menyimpan file_data untuk export', ['export_id' => $export->id, 'error' => $e->getMessage()]);
            }

            // Sending notifications
            $recipient = auth()->user();

            Notification::make()
                ->title('File Export Selesai')
                ->body('File export Anda telah selesai diproses dan siap diunduh.')
                ->icon('heroicon-o-check-circle')
                ->iconColor('success')
                ->actions([
                    Action::make('download_xlsx')
                        ->label('Unduh File')
                        ->url(route('report.export.download', [
                            'export' => $export->id,
                        ]))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('primary'),
                ])
                ->sendToDatabase($recipient, isEventDispatched: false);
        });
    }
}
