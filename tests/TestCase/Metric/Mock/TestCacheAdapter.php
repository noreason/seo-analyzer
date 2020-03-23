<?php

namespace Tests\TestCase\Metric\Mock;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Exception\InvalidArgumentException;

class TestCacheAdapter extends FilesystemAdapter
{
    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        throw new InvalidArgumentException();
    }
}
