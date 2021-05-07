<?php
class metafad_common_helpers_SortedIterator extends SplHeap
{
    public function __construct(Iterator $iterator)
    {
        foreach ($iterator as $item) {
            $this->insert($item);
        }
    }

    protected function compare($value1, $value2)
    {
        return strnatcmp($value2->getRealpath(), $value1->getRealpath());
    }
}
