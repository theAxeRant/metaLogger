<?php

namespace Theaxerant\Metalogger\Entity;

class NamedLocation {

    protected $name;
    protected $path;

    protected function __construct() {}

    public static function create(array $data): NamedLocation {
        $self = new self();
        $self->name = self::getIfSet($data, 'name', null);
        $self->name = self::getIfSet($data, 'name', null);
        return $self;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getPath(): string {
        return $this->path;
    }

    protected static function getIfSet(array $data, string $key, $default) {
        return $data[$key] ?? $default;
    }

}
