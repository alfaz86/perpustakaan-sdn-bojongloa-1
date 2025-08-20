<?php

namespace App\Http\Controllers;

use App\Models\LateFeeReceipt;
use App\Models\ReportUpload;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function lateFeeReceipt($id)
    {
        $receipt = LateFeeReceipt::findOrFail($id);

        $type = request()->query('type', 'preview');
        $contentType = $this->getMimeType($receipt->file_path);

        $disposition = $type === 'download'
            ? 'attachment'
            : 'inline';

        return Response::make($receipt->file_data, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => $disposition . '; filename="' . $receipt->file_path . '"',
        ]);
    }
    private function getMimeType($fileName)
    {
        return match (pathinfo($fileName, PATHINFO_EXTENSION)) {
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv',
            default => 'application/octet-stream',
        };
    }

    public function exportDownload(Export $export)
    {
        $user = auth()->user();
        $targetUrl = route('report.export.download', ['export' => $export->id]);

        $notification = $user->unreadNotifications
            ->filter(function ($notification) use ($targetUrl) {
                $data = $notification->data;

                if (is_string($data)) {
                    $data = json_decode($data, true);
                }

                return isset($data['actions'][0]['url']) && $data['actions'][0]['url'] === $targetUrl;
            })
            ->first();

        if ($notification) {
            $notification->markAsRead();
        }

        $fileData = $export->file_data;
        if (is_resource($fileData)) {
            $fileData = stream_get_contents($fileData);
        }

        if ($fileData !== false && $fileData !== null) {
            return response($fileData, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="data-laporan-' . $export->id . '.xlsx"',
            ]);
        }

        abort(404, 'File not found.');
    }

    public function uploadReport($id)
    {
        $upload = ReportUpload::findOrFail($id);

        $type = request()->query('type', 'preview');
        $contentType = $this->getMimeType($upload->file_path);

        $disposition = $type === 'download'
            ? 'attachment'
            : 'inline';

        return Response::make($upload->file_data, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => $disposition . '; filename="' . $upload->file_path . '"',
        ]);
    }
}
