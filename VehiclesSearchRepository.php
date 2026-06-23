<?php

/**
 * VehiclesSearchRepository
 *
 * Tabla: {$wpdb->prefix}vehicles_search
 */

final class VehiclesSearchRepository
{
    private wpdb $db;
    private string $table;
    private string $tableCategory;

    public function __construct(?wpdb $db = null)
    {
        global $wpdb;
        $this->db    = $db ?: $wpdb;
        $this->table = $this->db->prefix . 'vehicles_search';
        $this->tableCategory = $this->db->prefix . 'vehicles_category';
    }

    public function list(array $args = []): array
    {
        $defaults = [
            's'               => '',
            'vehicle_type'    => '',
            'lot_number'      => '',
            'auction_date_from' => '',
            'auction_date_to'   => '',
            'orderby'         => 'id',
            'order'           => 'DESC',
            'paged'           => 1,
            'per_page'        => 25,
        ];
        $a = array_merge($defaults, $args);

        $paged    = max(1, (int) $a['paged']);
        $perPage  = max(1, min(200, (int) $a['per_page']));
        $offset   = ($paged - 1) * $perPage;

        // Whitelist orderby
        $allowedOrderby = ['id', 'vehicle_id', 'post_title', 'lot_number', 'auction_date', 'vehicle_type', 'model_id'];
        $orderby = in_array($a['orderby'], $allowedOrderby, true) ? $a['orderby'] : 'id';

        $order = strtoupper((string) $a['order']);
        $order = in_array($order, ['ASC', 'DESC'], true) ? $order : 'DESC';

        $where = [];
        $params = [];

        if ($a['vehicle_type'] !== '') {
            $where[]  = "vehicle_type = %s";
            $params[] = (string) $a['vehicle_type'];
        }

        if ($a['lot_number'] !== '') {
            $where[]  = "lot_number = %s";
            $params[] = (string) $a['lot_number'];
        }

        // Ojo: auction_date es VARCHAR en tu tabla. Esto funciona si guardas formato ISO 'YYYY-mm-dd ...'
        if ($a['auction_date_from'] !== '') {
            $where[]  = "auction_date >= %s";
            $params[] = (string) $a['auction_date_from'];
        }
        if ($a['auction_date_to'] !== '') {
            $where[]  = "auction_date <= %s";
            $params[] = (string) $a['auction_date_to'];
        }

        if ($a['s'] !== '') {
            $like = '%' . $this->db->esc_like((string) $a['s']) . '%';
            $where[]  = "(post_title LIKE %s OR post_content LIKE %s OR lot_number LIKE %s)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        // Total
        $sqlCount = "SELECT COUNT(*) FROM {$this->table} {$whereSql}";
        $total = $params
            ? (int) $this->db->get_var($this->db->prepare($sqlCount, $params))
            : (int) $this->db->get_var($sqlCount);

        // Items
        $sqlItems = "SELECT * FROM {$this->table} {$whereSql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        $paramsItems = array_merge($params, [$perPage, $offset]);

        $items = $this->db->get_results($this->db->prepare($sqlItems, $paramsItems), ARRAY_A) ?: [];

        return [
            'items'    => $items,
            'total'    => $total,
            'pages'    => (int) ceil($total / $perPage),
            'paged'    => $paged,
            'per_page' => $perPage,
        ];
    }

    private function prepareCompat(string $query, array $params): string
    {
        if (empty($params)) {
            return $query;
        }

        // wpdb::prepare espera argumentos sueltos, no un array.
        return call_user_func_array([$this->db, 'prepare'], array_merge([$query], $params));
    }

    public function search(array $args): array
    {
        $defaults = [
            'q'        => '',
            'lots'     => '',
            'order_by' => '',
            'make_id'  => 0,
            'model_id' => 0,
            'auction_id' => 0,
            'category_id' => 0,
            'status'   => '',
            'per_page' => 48,
            'page'     => 1,
            'debug'    => true, // <-- ponlo true temporalmente
        ];
        $a = array_merge($defaults, $args);

        $perPage = max(1, min(200, (int) $a['per_page']));
        $page    = max(1, (int) $a['page']);
        $offset  = ($page - 1) * $perPage;

        [$whereSql, $params] = $this->buildWhere($a);
        $orderSql            = $this->buildOrder((string) $a['order_by']);

        // COUNT
        $sqlCount = "SELECT COUNT(*) FROM {$this->table} s {$whereSql}";
        $preparedCount = $this->prepareCompat($sqlCount, $params);
        $total = (int) $this->db->get_var($preparedCount);

        $postsTable = $this->db->posts; // wp_posts con prefijo real

        // ITEMS
        $sqlItems = "
            SELECT s.vehicle_id, s.post_title, s.post_content, s.lot_number, s.price, s.auction_date, s.make_id, s.model_id FROM {$this->table} s LEFT JOIN {$postsTable} p ON p.ID = s.vehicle_id {$whereSql} {$orderSql} LIMIT %d OFFSET %d
        ";

        $itemsParams = array_merge($params, [$perPage, $offset]);
        $preparedItems = $this->prepareCompat($sqlItems, $itemsParams);

        if (!empty($a['debug'])) {
            error_log('[VS] COUNT SQL: ' . $preparedCount);
            error_log('[VS] ITEMS SQL: ' . $preparedItems);
        }

        $items = $this->db->get_results($preparedItems, ARRAY_A) ?: [];

        if (!empty($a['debug']) && $this->db->last_error) {
            error_log('[VS] DB ERROR: ' . $this->db->last_error);
        }

        return [
            'items' => is_array($items) ? $items : [],
            'total' => $total,
        ];
    }

    private function buildWhere(array $a): array
    {
        $where  = [];
        $params = [];

        // 1) no borrados
        $where[]  = "s.is_deleted = %d";
        $params[] = 0;

        // 2) search en title/content/lot_number con %...%
        $q = trim((string) ($a['q'] ?? ''));
        if ($q !== '') {
            $like = '%' . $this->db->esc_like($q) . '%';
            $where[]  = "(s.post_title LIKE %s OR s.post_content LIKE %s OR s.lot_number LIKE %s)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        // 3) make_id (si aplica)
        $makeId = (int) ($a['make_id'] ?? 0);
        if ($makeId > 0) {
            $where[]  = "s.make_id = %d";
            $params[] = $makeId;
        }

        // 3b) model_id (si aplica)
        $modelId = (int) ($a['model_id'] ?? 0);
        if ($modelId > 0) {
            $where[]  = "s.model_id = %d";
            $params[] = $modelId;
        }

        // 3c) status (si aplica)
        $status = trim((string) ($a['status'] ?? ''));
        if ($status !== '') {
            $where[]  = "s.status = %s";
            $params[] = $status;
        }

        // 3d) SOLO private-sale en página específica (ID 803)
        if (!empty($a['vehicle_type'])) {
            $where[]  = "s.vehicle_type = %s";
            $params[] = (string) $a['vehicle_type']; // usará "private-sales"
        }

        // 3e) category_id (term_id) usando wp_vehicles_category
        $categoryId = (int) ($a['category_id'] ?? 0);
        if ($categoryId > 0) {
            $where[]  = "EXISTS (SELECT 1 FROM {$this->tableCategory} vc WHERE vc.vehicle_id = s.vehicle_id AND vc.category_id = %d)";
            $params[] = $categoryId;
        }

        // 3f) auction_id (si aplica)
        $auctionId = (int) ($a['auction_id'] ?? 0);
        if ($auctionId > 0) {
            $where[]  = "s.auction_id = %d";
            $params[] = $auctionId;
        }

        // 4) current/past por auction_date (VARCHAR)
        // Si lots viene vacío => NO filtrar por fecha (sirve para private-sale y búsquedas generales)
        $lotsRaw = trim((string)($a['lots'] ?? ''));

        $dtExpr = "COALESCE(
            STR_TO_DATE(s.auction_date, '%%Y-%%m-%%d %%H:%%i:%%s'),
            STR_TO_DATE(s.auction_date, '%%Y-%%m-%%d %%H:%%i')
        )";

        // Si es private-sale, NO tiene sentido filtrar por auction_date
        $vehicleType = trim((string)($a['vehicle_type'] ?? ''));

        if ($lotsRaw !== '' && $vehicleType !== 'private-sale') {
            $lots = ($lotsRaw === 'past') ? 'past' : 'current';

            if ($lots === 'current') {
                $where[] = "(s.auction_date IS NOT NULL AND s.auction_date <> '' AND {$dtExpr} >= NOW())";
            } else {
                $where[] = "(s.auction_date IS NOT NULL AND s.auction_date <> '' AND {$dtExpr} < NOW())";
            }
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        return [$whereSql, $params];
    }

    private function buildOrder(string $orderBy): string
    {
        switch ($orderBy) {
            case 'low-to-high':
                return "ORDER BY CAST(NULLIF(s.price,'') AS DECIMAL(20,2)) ASC, s.vehicle_id ASC";

            case 'high-to-low':
                return "ORDER BY CAST(NULLIF(s.price,'') AS DECIMAL(20,2)) DESC, s.vehicle_id ASC";

            case 'lot':
                return "ORDER BY CAST(NULLIF(s.lot_number,'') AS UNSIGNED) ASC, s.vehicle_id ASC";
            default:
                return "ORDER BY p.menu_order ASC, s.vehicle_id ASC";
        }
    }

    private function prepare(string $sql, array $params): string
    {
        return $params ? $this->db->prepare($sql, $params) : $sql;
    }

    public function table(): string
    {
        return $this->table;
    }

    public function sqlJoin(string $alias = 'vs_search'): string
    {
        return " INNER JOIN {$this->table} {$alias} ON {$alias}.vehicle_id = wp_posts.ID ";
    }

    public function whereStatus(string $alias, string $status): array
    {
        return [
            "{$alias}.status = %s",
            [$status],
        ];
    }

    public function whereAuctionId(string $alias, int $auctionId): array
    {
        return [
            "{$alias}.auction_id = %d",
            [$auctionId],
        ];
    }

    public function whereSpecialist(string $alias, int $userId): array
    {
        return [
            "{$alias}.specialist_id = %d",
            [$userId],
        ];
    }

    public function getVehicleIdsByAuctionId(int $auctionId): array
    {
        if ($auctionId <= 0) {
            return [];
        }

        $sql = "SELECT s.vehicle_id FROM {$this->table} s WHERE s.auction_id = %d AND s.is_deleted = 0";

        return $this->db->get_col(
            $this->db->prepare($sql, $auctionId)
        ) ?: [];
    }

    public function countVehiclesByAuctionId(int $auctionId): int
    {
        if ($auctionId <= 0) {
            return 0;
        }

        $sql = "SELECT COUNT(*) FROM {$this->table} s WHERE s.auction_id = %d AND s.is_deleted = 0";

        return (int) $this->db->get_var(
            $this->db->prepare($sql, $auctionId)
        );
    }
}
