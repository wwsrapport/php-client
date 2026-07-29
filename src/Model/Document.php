<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Model;

final class Document extends ApiObject
{
    public function id(): ?string
    {
        return $this->string('id');
    }

    public function type(): ?string
    {
        return $this->string('type');
    }

    public function status(): ?string
    {
        return $this->string('status');
    }

    public function filename(): ?string
    {
        return $this->string('filename');
    }

    public function downloadUrl(): ?string
    {
        return $this->string('download_url');
    }
}
