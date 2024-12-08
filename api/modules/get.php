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
        // First, get all units with their basic information
        $sql = "SELECT 
                u.*,
                CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM leases l 
                        WHERE l.unit_id = u.id 
                        AND l.status = 'active'
                    ) THEN 'occupied'
                    ELSE 'vacant'
                END as status
                FROM units u";

        if ($id !== null) {
            $sql .= " WHERE u.unit_number = :id";
            $params = ['id' => $id];
        } else {
            $params = [];
        }

        $sql .= " ORDER BY u.id";

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
                                l.rent_amount
                            FROM leases l
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
        $sql = "SELECT 
                    t.*, 
                    u.unit_number 
                FROM tenants t
                LEFT JOIN leases l ON t.lease_id = l.id
                LEFT JOIN units u ON l.unit_id = u.id
                WHERE t.status = 'active'";

        return $this->get_records(null, null, null, $sql, null);
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
            COUNT(tenants.id) as tenant_count,
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

            return $this->sendPayload($stats, 'success', "Successfully retrieved dashboard stats.", 200);
        }

        return $this->sendPayload(null, 'failed', "Failed to retrieve dashboard stats.", $result['code']);
    }


    public function getLeaseHistory($unit_id = null)
    {
        $sql = "SELECT 
                    l.*,
                    u.unit_number,
                    GROUP_CONCAT(CONCAT(t.first_name, ' ', t.last_name) SEPARATOR ', ') as tenant_names,
                    MAX(COALESCE(l.date_renewed, l.start_date)) OVER (PARTITION BY u.unit_number) as latest_date,
                    l.status
                FROM leases l
                JOIN units u ON l.unit_id = u.id
                LEFT JOIN tenants t ON l.id = t.lease_id
                WHERE 1=1 ";

        $params = [];
        if ($unit_id !== null) {
            $sql .= "AND l.unit_id = ? ";
            $params[] = $unit_id;
        }

        $sql .= "GROUP BY l.id, u.unit_number 
                 ORDER BY latest_date DESC, u.unit_number, l.start_date DESC";

        $result = $this->executeQuery($sql, $params);

        if ($result['code'] == 200) {
            // ORGANIZE LEASES BY UNIT
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
                    'rent_amount' => $lease['rent_amount'],
                    'tenants' => $lease['tenant_names'],
                    'latest_date' => $lease['latest_date'],
                    'created_at' => $lease['created_at']
                ];
            }

            return $this->sendPayload($organizedLeases, 'success', "Successfully retrieved lease history.", 200);
        }

        return $this->sendPayload(null, 'failed', "Failed to retrieve lease history.", $result['code']);
    }

}
