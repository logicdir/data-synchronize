<?php

namespace logicdir\DataSynchronize\Contracts\Importer;

interface WithMapping
{
    public function map(mixed $row): array;
}

