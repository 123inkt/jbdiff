<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Diff\Util;

use DR\JBDiff\Diff\Util\LCS\MyersLCS;
use DR\JBDiff\Diff\Util\LCS\PatienceIntLCS;
use DR\JBDiff\Entity\Change\Change;
use DR\JBDiff\Entity\Change\NullChange;
use DR\JBDiff\Entity\EquatableInterface;
use DR\JBDiff\Util\Enumerator;
use function count;

class Diff
{
    /**
     * @param int[]|EquatableInterface[] $objects1
     * @param int[]|EquatableInterface[] $objects2
     *
     * @throws DiffToBigException
     */
    public function buildChanges(array $objects1, array $objects2): ?Change
    {
        $startShift = $this->getStartShift($objects1, $objects2);
        $endCut     = $this->getEndCut($objects1, $objects2, $startShift);

        $change = $this->doBuildChangesFast(count($objects1), count($objects2), $startShift, $endCut);
        if ($change !== null) {
            return $change;
        }

        $enumerator = new Enumerator();
        $ints1      = $enumerator->enumerate($objects1, $startShift, $endCut);
        $ints2      = $enumerator->enumerate($objects2, $startShift, $endCut);

        return $this->doBuildChanges($ints1, $ints2, new LCSChangeBuilder($startShift));
    }

    /**
     * @param int[] $ints1
     * @param int[] $ints2
     *
     * @throws DiffToBigException
     */
    private function doBuildChanges(array $ints1, array $ints2, LCSChangeBuilder $builder): ?Change
    {
        $reindexer = new Reindexer(); // discard unique elements, that have no chance to be matched
        $discarded = $reindexer->discardUnique($ints1, $ints2);

        if (count($discarded[0]) === 0 && count($discarded[1]) === 0) {
            // assert trimmedLength > 0
            $builder->addChange(count($ints1), count($ints2));

            return $builder->getFirstChange();
        }

        if (DiffConfig::USE_PATIENCE_ALG) { // @phpstan-ignore-line
            $patienceIntLCS = new PatienceIntLCS($discarded[0], $discarded[1]);
            $patienceIntLCS->execute();
            $changes = $patienceIntLCS->getChanges();
        } else {
            try {
                $intLCS = new MyersLCS($discarded[0], $discarded[1]);
                $intLCS->executeWithThreshold();
                $changes = $intLCS->getChanges();
            } catch (DiffToBigException) {
                $patienceIntLCS = new PatienceIntLCS($discarded[0], $discarded[1]);
                $patienceIntLCS->execute(true);
                $changes = $patienceIntLCS->getChanges();
            }
        }

        $reindexer->reindex($changes, $builder);

        return $builder->getFirstChange();
    }

    /**
     * @param int[]|EquatableInterface[] $objects1
     * @param int[]|EquatableInterface[] $objects2
     */
    private function getStartShift(array $objects1, array $objects2): int
    {
        $size  = min(count($objects1), count($objects2));
        $index = 0;

        for ($i = 0; $i < $size; $i++) {
            $object1 = $objects1[$i];
            $object2 = $objects2[$i];

            if ($object1 instanceof EquatableInterface && $object2 instanceof EquatableInterface) {
                if ($object1->equals($object2) === false) {
                    break;
                }
            } elseif ($object1 !== $object2) {
                break;
            }
            ++$index;
        }

        return $index;
    }

    /**
     * @param int[]|EquatableInterface[] $objects1
     * @param int[]|EquatableInterface[] $objects2
     */
    private function getEndCut(array $objects1, array $objects2, int $startShift): int
    {
        $length1 = count($objects1);
        $length2 = count($objects2);
        $size    = min($length1, $length2) - $startShift;
        $index   = 0;

        for ($i = 0; $i < $size; $i++) {
            $object1 = $objects1[$length1 - $i - 1];
            $object2 = $objects2[$length2 - $i - 1];

            if ($object1 instanceof EquatableInterface && $object2 instanceof EquatableInterface) {
                if ($object1->equals($object2) === false) {
                    break;
                }
            } elseif ($object1 !== $object2) {
                break;
            }
            ++$index;
        }

        return $index;
    }

    /**
     * @return Change|null
     */
    private function doBuildChangesFast(int $length1, int $length2, int $startShift, int $endCut): ?Change
    {
        $trimmedLength1 = $length1 - $startShift - $endCut;
        $trimmedLength2 = $length2 - $startShift - $endCut;

        if ($trimmedLength1 !== 0 && $trimmedLength2 !== 0) {
            return null;
        }

        if ($trimmedLength1 === 0 && $trimmedLength2 === 0) {
            return new NullChange();
        }

        return new Change($startShift, $startShift, $trimmedLength1, $trimmedLength2);
    }
}
