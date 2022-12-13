<?php

namespace Natix\Data\Collection;

/**
 * Description of CollectionInterface
 *
 * @see https://github.com/slimphp/Slim/blob/3.x/Slim/Interfaces/CollectionInterface.php
 */
interface CollectionInterface extends \ArrayAccess, \Countable, \IteratorAggregate
{

    public function set($key, $value);

    public function get($key, $default = null);

    public function replace(array $items);

    public function all();

    public function has($key);

    public function remove($key);

    public function clear();
}
