<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Resources;

use Wwsrapport\Client\Exception\WwsrapportException;
use Wwsrapport\Client\Http\ApiClient;

final class DocumentsResource
{
    public function __construct(private readonly ApiClient $api) {}

    /**
     * @return array<string, mixed>
     */
    public function list(string $reportId): array
    {
        return $this->api->json('GET', '/reports/'.$this->encode($reportId).'/documents');
    }

    public function downloadWwsReport(string $reportId): string
    {
        return $this->api->binary('/reports/'.$this->encode($reportId).'/documents/wws-report');
    }

    public function downloadImprovementAdvice(string $reportId): string
    {
        return $this->api->binary('/reports/'.$this->encode($reportId).'/documents/improvement-advice');
    }

    public function downloadToFile(string $reportId, string $documentType, string $path): void
    {
        $content = match ($documentType) {
            'wws_report', 'wws-report' => $this->downloadWwsReport($reportId),
            'improvement_advice', 'improvement-advice' => $this->downloadImprovementAdvice($reportId),
            default => throw new \InvalidArgumentException('Unknown WWSrapport document type: '.$documentType),
        };

        if (file_put_contents($path, $content) === false) {
            throw new WwsrapportException('Could not write WWSrapport document to '.$path.'.');
        }
    }

    private function encode(string $value): string
    {
        return rawurlencode($value);
    }
}
