<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ModelExtensionModuleAwDbOptimize extends Model
{
    /** Suffix appended to DB_PREFIX to mark module-created indexes (e.g. oc_idx_). */
    private const INDEX_MARK = 'idx_';

    /** Columns considered worth a single-column index in conservative mode. */
    private const WHITELIST = [
        'language_id', 'store_id', 'customer_id', 'customer_group_id',
        'category_id', 'product_id', 'option_id', 'attribute_id',
        'date_added', 'code',
    ];

    /** Low-cardinality flag-like columns excluded from heuristic suggestions. */
    private const EXCLUDE = ['status', 'subtract', 'quantity', 'sort_order'];

    /** Non-indexable column types. */
    private const NON_INDEXABLE = [
        'text', 'tinytext', 'mediumtext', 'longtext',
        'blob', 'tinyblob', 'mediumblob', 'longblob', 'json',
    ];

    /**
     * Known-good index set from the original OpenCart optimization list.
     * Format: table suffix (without DB_PREFIX) => [columns].
     */
    private const CURATED = [
        'product_attribute' => ['attribute_id', 'language_id'],
        'product_description' => ['language_id'],
        'product_image' => ['product_id', 'sort_order'],
        'product_option' => ['product_id', 'option_id'],
        'product_option_value' => ['product_option_id', 'product_id', 'option_id', 'option_value_id', 'subtract', 'quantity'],
        'product_reward' => ['product_id', 'customer_group_id'],
        'product_to_category' => ['category_id'],
        'product_to_store' => ['store_id'],
        'setting' => ['store_id', 'group', 'key', 'serialized'],
        'url_alias' => ['query'],
    ];

    public function analyze(array $options = []): array
    {
        $minRows = (int) ($options['min_rows'] ?? 500);
        $maxPerTable = (int) ($options['max_indexes_per_table'] ?? 8);
        $scope = (string) ($options['scope'] ?? 'all');

        $snapshot = $this->getSchemaSnapshot();

        $recommendations = [];
        $noPk = [];
        $myisam = [];
        $fragmented = [];
        $scanned = 0;

        foreach ($snapshot as $table => $info) {
            if (! $this->inScope($table, $scope)) {
                continue;
            }

            $scanned++;

            if (! $info['has_primary']) {
                $noPk[] = $table;
            }

            if (strtoupper((string) $info['engine']) === 'MYISAM') {
                $myisam[] = [
                    'table' => $table,
                    'sql' => 'ALTER TABLE `' . $table . '` ENGINE=InnoDB',
                ];
            }

            if ($this->isFragmented($info)) {
                $fragmented[] = [
                    'table' => $table,
                    'data_free_mb' => round($info['data_free'] / 1048576, 2),
                    'sql' => 'OPTIMIZE TABLE `' . $table . '`',
                ];
            }

            $tableRecs = $this->buildTableRecommendations($table, $info, $minRows);

            if (count($tableRecs) > $maxPerTable) {
                $tableRecs = array_slice($tableRecs, 0, $maxPerTable);
            }

            foreach ($tableRecs as $rec) {
                $recommendations[] = $rec;
            }
        }

        return [
            'recommendations' => $recommendations,
            'diagnostics' => [
                'no_pk' => $noPk,
                'myisam' => $myisam,
                'fragmented' => $fragmented,
            ],
            'summary' => [
                'tables_scanned' => $scanned,
                'recommendations' => count($recommendations),
                'no_pk' => count($noPk),
                'myisam' => count($myisam),
                'fragmented' => count($fragmented),
            ],
        ];
    }

    private function buildTableRecommendations(string $table, array $info, int $minRows): array
    {
        $recs = [];
        $seen = [];

        $suffix = $this->stripPrefix($table);
        $curatedCols = self::CURATED[$suffix] ?? [];

        // Curated baseline (known-good, ignores row threshold).
        foreach ($curatedCols as $column) {
            if (! isset($info['columns'][$column]) || isset($info['leading'][$column])) {
                continue;
            }

            $recs[] = $this->makeRecommendation($table, $column, 'curated', $info);
            $seen[$column] = true;
        }

        // Heuristic detection (conservative), only for tables above row threshold.
        if ((int) $info['rows'] >= $minRows) {
            foreach ($info['columns'] as $column => $type) {
                if (isset($seen[$column]) || isset($info['leading'][$column])) {
                    continue;
                }

                if (! $this->isCandidateColumn($column, $type)) {
                    continue;
                }

                $recs[] = $this->makeRecommendation($table, $column, 'recommended', $info);
                $seen[$column] = true;
            }
        }

        return $recs;
    }

    private function isCandidateColumn(string $column, string $type): bool
    {
        if (in_array($column, self::EXCLUDE, true)) {
            return false;
        }

        if (in_array(strtolower($type), self::NON_INDEXABLE, true)) {
            return false;
        }

        return $this->endsWith($column, '_id') || in_array($column, self::WHITELIST, true);
    }

    private function makeRecommendation(string $table, string $column, string $confidence, array $info): array
    {
        $indexName = $this->indexName($column);

        return [
            'table' => $table,
            'column' => $column,
            'index_name' => $indexName,
            'confidence' => $confidence,
            'rows' => (int) $info['rows'],
            'current_indexes' => (int) $info['index_count'],
            'sql' => 'ALTER TABLE `' . $table . '` ADD INDEX `' . $indexName . '` (`' . $column . '`)',
        ];
    }

    public function addRecommendedIndex(string $table, string $column): array
    {
        if (! $this->safeIdent($table) || ! $this->safeIdent($column)) {
            return ['ok' => false, 'table' => $table, 'column' => $column, 'error' => 'Invalid identifier'];
        }

        if (! $this->columnExists($table, $column)) {
            return ['ok' => false, 'table' => $table, 'column' => $column, 'error' => 'Column not found'];
        }

        $indexName = $this->indexName($column);
        $sql = 'ALTER TABLE `' . $table . '` ADD INDEX `' . $indexName . '` (`' . $column . '`)';

        $applied = false;

        if (! $this->hasIndex($table, $indexName)) {
            $this->db->query($sql);
            $applied = true;
        }

        return [
            'ok' => true,
            'table' => $table,
            'column' => $column,
            'index' => $indexName,
            'applied' => $applied,
            'sql' => $sql,
        ];
    }

    public function convertEngine(string $table): array
    {
        if (! $this->safeIdent($table)) {
            return ['ok' => false, 'table' => $table, 'error' => 'Invalid identifier'];
        }

        $current = $this->getEngine($table);

        if (strtoupper((string) $current) !== 'MYISAM') {
            return ['ok' => true, 'table' => $table, 'applied' => false, 'engine' => $current];
        }

        $sql = 'ALTER TABLE `' . $table . '` ENGINE=InnoDB';
        $this->db->query($sql);

        return ['ok' => true, 'table' => $table, 'applied' => true, 'sql' => $sql, 'engine' => 'InnoDB'];
    }

    public function optimizeTable(string $table): array
    {
        if (! $this->safeIdent($table)) {
            return ['ok' => false, 'table' => $table, 'error' => 'Invalid identifier'];
        }

        $sql = 'OPTIMIZE TABLE `' . $table . '`';
        $this->db->query($sql);

        return ['ok' => true, 'table' => $table, 'applied' => true, 'sql' => $sql];
    }

    public function listAwIndexes(): array
    {
        $prefix = $this->indexPrefix();

        $sql = "SELECT TABLE_NAME, INDEX_NAME, COLUMN_NAME
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE() AND INDEX_NAME <> 'PRIMARY'
                ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX";

        $rows = $this->db->query($sql)->rows;

        $result = [];

        foreach ($rows as $row) {
            if (strpos($row['INDEX_NAME'], $prefix) !== 0) {
                continue;
            }

            $key = $row['TABLE_NAME'] . '|' . $row['INDEX_NAME'];

            if (! isset($result[$key])) {
                $result[$key] = [
                    'table' => $row['TABLE_NAME'],
                    'index' => $row['INDEX_NAME'],
                    'columns' => [],
                ];
            }

            $result[$key]['columns'][] = $row['COLUMN_NAME'];
        }

        return array_values($result);
    }

    public function dropAwIndexes(): int
    {
        $dropped = 0;

        foreach ($this->listAwIndexes() as $index) {
            $result = $this->dropAwIndex($index['table'], $index['index']);

            if (! empty($result['ok'])) {
                $dropped++;
            }
        }

        return $dropped;
    }

    public function dropAwIndex(string $table, string $index): array
    {
        if (! $this->safeIdent($table) || ! $this->safeIdent($index)) {
            return ['ok' => false, 'table' => $table, 'index' => $index, 'error' => 'Invalid identifier'];
        }

        if (strpos($index, $this->indexPrefix()) !== 0) {
            return ['ok' => false, 'table' => $table, 'index' => $index, 'error' => 'Refusing to drop non-module index'];
        }

        if ($this->hasIndex($table, $index)) {
            $this->db->query('ALTER TABLE `' . $table . '` DROP INDEX `' . $index . '`');
        }

        return ['ok' => true, 'table' => $table, 'index' => $index];
    }

    private function getSchemaSnapshot(): array
    {
        $this->refreshStats();

        $database = "TABLE_SCHEMA = DATABASE()";
        $tables = [];

        $sql = "SELECT TABLE_NAME, ENGINE, TABLE_ROWS, DATA_FREE, DATA_LENGTH
                FROM information_schema.TABLES
                WHERE " . $database . " AND TABLE_TYPE = 'BASE TABLE'";

        foreach ($this->db->query($sql)->rows as $row) {
            $tables[$row['TABLE_NAME']] = [
                'engine' => $row['ENGINE'],
                'rows' => (int) $row['TABLE_ROWS'],
                'data_free' => (int) $row['DATA_FREE'],
                'data_length' => (int) $row['DATA_LENGTH'],
                'columns' => [],
                'leading' => [],
                'index_count' => 0,
                'has_primary' => false,
            ];
        }

        $sql = "SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE
                FROM information_schema.COLUMNS
                WHERE " . $database;

        foreach ($this->db->query($sql)->rows as $row) {
            if (isset($tables[$row['TABLE_NAME']])) {
                $tables[$row['TABLE_NAME']]['columns'][$row['COLUMN_NAME']] = $row['DATA_TYPE'];
            }
        }

        $sql = "SELECT TABLE_NAME, INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX
                FROM information_schema.STATISTICS
                WHERE " . $database;

        $indexNames = [];

        foreach ($this->db->query($sql)->rows as $row) {
            $table = $row['TABLE_NAME'];

            if (! isset($tables[$table])) {
                continue;
            }

            if ((int) $row['SEQ_IN_INDEX'] === 1) {
                $tables[$table]['leading'][$row['COLUMN_NAME']] = true;
            }

            if ($row['INDEX_NAME'] === 'PRIMARY') {
                $tables[$table]['has_primary'] = true;
            }

            $indexNames[$table][$row['INDEX_NAME']] = true;
        }

        foreach ($indexNames as $table => $names) {
            $tables[$table]['index_count'] = count($names);
        }

        return $tables;
    }

    private function refreshStats(): void
    {
        // MySQL 8 caches information_schema table stats (DATA_FREE, TABLE_ROWS) for 24h
        // by default; force live values so the analyzer reflects recent changes.
        try {
            $this->db->query('SET SESSION information_schema_stats_expiry = 0');
        } catch (\Throwable $e) {
            // MySQL < 8 has no such variable - its stats are already live.
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $sql = "SHOW INDEX FROM `" . $table . "` WHERE Key_name = '" . $this->db->escape($indexName) . "'";

        return (bool) $this->db->query($sql)->num_rows;
    }

    private function columnExists(string $table, string $column): bool
    {
        $sql = "SELECT COLUMN_NAME FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = '" . $this->db->escape($table) . "'
                AND COLUMN_NAME = '" . $this->db->escape($column) . "'";

        return (bool) $this->db->query($sql)->num_rows;
    }

    private function getEngine(string $table): string
    {
        $sql = "SELECT ENGINE FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = '" . $this->db->escape($table) . "'";

        $row = $this->db->query($sql)->row;

        return $row['ENGINE'] ?? '';
    }

    private function isFragmented(array $info): bool
    {
        // Reclaimable space worth an OPTIMIZE: over 10 MB free AND over 25% of the data.
        // The absolute floor filters InnoDB's normal free-page overhead (small tables
        // always report some DATA_FREE) so only genuinely bloated tables are flagged.
        return $info['data_free'] > 10485760
            && $info['data_free'] > ($info['data_length'] * 0.25);
    }

    private function inScope(string $table, string $scope): bool
    {
        $prefixed = strpos($table, DB_PREFIX) === 0;

        if ($scope === 'standard') {
            return $prefixed;
        }

        if ($scope === 'custom') {
            return ! $prefixed;
        }

        return true;
    }

    private function stripPrefix(string $table): string
    {
        if (DB_PREFIX !== '' && strpos($table, DB_PREFIX) === 0) {
            return substr($table, strlen(DB_PREFIX));
        }

        return $table;
    }

    private function indexPrefix(): string
    {
        return DB_PREFIX . self::INDEX_MARK;
    }

    private function indexName(string $column): string
    {
        return substr($this->indexPrefix() . $column, 0, 64);
    }

    private function safeIdent(string $name): bool
    {
        return (bool) preg_match('/^[A-Za-z0-9_]+$/', $name);
    }

    private function endsWith(string $haystack, string $needle): bool
    {
        $length = strlen($needle);

        return $length === 0 || substr($haystack, -$length) === $needle;
    }
}
