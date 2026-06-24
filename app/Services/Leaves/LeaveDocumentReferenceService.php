<?php

namespace App\Services\Leaves;

use App\Models\LeaveDocument;

class LeaveDocumentReferenceService
{
    public function next(?int $year = null): string
    {
        $year ??= (int) now()->format('Y');

        $lastReference = LeaveDocument::query()
            ->where('document_reference', 'like', sprintf('NC-CONGES-%d-%%', $year))
            ->orderByDesc('document_reference')
            ->value('document_reference');

        $nextSequence = $this->sequenceFrom($lastReference) + 1;

        return sprintf('NC-CONGES-%d-%05d', $year, $nextSequence);
    }

    private function sequenceFrom(?string $reference): int
    {
        if (! $reference || ! preg_match('/-(\d{5})$/', $reference, $matches)) {
            return 0;
        }

        return (int) $matches[1];
    }
}
