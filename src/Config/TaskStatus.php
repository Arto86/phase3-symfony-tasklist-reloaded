<?php

namespace App\Config;

enum TaskStatus: string
{
    case Pending = 'Pending';
    case Completed = 'Completed';
    case Archived = 'Archived';

    public function getOrder(): int {
        return match($this){
            self::Pending => 1,
            self::Completed => 2,
            self::Archived => 3,
        };
    }
}
