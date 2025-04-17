<?php

namespace App\Http\Controllers;

use App\Models\LateFeeReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function lateFeeReceiptDownload($id)
    {
        $receipt = LateFeeReceipt::findOrFail($id);

        $contentType = $this->getMimeType($receipt->file_path);

        return Response::make($receipt->file_data, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="' . $receipt->file_path . '"',
        ]);
    }

    private function getMimeType($fileName)
    {
        return match (pathinfo($fileName, PATHINFO_EXTENSION)) {
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv'  => 'text/csv',
            default => 'application/octet-stream',
        };
    }
}
