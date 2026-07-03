<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

/**
 * Streams a PDF download from a Blade view.
 */
class PdfExporter
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function download(string $filename, string $view, array $data, string $orientation = 'portrait'): Response
    {
        return Pdf::loadView($view, $data)
            ->setPaper('a4', $orientation)
            ->download($filename);
    }
}
