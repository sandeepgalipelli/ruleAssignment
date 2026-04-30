<?php
declare(strict_types=1);

class Rule
{
    public int $id;
    public string $name;
    public string $type;

    public function __construct(array $row)
    {
        $this->id = (int)$row['id'];
        $this->name = (string)$row['name'];
        $this->type = (string)$row['type'];
    }
}

