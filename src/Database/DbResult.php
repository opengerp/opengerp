<?php

namespace Opengerp\Database;

interface DbResult
{
    /** come mysqli_fetch_assoc(): ritorna riga o null a fine cursore */
    public function fetch(): ?array;

    public function num_rows(): int;     // solo per SELECT (0 altrimenti)
    public function affected_rows(): int; // solo per DML (0 altrimenti)
    public function free(): void;        // libera risorse (no-op con PDO)
}

