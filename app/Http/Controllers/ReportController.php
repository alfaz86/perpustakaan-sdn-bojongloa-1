<?php

namespace App\Http\Controllers;

use App\Models\LateFeeReceipt;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function lateFeeReceiptDownload($id)
    {
        $receipt = LateFeeReceipt::findOrFail($id);

        $contentType = $this->getMimeType($receipt->file_path);

        return Response::make($receipt->file_data, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline; filename="' . $receipt->file_path . '"',
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

        if ($export->file_data) {
            return response($export->file_data, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="data-laporan-' . $export->id . '.xlsx"',
            ]);
        }

        abort(404, 'File not found.');
    }

}
