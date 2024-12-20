<?php

require_once 'global.php';

class Get extends GlobalMethods
{
    private $pdo;
    public function __construct(\PDO $pdo)
    {
        parent::__construct();
        $this->pdo = $pdo;
    }

    //ESSENTIALS
    private function get_records($table = null, $conditions = null, $columns = '*', $customSqlStr = null, $params = [])
    {
        if ($customSqlStr != null) {
            $sqlStr = $customSqlStr;
        } else {
            $sqlStr = "SELECT $columns FROM $table";
            if ($conditions != null) {
                $sqlStr .= " WHERE " . $conditions;
            }
        }
        $result = $this->executeQuery($sqlStr, $params);

        if ($result['code'] == 200) {
            return $this->sendPayload($result['data'], 'success', "Successfully retrieved data.", $result['code']);
        }
        return $this->sendPayload(null, 'failed', "Failed to retrieve data.", $result['code']);
    }

    private function executeQuery($sql, $params = [])
    {
        $data = [];
        $errmsg = "";
        $code = 0;

        try {
            $statement = $this->pdo->prepare($sql);
            if ($statement->execute($params)) {
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                foreach ($result as $record) {
                    // Handle BLOB data
                    if (isset($record['file_data'])) {
                        $record['file_data'] = base64_encode($record['file_data']);
                    }
                    array_push($data, $record);
                }
                $code = 200;
                return array("code" => $code, "data" => $data);
            } else {
                $errmsg = "No data found.";
                $code = 404;
                return array("code" => $code, "errmsg" => $errmsg);
            }
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 403;
        }
        return array("code" => $code, "errmsg" => $errmsg);
    }




    //LOGIN FUNCTIONS
    public function getByEmail(string $email = null): array|false
    {
        if ($email === null) {
            return false;
        }

        $sql = "SELECT * FROM user WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    //ADMIN: GET USERS
    public function get_users($id = null)
    {
        $condition = null;
        if ($id != null) {
            $condition = "id=$id";
        }
        return $this->get_records('users', $condition);
    }

    public function get_units($id = null)
    {
        $search = $_GET['search'] ?? null;
        $status = $_GET['status'] ?? null;
        $floor = $_GET['floor'] ?? null;
        $today = date('Y-m-d');

        $sql = "SELECT 
                u.*,
                CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM leases l 
                        WHERE l.unit_id = u.id 
                        AND l.status = 'active'
                    ) THEN 'occupied'
                    ELSE 'vacant'
                END as status,
                CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM leases l 
                        WHERE l.unit_id = u.id 
                        AND l.status = 'active'
                        AND l.end_date < ?
                    ) THEN 'expired'
                    ELSE 'current'
                END as lease_status
                FROM units u";

        $params = [$today];
        $conditions = [];

        if ($id !== null) {
            $conditions[] = "u.unit_number = ?";
            $params[] = $id;
        }

        if ($search !== null) {
            $conditions[] = "(
                u.unit_number LIKE ? OR
                EXISTS (
                    SELECT 1 FROM leases l 
                    JOIN tenants t ON l.id = t.lease_id 
                    WHERE l.unit_id = u.id AND (
                        t.first_name LIKE ? OR 
                        t.last_name LIKE ?
                    )
                )
            )";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }

        if ($status !== null) {
            if ($status === 'occupied') {
                $conditions[] = "EXISTS (
                    SELECT 1 FROM leases l 
                    WHERE l.unit_id = u.id 
                    AND l.status = 'active'
                )";
            } else if ($status === 'vacant') {
                $conditions[] = "NOT EXISTS (
                    SELECT 1 FROM leases l 
                    WHERE l.unit_id = u.id 
                    AND l.status = 'active'
                )";
            } else if ($status === 'expired') {
                $conditions[] = "EXISTS (
                    SELECT 1 FROM leases l 
                    WHERE l.unit_id = u.id 
                    AND l.status = 'active'
                    AND l.end_date < ?
                )";
                $params[] = $today;
            }
        }

        if ($floor !== null && $floor !== 'all') {
            $conditions[] = "u.floor = ?";
            $params[] = $floor;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY u.unit_number";

        $result = $this->executeQuery($sql, $params);

        if ($result['code'] == 200) {
            $units = $result['data'];

            // For each unit, get its current ACTIVE lease and tenants
            foreach ($units as &$unit) {
                // Get the current active lease only
                $leaseSql = "SELECT 
                                l.id,
                                l.start_date,
                                l.end_date,
                                l.date_renewed,
                                l.rent_amount,
                                d.amount as deposit_amount
                            FROM leases l
                            LEFT JOIN deposits d ON l.id = d.lease_id
                            WHERE l.unit_id = :unit_id
                            AND l.status = 'active'
                            LIMIT 1";

                $leaseResult = $this->executeQuery($leaseSql, ['unit_id' => $unit['id']]);

                if ($leaseResult['code'] == 200 && !empty($leaseResult['data'])) {
                    $lease = $leaseResult['data'][0];

                    // Get active tenants for this lease
                    $tenantSql = "SELECT 
                                    t.id,
                                    t.first_name,
                                    t.last_name,
                                    t.move_in_date
                                FROM tenants t
                                WHERE t.lease_id = :lease_id
                                AND t.status = 'active'";

                    $tenantResult = $this->executeQuery($tenantSql, ['lease_id' => $lease['id']]);

                    if ($tenantResult['code'] == 200) {
                        $unit['current_lease'] = [
                            'id' => $lease['id'],
                            'start_date' => $lease['start_date'],
                            'end_date' => $lease['end_date'],
                            'date_renewed' => $lease['date_renewed'],
                            'rent_amount' => $lease['rent_amount'],
                            'deposit_amount' => $lease['deposit_amount'] ?? 0,
                            'tenants' => $tenantResult['data']
                        ];
                    }
                } else {
                    $unit['current_lease'] = null;
                }
            }

            return $this->sendPayload($units, 'success', "Successfully retrieved data.", 200);
        }

        return $this->sendPayload(null, 'failed', "Failed to retrieve data.", $result['code']);
    }


    //NOTE TO SELF, INCLUDE LEASE INFO OF EACH TENANT WITH JOIN QUERY
    public function get_tenants($id = null)
    {
        $search = $_GET['search'] ?? null;
        $status = $_GET['status'] ?? null;
        $today = date('Y-m-d');

        $sql = "SELECT 
                    t.*, 
                    u.unit_number,
                    l.start_date as lease_start_date,
                    l.end_date as lease_end_date,
                    l.rent_amount,
                    l.status as lease_status,
                    CASE 
                        WHEN t.valid_id_path IS NOT NULL 
                        THEN CONCAT('http://localhost/aricio-bonina/api/', t.valid_id_path)
                        ELSE NULL 
                    END as valid_id_url
                FROM tenants t
                LEFT JOIN leases l ON t.lease_id = l.id
                LEFT JOIN units u ON l.unit_id = u.id
                WHERE t.status = 'active'";

        $params = [];

        if ($search !== null) {
            $sql .= " AND (
                t.first_name LIKE ? 
                OR t.last_name LIKE ?
                OR u.unit_number LIKE ?
                OR t.email LIKE ?
                OR t.contact_number LIKE ?
            )";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        if ($status !== null) {
            if ($status === 'active') {
                $sql .= " AND (l.end_date >= ? OR l.end_date IS NULL)";
                $params[] = $today;
            } else if ($status === 'expired') {
                $sql .= " AND l.end_date < ?";
                $params[] = $today;
            }
        }

        if ($id !== null) {
            $sql .= " AND t.id = ?";
            $params[] = $id;
        }

        $sql .= " ORDER BY t.first_name, t.last_name";

        $result = $this->executeQuery($sql, $params);

        if ($result['code'] == 200) {
            $tenants = $result['data'];
            foreach ($tenants as &$tenant) {
                $tenant['current_lease'] = [
                    'start_date' => $tenant['lease_start_date'],
                    'end_date' => $tenant['lease_end_date'],
                    'rent_amount' => $tenant['rent_amount'],
                    'status' => $tenant['lease_status']
                ];
            }
            return $this->sendPayload($tenants, 'success', "Successfully retrieved tenants data.", 200);
        }

        return $this->sendPayload(null, 'failed', "Failed to retrieve tenants data.", $result['code']);
    }

    public function get_billings($id = null)
    {
        $condition = null;
        if ($id != null) {
            $condition = "id=$id";
        }
        return $this->get_records('billings', $condition);
    }

    public function get_dashboard_stats()
    {
        $today = date('Y-m-d');
        $sevenDaysFromNow = date('Y-m-d', strtotime('+7 days'));
        $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));

        $sql = "SELECT 
            units.*,
            leases.id as lease_id,
            leases.start_date,
            leases.end_date,
            GROUP_CONCAT(CONCAT(tenants.first_name, ' ', tenants.last_name) SEPARATOR ', ') as tenant_names,
            SUM(CASE WHEN tenants.status = 'active' THEN 1 ELSE 0 END) as tenant_count,
            DATEDIFF(:today, leases.end_date) as days_overdue
        FROM units 
        LEFT JOIN leases ON units.id = leases.unit_id AND leases.status = 'active'
        LEFT JOIN tenants ON leases.id = tenants.lease_id
        WHERE leases.id IS NOT NULL
        GROUP BY units.id, leases.id";

        $params = ['today' => $today];

        $result = $this->executeQuery($sql, $params);

        if ($result['code'] == 200) {
            $stats = [
                'totalUnits' => 0,
                'occupiedUnits' => 0,
                'totalTenants' => 0,
                'overdueLease' => [],
                'expiringSoon' => [],
                'recentPayments' => [],
                'monthlyRevenue' => [
                    'labels' => [],
                    'revenue' => []
                ]
            ];

            // PROCESS DATA
            foreach ($result['data'] as $row) {
                if ($row['end_date'] < $today) {
                    $stats['overdueLease'][] = [
                        'unit' => $row['unit_number'],
                        'tenants' => $row['tenant_names'],
                        'daysOverdue' => max(0, $row['days_overdue'])
                    ];
                } else if ($row['end_date'] <= $sevenDaysFromNow) {
                    $stats['expiringSoon'][] = [
                        'unit' => $row['unit_number'],
                        'tenants' => $row['tenant_names'],
                        'date' => $row['end_date']
                    ];
                }

                // TENANT COUNT
                $stats['totalTenants'] += $row['tenant_count'];
            }

            // OCCUPIED UNITS
            $stats['occupiedUnits'] = count($result['data']);

            // ALL UNITS
            $totalUnitsResult = $this->executeQuery("SELECT COUNT(*) as total FROM units");
            $stats['totalUnits'] = $totalUnitsResult['data'][0]['total'];

            // GET RECENT PAYMENTS (LEASE RENEWALS)
            $recentPaymentsSQL = "SELECT 
                u.unit_number,
                l.rent_amount,
                l.date_renewed,
                GROUP_CONCAT(CONCAT(t.first_name, ' ', t.last_name) SEPARATOR ', ') as tenant_names
            FROM leases l
            JOIN units u ON l.unit_id = u.id
            LEFT JOIN tenants t ON l.id = t.lease_id
            WHERE l.date_renewed >= :sevenDaysAgo
            GROUP BY l.id, u.unit_number
            ORDER BY l.date_renewed DESC";

            $recentPaymentsResult = $this->executeQuery($recentPaymentsSQL, ['sevenDaysAgo' => $sevenDaysAgo]);

            if ($recentPaymentsResult['code'] == 200) {
                foreach ($recentPaymentsResult['data'] as $payment) {
                    $stats['recentPayments'][] = [
                        'unit' => $payment['unit_number'],
                        'amount' => $payment['rent_amount'],
                        'date' => $payment['date_renewed'],
                        'tenants' => $payment['tenant_names']
                    ];
                }
            }

            // GET MONTHLY REVENUE FOR ALL MONTHS OF CURRENT YEAR
            $monthlyRevenueSQL = "
    WITH RECURSIVE months AS (
        SELECT 1 as month_num, 'January' as month_name
        UNION ALL
        SELECT 
            month_num + 1,
            CASE month_num + 1
                WHEN 1 THEN 'January'
                WHEN 2 THEN 'February'
                WHEN 3 THEN 'March'
                WHEN 4 THEN 'April'
                WHEN 5 THEN 'May'
                WHEN 6 THEN 'June'
                WHEN 7 THEN 'July'
                WHEN 8 THEN 'August'
                WHEN 9 THEN 'September'
                WHEN 10 THEN 'October'
                WHEN 11 THEN 'November'
                WHEN 12 THEN 'December'
            END
        FROM months
        WHERE month_num < 12
    )
    SELECT 
        m.month_name as month,
        COALESCE(SUM(l.rent_amount), 0) as total_revenue
    FROM months m
    LEFT JOIN leases l ON MONTH(l.start_date) = m.month_num 
        AND YEAR(l.start_date) = YEAR(CURRENT_DATE)
    GROUP BY m.month_num, m.month_name
    ORDER BY m.month_num
";

            $revenueResult = $this->executeQuery($monthlyRevenueSQL);

            if ($revenueResult['code'] == 200) {
                foreach ($revenueResult['data'] as $revenue) {
                    $stats['monthlyRevenue']['labels'][] = $revenue['month'];
                    $stats['monthlyRevenue']['revenue'][] = (float) $revenue['total_revenue'];
                }
            }
            // Get total revenue for current year
            $yearlyRevenueSQL = "
                SELECT 
                    YEAR(CURRENT_DATE) as year,
                    SUM(l.rent_amount) as total_yearly_revenue
                FROM leases l
                WHERE YEAR(l.created_at) = YEAR(CURRENT_DATE)
            ";

            $yearlyResult = $this->executeQuery($yearlyRevenueSQL);

            if ($yearlyResult['code'] == 200) {
                $stats['yearlyRevenue'] = (float) $yearlyResult['data'][0]['total_yearly_revenue'] ?? 0;
            }

            $monthlyTenantsSQL = "
                WITH RECURSIVE months AS (
                    SELECT 1 as month_num, 'January' as month_name
                    UNION ALL
                    SELECT 
                        month_num + 1,
                        CASE month_num + 1
                            WHEN 1 THEN 'January'
                            WHEN 2 THEN 'February'
                            WHEN 3 THEN 'March'
                            WHEN 4 THEN 'April'
                            WHEN 5 THEN 'May'
                            WHEN 6 THEN 'June'
                            WHEN 7 THEN 'July'
                            WHEN 8 THEN 'August'
                            WHEN 9 THEN 'September'
                            WHEN 10 THEN 'October'
                            WHEN 11 THEN 'November'
                            WHEN 12 THEN 'December'
                        END
                    FROM months
                    WHERE month_num < 12
                )
                SELECT 
                    m.month_name as month,
                    COUNT(DISTINCT CASE 
                        WHEN MONTH(t.move_in_date) <= m.month_num 
                        AND t.status = 'active'
                        AND YEAR(t.move_in_date) = YEAR(CURRENT_DATE)
                        THEN t.id 
                        END) as tenant_count
                FROM months m
                LEFT JOIN tenants t ON YEAR(t.move_in_date) = YEAR(CURRENT_DATE)
                GROUP BY m.month_num, m.month_name
                ORDER BY m.month_num;
            ";

            $tenantsResult = $this->executeQuery($monthlyTenantsSQL);

            if ($tenantsResult['code'] == 200) {
                $stats['monthlyTenants'] = [
                    'labels' => [],
                    'counts' => []
                ];
                foreach ($tenantsResult['data'] as $data) {
                    $stats['monthlyTenants']['labels'][] = $data['month'];
                    $stats['monthlyTenants']['counts'][] = (int) $data['tenant_count'];
                }
            }

            $monthlyOccupiedUnitsSQL = "
                WITH RECURSIVE months AS (
                    SELECT 1 as month_num, 'January' as month_name
                    UNION ALL
                    SELECT 
                        month_num + 1,
                        CASE month_num + 1
                            WHEN 1 THEN 'January'
                            WHEN 2 THEN 'February'
                            WHEN 3 THEN 'March'
                            WHEN 4 THEN 'April'
                            WHEN 5 THEN 'May'
                            WHEN 6 THEN 'June'
                            WHEN 7 THEN 'July'
                            WHEN 8 THEN 'August'
                            WHEN 9 THEN 'September'
                            WHEN 10 THEN 'October'
                            WHEN 11 THEN 'November'
                            WHEN 12 THEN 'December'
                        END
                    FROM months
                    WHERE month_num < 12
                )
                SELECT 
                    m.month_name as month,
                    COUNT(DISTINCT CASE 
                        WHEN l.status = 'active'
                        AND (
                            (YEAR(l.start_date) < YEAR(CURRENT_DATE) AND l.end_date >= STR_TO_DATE(CONCAT(YEAR(CURRENT_DATE), '-', m.month_num, '-01'), '%Y-%m-%d'))
                            OR 
                            (YEAR(l.start_date) = YEAR(CURRENT_DATE) AND MONTH(l.start_date) <= m.month_num)
                        )
                        THEN u.id 
                    END) as occupied_units
                FROM months m
                LEFT JOIN leases l ON l.status = 'active'
                LEFT JOIN units u ON l.unit_id = u.id
                GROUP BY m.month_num, m.month_name
                ORDER BY m.month_num;
            ";

            $occupiedUnitsResult = $this->executeQuery($monthlyOccupiedUnitsSQL);

            if ($occupiedUnitsResult['code'] == 200) {
                $stats['monthlyOccupiedUnits'] = [
                    'labels' => [],
                    'counts' => []
                ];
                foreach ($occupiedUnitsResult['data'] as $data) {
                    $stats['monthlyOccupiedUnits']['labels'][] = $data['month'];
                    $stats['monthlyOccupiedUnits']['counts'][] = (int) $data['occupied_units'];
                }
            }

            return $this->sendPayload($stats, 'success', "Successfully retrieved dashboard stats.", 200);
        }

        return $this->sendPayload(null, 'failed', "Failed to retrieve dashboard stats.", $result['code']);
    }


    public function getLeaseHistory($unit_id = null)
    {
        $search = $_GET['search'] ?? null;
        $status = $_GET['status'] ?? null;
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;

        // Build base params array
        $params = [];
        $conditions = [];

        // Build WHERE conditions
        if ($unit_id !== null) {
            $conditions[] = "l.unit_id = ?";
            $params[] = $unit_id;
        }

        if ($search !== null) {
            $conditions[] = "(u.unit_number LIKE ? OR CONCAT(t.first_name, ' ', t.last_name) LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if ($status !== null) {
            if ($status === 'active') {
                $conditions[] = "l.date_terminated IS NULL";
            } else if ($status === 'terminated') {
                $conditions[] = "l.date_terminated IS NOT NULL";
            }
        }

        $whereClause = count($conditions) > 0 ? "WHERE " . implode(" AND ", $conditions) : "";

        // Count query
        $countSql = "SELECT COUNT(DISTINCT u.unit_number) as total 
                     FROM leases l
                     JOIN units u ON l.unit_id = u.id
                     LEFT JOIN tenants t ON l.id = t.lease_id
                     $whereClause";

        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($params);
        $totalCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        $totalPages = ceil($totalCount / $limit);

        // Get paginated unit numbers
        $unitParams = $params;
        $unitParams[] = $limit;
        $unitParams[] = $offset;

        $unitSql = "SELECT DISTINCT u.unit_number
                    FROM leases l
                    JOIN units u ON l.unit_id = u.id
                    LEFT JOIN tenants t ON l.id = t.lease_id
                    $whereClause
                    ORDER BY u.unit_number 
                    LIMIT ? OFFSET ?";

        $stmt = $this->pdo->prepare($unitSql);
        $stmt->execute($unitParams);
        $paginatedUnits = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($paginatedUnits)) {
            return $this->sendPayload([
                'leases' => [],
                'pagination' => [
                    'currentPage' => $page,
                    'totalPages' => $totalPages,
                    'totalItems' => $totalCount,
                    'itemsPerPage' => $limit
                ]
            ], 'success', "No lease history found.", 200);
        }

        // Prepare IN clause and parameters for final query
        $placeholders = str_repeat('?,', count($paginatedUnits) - 1) . '?';

        // Main query to get all leases for the paginated units
        $sql = "SELECT 
                    l.*,
                    u.unit_number,
                    GROUP_CONCAT(CONCAT(t.first_name, ' ', t.last_name) SEPARATOR ', ') as tenant_names,
                    MAX(COALESCE(l.date_renewed, l.start_date)) OVER (PARTITION BY u.unit_number) as latest_date,
                    l.status
                FROM leases l
                JOIN units u ON l.unit_id = u.id
                LEFT JOIN tenants t ON l.id = t.lease_id
                WHERE u.unit_number IN ($placeholders)
                GROUP BY l.id, u.unit_number 
                ORDER BY latest_date DESC, u.unit_number, l.start_date DESC";

        $result = $this->executeQuery($sql, $paginatedUnits);

        if ($result['code'] == 200) {
            // Organize leases by unit
            $organizedLeases = [];
            foreach ($result['data'] as $lease) {
                $unitNumber = $lease['unit_number'];
                if (!isset($organizedLeases[$unitNumber])) {
                    $organizedLeases[$unitNumber] = [];
                }
                $organizedLeases[$unitNumber][] = [
                    'id' => $lease['id'],
                    'start_date' => $lease['start_date'],
                    'end_date' => $lease['end_date'],
                    'date_renewed' => $lease['date_renewed'],
                    'date_terminated' => $lease['date_terminated'],
                    'rent_amount' => $lease['rent_amount'],
                    'tenants' => $lease['tenant_names'],
                    'latest_date' => $lease['latest_date'],
                    'created_at' => $lease['created_at']
                ];
            }

            return $this->sendPayload([
                'leases' => $organizedLeases,
                'pagination' => [
                    'currentPage' => $page,
                    'totalPages' => $totalPages,
                    'totalItems' => $totalCount,
                    'itemsPerPage' => $limit
                ]
            ], 'success', "Successfully retrieved lease history.", 200);
        }

        return $this->sendPayload(null, 'failed', "Failed to retrieve lease history.", $result['code']);
    }

}
