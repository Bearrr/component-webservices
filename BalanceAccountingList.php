<?php

class BalanceAccountingList extends aJWSApiMethod
{
    protected function dispatch()
    {
        $userId = $this->getUserId();
        $dev = $this->app->input->get('dev', 0);

        $filters = $_GET['filters'];
		$user_id = $_GET['user_id'];
        $querylog = [];

        if ($dev ==1 ) $userId = 539;
        if ($userId == 0) {
            return $this->error('Для запиту інформації необхідно пройти авторизацію. Спробуйте оновити сторінку та авторизуватись.');
        }
		$querylog[]=['type'=>'start','date'=>microtime()];
		if ($user_id != 0) {

			$this->db->setQuery("SELECT * FROM jos_fields_values v WHERE v.field_id = 1 AND item_id IN ($user_id, $userId)");
			$region_list = $this->db->loadAssocList();
			if (!empty($region_list)) {
				$values = array();

				foreach ($region_list as $row) {
					$values[] = $row["value"];
				}
				
				if (count(array_unique($values)) === 1) {
					$this->db->setQuery("SELECT u.id, u.name FROM jos_users u
join jos_user_usergroup_map gm on u.id=gm.user_id
WHERE gm.group_id = 261 AND u.id=$user_id");
					$user_group = $this->db->loadAssocList();
					if (!empty($user_group)) {
						$userId = $user_id;
					} else {
						return $this->error('Користувач не належить до групи списання залишків');
					}
				} else {
					return $this->error('Користувач не належить до області закладу');
				}
			} else {
				return $this->error('Невірний ідентифікатор користувача');
			}
		}
        $querylog[]=['type'=>'rights_check','date'=>microtime()];

        $conditions = [];
        $values = [];
		
		$statusFilter = null;
		foreach ($filters as $key => $filter) {
			if ($filter['key'] === 'status') {
				$statusFilter = $filter;
				unset($filters[$key]);
				break;
			}
		}

        if (count($filters) > 0) {
            foreach ($filters as $in => $filter) {
                $filterKey = $filter['key'];
                $filterValue = $filter['value'];

                if (is_array($filterValue)) {
                    foreach ($filterValue as $id => $val) {
                        $values[] = "'" . mysqli_real_escape_string($val) . "'";
                    }
                    $conditions[] = " AND " . $filterKey . " IN (" . implode(",", $values) . ")";
                } else {
                    // Перевірка, чи ключ фільтру знаходиться у списку колонок, щоб проставити відповідні аліаси таблиць
                    if (in_array($filterKey, ['start_quantity', 'cons_quantity', 'broken_quantity'])) {
                        $conditions[] = " AND c." . $filterKey . " LIKE '%" . $filterValue . "%'";
                    } elseif (in_array($filterKey, ['trade_name', 'series', 'multiplicity', 'expire_date', 'performer'])) {
                        $conditions[] = " AND l." . $filterKey . " LIKE '%" . $filterValue . "%'";
                    } elseif (in_array($filterKey, ['year', 'rep_name', 'description'])) {
                        $conditions[] = " AND t." . $filterKey . " LIKE '%" . $filterValue . "%'";
                    } elseif (in_array($filterKey, ['balance_quantity', 'invoice_num', 'invoice_date'])) {
                        $conditions[] = " AND r." . $filterKey . " LIKE '%" . $filterValue . "%'";
                    } else {
                        $conditions[] = " AND n." . $filterKey . " LIKE '%" . $filterValue . "%'";
                    }
                }
            }
        }
        $querylog[]=['type'=>'filters_end','date'=>microtime()];
        $q = "SELECT c.id, r.id AS remnant_id, c.start_quantity, ifnull(c.cons_quantity,'') AS cons_quantity, ifnull(c.broken_quantity, '') AS broken_quantity, r.balance_quantity, n.unit, 
l.trade_name, l.series, l.multiplicity, r.invoice_num, r.invoice_date, n.dosage, n.release_form, n.name, l.expire_date, t.`year`, t.rep_name, 
if(r.parent_id IS NULL OR r.parent_id = '', l.performer,v.value) as performer, t.description, n.subtype, n.excl_mult, true AS period, v13.value AS `order`, c.start_date
from med_dp_consumption c
JOIN med_dp_remnants r ON c.remnant_id = r.id
JOIN med_dp_logistics_supply s ON r.supply_id = s.id AND s.inactive = 0
JOIN med_dp_logistics l ON s.remnants_id = l.id
JOIN med_name n ON l.mnn_id = n.id
JOIN med_type t ON n.type_code = t.type_code
LEFT JOIN med_dp_remnants p ON r.parent_id = p.id
LEFT JOIN jos_fields_values v ON p.userid = v.item_id AND v.field_id = 2
LEFT JOIN jos_fields_values v13 ON c.userid = v13.item_id AND v13.field_id = 13
WHERE c.start_quantity > 0 AND c.active = 1 and c.userid = ".$userId . implode(" ", $conditions);
        $this->db->setQuery($q);
        $balance = $this->db->loadAssocList();
		$q_check = "SELECT c.id, r.id AS remnant_id, c.start_quantity, ifnull(c.cons_quantity,'') AS cons_quantity, ifnull(c.broken_quantity, '') AS broken_quantity, r.balance_quantity, n.unit, 
l.trade_name, l.series, l.multiplicity, r.invoice_num, r.invoice_date, n.dosage, n.release_form, n.name, l.expire_date, t.`year`, t.rep_name, 
if(r.parent_id IS NULL OR r.parent_id = '', l.performer,v.value) as performer, t.description, n.subtype, n.excl_mult, true AS period, v13.value AS `order`, c.start_date
from med_dp_consumption c
JOIN med_dp_remnants r ON c.remnant_id = r.id
JOIN med_dp_logistics_supply s ON r.supply_id = s.id AND s.inactive = 0
JOIN med_dp_logistics l ON s.remnants_id = l.id
JOIN med_name n ON l.mnn_id = n.id
JOIN med_type t ON n.type_code = t.type_code
LEFT JOIN med_dp_remnants p ON r.parent_id = p.id
LEFT JOIN jos_fields_values v ON p.userid = v.item_id AND v.field_id = 2
LEFT JOIN jos_fields_values v13 ON c.userid = v13.item_id AND v13.field_id = 13
WHERE c.start_quantity > 0 AND c.active = 1 and c.userid = ".$userId .";";
        $this->db->setQuery($q_check);
        $balance_check = $this->db->loadAssocList();
        $querylog[]=['type'=>'balance_query','date'=>microtime(),'query'=>$q];
        if (!$balance_check) { // Якщо немає записів що відповідають умові формування записів в звітному періоді
            $this->db->setQuery("SELECT c.id, ifnull(r.balance_quantity+ifnull(c.cons_quantity,0)+ifnull(c.broken_quantity, 0), r.quantity - IFNULL(r.distributed_quantity, 0)) AS start_quantity, ifnull(c.cons_quantity,'') AS cons, ifnull(c.broken_quantity, '') AS broken, 
r.balance_quantity, n.unit, l.trade_name, l.series, l.multiplicity, r.invoice_num, r.invoice_date, n.dosage, n.release_form, n.name, l.expire_date, t.`year`, t.rep_name, 
if(r.parent_id IS NULL OR r.parent_id = '', l.performer,v.value) as performer, t.description, n.subtype, false AS period, r.id AS remnant_id, v13.value AS `order`, c.start_date
FROM med_dp_remnants r
LEFT JOIN med_dp_remnants p ON p.id = r.parent_id
left JOIN med_dp_consumption c ON r.id = c.remnant_id  AND c.end_date = (select MAX(c.end_date) FROM med_dp_consumption c)
JOIN med_dp_logistics_supply s ON s.id = r.supply_id and s.inactive = 0
JOIN med_dp_logistics l ON l.id = s.remnants_id
JOIN med_name n ON n.id = l.mnn_id
JOIN med_type t ON t.type_code = n.type_code
LEFT JOIN jos_fields_values v ON v.item_id = p.userid AND v.field_id = 2
LEFT JOIN jos_fields_values v13 ON r.userid = v13.item_id AND v13.field_id = 13
WHERE r.userid  = $userId AND r.balance_quantity != 0". implode(" ", $conditions));
            $balance = $this->db->loadAssocList();
            $querylog[]=['type'=>'balance_try1','date'=>microtime()];
        }

        // Поточний звітний період
        $this->db->setQuery("SELECT c.accounting_date
from meddata.med_dp_consumption c
WHERE c.start_date = (SELECT max(c.start_date) FROM med_dp_consumption c)
GROUP BY c.accounting_date");
        $accounting_date = $this->db->loadAssocList();
        $querylog[]=['type'=>'acc_date','date'=>microtime()];

		$acc_date = $accounting_date[0]['accounting_date'];
		
		// Статус підписання КЕП
//        $this->db->setQuery("SELECT min(case when app=content then 'Підписано' ELSE 'В роботі' END) `status`, app, content, id
//FROM (SELECT a.id,a.userid,a.date_time,a.accounting_date,concat(ifnull(a.cons_quantity,0),';',ifnull(a.broken_quantity,0)) app,IFNULL(v.content,'') content,v.created FROM med_dp_consumption a
//LEFT JOIN med_dp_consumption_verification v ON a.id = v.id
//
//WHERE a.accounting_date = '".$acc_date."' AND a.userid = ".$userId.") d
//GROUP BY id");

        $this->db->setQuery("SELECT if(cons_quantity=cons AND broken_quantity=broken, 'Підписано', 
if((d.cons_quantity!=ifnull(d.cons,-1) OR d.broken_quantity!=ifnull(d.broken,-1)) and (d.cons_quantity is NOT null and d.broken_quantity IS NOT NULL), 'Прозвітовано', 'Очікується')) `status`, 
		id, d.cons_quantity, cons, d.broken_quantity, broken
FROM (SELECT a.id, a.userid, a.date_time, a.accounting_date, a.cons_quantity AS cons_quantity, a.broken_quantity AS  broken_quantity, v.cons_quantity AS cons,
					v.broken_quantity AS broken, v.created 
		FROM med_dp_consumption a
		LEFT JOIN med_dp_consumption_verification v ON a.id = v.id
		WHERE a.userid = ".$userId." AND a.accounting_date = '".$acc_date."') d");
        $status = $this->db->loadAssocList();
        $querylog[]=['type'=>'status','date'=>microtime()];

		foreach ($status as $row) {
			$id = $row['id'];
			$status = $row['status'];

			foreach ($balance as &$item) {
				if ($item['id'] == $id) {
					$item['status'] = $status;
					break;
				}
			}
		}

        // Відсоткові значення для прогрес бару статусу звітування й підписання обліку залишків
        $countNonEmptyFields = 0;
        $countAllRows = count($balance);
        $countKepFields = 0;

        if ($countAllRows > 0) {
            foreach ($balance as $row) {
                if ($row['cons_quantity'] !== '' || $row['broken_quantity'] !== '') {
                    $countNonEmptyFields++;
                }
            }

            foreach ($balance as $row) {
                if ($row['status'] == 'Підписано') {
                    $countKepFields++;
                }
            }

            $percentage = floor(($countNonEmptyFields / $countAllRows) * 100);
            $percentage_kep = floor(($countKepFields / $countAllRows) * 100);
        } else {
            $percentage = 0;
            $percentage_kep = 0;
        }
        $querylog[]=['type'=>'finish','date'=>microtime()];
		
		if (!empty($statusFilter)) {
			$statusValue = $statusFilter['value'];
			$filteredBalance = array_filter($balance, function ($item) use ($statusValue) {
				return isset($item['status']) && $item['status'] === $statusValue;
			});
		} else {
			$filteredBalance = $balance;
		}
		$filteredBalance = array_values($filteredBalance);

        return ['success' => true,'log'=>$querylog, 'data' => $filteredBalance, 'filters' => $filters, 'accounting_date' => $accounting_date, 'progress_bar' => $percentage. "%", 'progress_bar_kep' => $percentage_kep. "%"];
    }
}