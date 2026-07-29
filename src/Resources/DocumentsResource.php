<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Resources;

use Wwsrapport\Client\Exception\WwsrapportException;
use Wwsrapport\Client\Http\ApiClient;
use Wwsrapport\Client\Model\Document;

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

    /**
     * @return array<int, Document>
     */
    public function listObjects(string $reportId): array
    {
        return array_map(
            static fn (array $item): Document => Document::fromArray($item),
            $this->dataList($this->list($reportId)),
        );
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

    /**
     * @param  array<string, mixed>  $response
     * @return array<int, array<string, mixed>>
     */
    private function dataList(array $response): array
    {
        $data = $response['data'] ?? $response;

        if (! is_array($data)) {
            return [];
        }

        return array_values(array_filter($data, 'is_array'));
    }
}
