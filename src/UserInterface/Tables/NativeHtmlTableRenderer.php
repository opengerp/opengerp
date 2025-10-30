<?php

namespace Opengerp\UserInterface\Tables;

class NativeHtmlTableRenderer
{
    public function render(Table $t): string
    {
        $esc = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $h = [];

        $h[] = '<table>';
        if ($t->caption() !== null) {
            $h[] = '<caption>'.$esc($t->caption()).'</caption>';
        }

        // thead
        $h[] = '<thead><tr>';
        foreach ($t->columns as $col) {
            $h[] = '<th>'.$esc($col->label).'</th>';
        }
        $h[] = '</tr></thead>';

        // tbody
        $h[] = '<tbody>';

        $rows = $t->getRows();

        if (!$rows) {

            $h[] = '<tr><td colspan="'.count($t->columns).'">'.$esc($t->emptyText()).'</td></tr>';

        } else {
            foreach ($rows as $row) {
                $h[] = '<tr>';
                foreach ($t->columns as $col) {
                    $value = $row[$col->key] ?? null;
                    $out = $col->formatter ? ($col->formatter)($value, $row) : $value;
                    $h[] = '<td>'.($col->escape ? $esc((string)$out) : (string)$out).'</td>';
                }
                $h[] = '</tr>';
            }
        }
        $h[] = '</tbody>';

        $h[] = '</table>';

        return implode('', $h);
    }
}