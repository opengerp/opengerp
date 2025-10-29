<?php

namespace Opengerp\Database;

final class PdoResult implements DbResult
{
    private int $affected = 0;
    private ?int $numRows = null;
    private bool $freed = false;
    private \PDOStatement $stmt;

    public function __construct(\PDOStatement $stmt)
    {
        $this->stmt = $stmt;
        
        $this->affected = $stmt->rowCount();
    }

    public function fetch(): ?array
    {
        if ($this->freed) return null;
        $row = $this->stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row === false) return null;
        return $row;
    }

    public function num_rows(): int {
        if ($this->numRows !== null) return $this->numRows;
        // Calcoliamo consumando il resto e contando: salviamo in buffer.
        $rows = [];
        while (($r = $this->stmt->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $rows[] = $r;
        }
        $this->numRows = count($rows);
        // Rende disponibili i record buffered a fetch_assoc() successivi:
        $this->stmt->closeCursor(); // chiude lo stream
        // Simuliamo un cursore in-memory per ulteriori fetch:
        $this->it = $rows; $this->itPos = 0;
        return $this->numRows;
    }

    // Buffer interno per fetch dopo num_rows()
    private array $it = [];
    private int $itPos = 0;

    public function affected_rows(): int { return $this->affected; }

    public function free(): void {
        if ($this->freed) return;
        $this->stmt->closeCursor();
        $this->freed = true;
        $this->it = [];
    }

    // Override fetch_assoc per il caso buffered
    public function __call($name, $args) {
        if ($name === 'fetch_assoc' && !empty($this->it)) {
            if ($this->itPos >= count($this->it)) return null;
            return $this->it[$this->itPos++];
        }
        throw new \BadMethodCallException("Metodo $name non supportato");
    }
}

