<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Resources;

use Wwsrapport\Client\Http\ApiClient;
use Wwsrapport\Client\Model\CalculationResult;
use Wwsrapport\Client\Model\ImprovementAdvice;
use Wwsrapport\Client\Model\Report;
use Wwsrapport\Client\Model\ReportValidationResult;

final class ReportsResource
{
    public function __construct(private readonly ApiClient $api) {}

    /**
     * @return array<string, mixed>
     */
    public function validate(array $payload): array
    {
        return $this->api->json('POST', '/reports/validate', $payload);
    }

    public function validateObject(array $payload): ReportValidationResult
    {
        return ReportValidationResult::fromArray($this->data($this->validate($payload)));
    }

    /**
     * Create one immutable paid API report. Always pass a stable idempotency key
     * per partner order so retries cannot consume duplicate quota.
     *
     * @return array<string, mixed>
     */
    public function create(array $payload, string $idempotencyKey): array
    {
        return $this->api->json('POST', '/reports', $payload, [], [
            'Idempotency-Key' => $idempotencyKey,
        ]);
    }

    public function createObject(array $payload, string $idempotencyKey): Report
    {
        return Report::fromArray($this->data($this->create($payload, $idempotencyKey)));
    }

    /**
     * @return array<string, mixed>
     */
    public function list(array $query = []): array
    {
        return $this->api->json('GET', '/reports', null, $query);
    }

    /**
     * @return array<int, Report>
     */
    public function listObjects(array $query = []): array
    {
        return array_map(
            static fn (array $item): Report => Report::fromArray($item),
            $this->dataList($this->list($query)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $reportId): array
    {
        return $this->api->json('GET', '/reports/'.$this->encode($reportId));
    }

    public function getObject(string $reportId): Report
    {
        return Report::fromArray($this->data($this->get($reportId)));
    }

    /**
     * @return array<string, mixed>
     */
    public function calculation(string $reportId): array
    {
        return $this->api->json('GET', '/reports/'.$this->encode($reportId).'/calculation');
    }

    public function calculationObject(string $reportId): CalculationResult
    {
        return CalculationResult::fromArray($this->data($this->calculation($reportId)));
    }

    /**
     * @return array<string, mixed>
     */
    public function improvementAdvice(string $reportId): array
    {
        return $this->api->json('GET', '/reports/'.$this->encode($reportId).'/improvement-advice');
    }

    public function improvementAdviceObject(string $reportId): ImprovementAdvice
    {
        return ImprovementAdvice::fromArray($this->data($this->improvementAdvice($reportId)));
    }

    private function encode(string $value): string
    {
        return rawurlencode($value);
    }

    /**
     * @param  array<string, mixed>  $response
     * @return array<string, mixed>
     */
    private function data(array $response): array
    {
        $data = $response['data'] ?? $response;

        return is_array($data) ? $data : [];
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
