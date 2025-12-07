<?php

namespace Opengerp\App;

use Gerp_Auth as User;

class UserContext
{
    private static ?User $user = null;

    /**
     * Imposta l'utente corrente.
     */
    public static function setUser(User $user): void
    {
        self::$user = $user;
    }

    public static function getUser(): ?User
    {
        return self::$user;
    }

    public static function hasUser(): bool
    {
        return self::$user !== null;
    }

}
