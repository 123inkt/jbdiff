<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Entity\Change;

class Change
{
    /**
     * @param int         $line0    Lines of file 0 changed here.
     * @param int         $line1    Lines of file 1 changed here.
     * @param int         $deleted  Line number of 1st deleted line.
     * @param int         $inserted Line number of 1st inserted line.
     * @param Change|null $link     Previous or next edit command.
     */
    public function __construct(
        public readonly int $line0,
        public readonly int $line1,
        public readonly int $deleted,
        public readonly int $inserted,
        public ?Change $link = null
    ) {
    }

    public function isNull(): bool
    {
        return false;
    }

    public function __toString()
    {
        return sprintf("change[inserted=%d, deleted=%d, line0=%d, line1=%d]", $this->inserted, $this->deleted, $this->line0, $this->line1);
    }

    /**
     * @return Change[]
     */
    public function toArray(): array
    {
        $result = [];
        for ($current = $this; $current !== null; $current = $current->link) {
            $result[] = $current;
        }

        return $result;
    }
}
