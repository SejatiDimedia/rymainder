<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Sponsor\Actions\GenerateSponsorImportTemplateAction;
use App\Domain\Sponsor\Actions\ImportSponsorsAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SponsorImportController extends Controller
{
    /**
     * Download the sample import template in XLSX or CSV format.
     */
    public function downloadTemplate(Request $request, GenerateSponsorImportTemplateAction $action): BinaryFileResponse
    {
        $format = $request->query('format', 'xlsx');
        if (! in_array($format, ['xlsx', 'csv'], true)) {
            $format = 'xlsx';
        }

        return $action->execute($format);
    }

    /**
     * Process bulk sponsor import from uploaded spreadsheet file.
     */
    public function import(Request $request, ImportSponsorsAction $importAction): RedirectResponse
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv,txt',
                'max:10240', // Max 10MB
            ],
            'duplicate_mode' => [
                'required',
                'string',
                'in:skip,update',
            ],
        ], [
            'file.required' => 'Please select a spreadsheet file (.xlsx, .xls, or .csv) to import.',
            'file.mimes' => 'Unsupported file format. Please upload an Excel (.xlsx, .xls) or CSV (.csv) file.',
            'file.max' => 'File size exceeds maximum allowable limit of 10 MB.',
            'duplicate_mode.in' => 'Selected duplicate resolution mode is invalid.',
        ]);

        $duplicateMode = $request->input('duplicate_mode', 'skip');
        $result = $importAction->execute($request->file('file'), $duplicateMode);

        if ($result->hasErrors() && $result->importedCount === 0 && $result->updatedCount === 0) {
            return redirect()->route('admin.sponsors.index')
                ->with('error', 'Import failed. No records could be processed. Please review the errors below.')
                ->with('import_errors', $result->errors);
        }

        $parts = [];
        if ($result->importedCount > 0) {
            $parts[] = "{$result->importedCount} new sponsor(s) imported";
        }
        if ($result->updatedCount > 0) {
            $parts[] = "{$result->updatedCount} existing sponsor(s) updated";
        }
        if ($result->skippedCount > 0) {
            $parts[] = "{$result->skippedCount} duplicate(s) skipped";
        }

        $summaryText = implode(', ', $parts) . '.';
        $msg = "Import completed: {$summaryText}";

        $redirect = redirect()->route('admin.sponsors.index')->with('success', $msg);

        if ($result->hasErrors()) {
            $redirect->with('import_errors', $result->errors);
        }

        return $redirect;
    }
}
