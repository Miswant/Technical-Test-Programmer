<?php

namespace App\Exceptions;

use Exception;

class WorkflowException extends Exception
{
    public static function invalidTransition(string $old, string $new): self
    {
        return new self("Transisi status dari {$old} ke {$new} tidak diizinkan.");
    }

    public static function unauthorizedRole(string $role, string $status): self
    {
        return new self("User dengan role '{$role}' tidak berhak mengubah status ke {$status}.");
    }
}
