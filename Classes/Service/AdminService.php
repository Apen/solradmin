<?php

declare(strict_types=1);

namespace Sng\Solradmin\Service;

class AdminService
{
    /**
     * @param array $settings
     * @return array
     */
    public function buildConnectionsSelect(array $settings): array
    {
        $select = [];
        foreach ($settings as $connectionName => $connection) {
            $select[$connectionName] = $connection['scheme'] . '://' . $connection['host'] . ':' . $connection['port'] . $connection['path'];
        }

        return $select;
    }
}
