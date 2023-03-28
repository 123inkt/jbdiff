<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Diff\Comparison\Iterables;

use DR\JBDiff\Entity\Range;

/**
 * Represents computed differences between two sequences.
 * <p>
 * All {@link Range} are not empty (have at least one element in one of the sides). Ranges do not overlap.
 * <p>
 * Differences are guaranteed to be 'squashed': there are no two changed or two unchanged {@link Range} with
 * <code>(range1.end1 == range2.start1 && range1.end2 == range2.start2)</code>.
 * @see FairDiffIterableInterface
 * @see DiffIterableUtil::iterateAll(DiffIterableInterface)
 */
interface DiffIterableInterface
{
    /**
     * @return int length of the first sequence
     */
    public function getLength1(): int;

    /**
     * @return int length of the second sequence
     */
    public function getLength2(): int;

    /**
     * @return CursorIteratorInterface<Range>
     */
    public function changes(): CursorIteratorInterface;

    /**
     * @return CursorIteratorInterface<Range>
     */
    public function unchanged(): CursorIteratorInterface;
}
