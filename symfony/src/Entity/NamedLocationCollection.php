<?php

namespace Theaxerant\Metalogger\Entity;

class NamedLocationCollection implements \Countable, \Iterator {

    /**
     * @var array<NamedLocation>
     */
    protected $data;

    /**
     * @var int
     */
    protected $position = 0;
    
    public function count(): int {
        return count($this->data);
    }
    
    protected function __construct() {}
    
    public static function create(array $data): NamedLocationCollection {
        $self = new self();
        foreach ($data as $datum) {
            $self->data[] = NamedLocation::create($datum);
        }
        return $self;
    }

    public function current(): NamedLocation {
        return $this->data[$this->position];
    }

    public function next() {
        $this->position++;
    }

    public function key() {
        return $this->position;
    }

    public function valid() {
        return isset($this->data[$this->position]);
    }

    public function rewind() {
        $this->position = 0;
    }
}
