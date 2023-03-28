<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Util;

use DR\JBDiff\Entity\EquatableInterface;

class Enumerator
{
    /** @var array<int, int|EquatableInterface> */
    private array $numbers = [];

    private int $nextNumber = 1;

    /**
     * @param int[]|EquatableInterface[] $objects
     *
     * @return int[]
     */
    public function enumerate(array $objects, int $startShift, int $endCut): array
    {
        $len = count($objects) - $endCut;

        $idx = [];
        for ($i = $startShift; $i < $len; $i++) {
            $idx[] = $this->enumerateObject($objects[$i]);
        }

        return $idx;
    }

    private function enumerateObject(int|EquatableInterface $object): int
    {
        $number = $this->getInt($object);
        if ($number === 0) {
            $number                 = $this->nextNumber++;
            $this->numbers[$number] = $object;
        }

        return $number;
    }

    private function getInt(int|EquatableInterface $object): int
    {
        foreach ($this->numbers as $number => $entry) {
            if ($entry instanceof EquatableInterface && $object instanceof EquatableInterface) {
                if ($entry->equals($object)) {
                    return $number;
                }
            } elseif ($entry === $object) {
                return $number;
            }
        }

        return 0;
    }
}
