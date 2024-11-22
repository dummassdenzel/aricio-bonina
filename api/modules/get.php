<?php

require_once 'global.php';

class Get extends GlobalMethods
{
    private $pdo;
    public function __construct(\PDO $pdo)
    {
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
        $columns = "units.*, 
                GROUP_CONCAT(tenants.id) as id,
                GROUP_CONCAT(tenants.first_name) as first_name,
                GROUP_CONCAT(tenants.last_name) as last_name,
                GROUP_CONCAT(tenants.unit_id) as unit_id,
                GROUP_CONCAT(tenants.move_in_date) as move_in_date,
                GROUP_CONCAT(leases.start_date) as start_date,
                GROUP_CONCAT(leases.end_date) as end_date,
                GROUP_CONCAT(leases.date_renewed) as date_renewed";


        $customSqlStr = "SELECT $columns 
                     FROM units 
                     LEFT JOIN tenants ON units.id = tenants.unit_id
                     LEFT JOIN leases ON tenants.id = leases.tenant_id";

        if ($id != null) {
            $customSqlStr .= " WHERE units.id = :id";
            $params = ['id' => $id];
        } else {
            $params = [];
        }

        $customSqlStr .= " GROUP BY units.id";

        return $this->get_records(null, null, null, $customSqlStr, $params);
    }


    //NOTE TO SELF, INCLUDE LEASE INFO OF EACH TENANT WITH JOIN QUERY
    public function get_tenants($id = null)
    {
        $condition = null;
        if ($id != null) {
            $condition = "id=$id";
        }
        return $this->get_records('tenants', $condition);
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
        $thirtyDaysFromNow = date('Y-m-d', strtotime('+30 days'));

        $sql = "SELECT 
            units.*,
            tenants.id as tenant_id,
            tenants.first_name,
            tenants.last_name,
            leases.start_date,
            leases.end_date,
            DATEDIFF(:today, leases.end_date) as days_overdue
        FROM units 
        LEFT JOIN tenants ON units.id = tenants.unit_id
        LEFT JOIN leases ON tenants.id = leases.tenant_id
        WHERE tenants.id IS NOT NULL";

        $params = ['today' => $today];

        $result = $this->executeQuery($sql, $params);

        if ($result['code'] == 200) {
            $stats = [
                'totalUnits' => 0,
                'occupiedUnits' => 0,
                'totalTenants' => 0,
                'overdueLease' => [],
                'expiringSoon' => [],
                'recentPayments' => []
            ];

            // PROCESS DATA
            foreach ($result['data'] as $row) {
                if ($row['end_date'] < $today) {
                    $stats['overdueLease'][] = [
                        'unit' => $row['unit_number'],
                        'tenant' => $row['first_name'] . ' ' . $row['last_name'],
                        'daysOverdue' => max(0, $row['days_overdue'])
                    ];
                } else if ($row['end_date'] <= $thirtyDaysFromNow) {
                    $stats['expiringSoon'][] = [
                        'unit' => $row['unit_number'],
                        'issue' => $row['first_name'] . ' ' . $row['last_name'],
                        'date' => $row['end_date']
                    ];
                }
            }

            // Count totals
            $stats['totalUnits'] = count($this->get_units()['payload']);
            $stats['occupiedUnits'] = count($result['data']);
            $stats['totalTenants'] = count($result['data']);

            return $this->sendPayload($stats, 'success', "Successfully retrieved dashboard stats.", 200);
        }

        return $this->sendPayload(null, 'failed', "Failed to retrieve dashboard stats.", $result['code']);
    }

}
