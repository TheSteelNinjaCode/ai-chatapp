<?php

declare(strict_types=1);

namespace Lib\Prisma\Classes;

use DateTime;

class CareerOptionData
{

    public ?CareerOptionData $_avg = null;
    public ?CareerOptionData $_count = null;
    public ?CareerOptionData $_max = null;
    public ?CareerOptionData $_min = null;
    public ?CareerOptionData $_sum = null;
    public ?int $id;
    public int $code;
    public string $career;
    public string $shift;
    public string $level;
    public string $area;
    public string $description;
    public DateTime|string $createdAt;
    public DateTime|string $updatedAt;

    public function __construct(
        int $code,
        string $career,
        string $shift,
        string $level,
        string $area,
        string $description,
        DateTime|string $createdAt = new DateTime(),
        DateTime|string $updatedAt = new DateTime(),
        ?int $id = null,
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->career = $career;
        $this->shift = $shift;
        $this->level = $level;
        $this->area = $area;
        $this->description = $description;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'career' => $this->career,
            'shift' => $this->shift,
            'level' => $this->level,
            'area' => $this->area,
            'description' => $this->description,
            'createdAt' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
            'updatedAt' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null
        ];
    }
}