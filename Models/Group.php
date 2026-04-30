<?php
declare(strict_types=1);

class Group
{
    public int $id;
    public string $name;

    public function __construct(array $row)
    {
        $this->id = (int)$row['id'];
        $this->name = (string)$row['name'];
    }
}

