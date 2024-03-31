<?php


class BalanceAccountingRedistributionList extends aJWSApiMethod
{
    protected function dispatch()
    {
        $userId = $this->getUserId();
        $dev = $this->app->input->get('dev', 0);
        $querylog = [];

		
		$user_id = $_GET['user_id'];

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
        $querylog[]=['type'=>'check_rights','date'=>microtime()];


        $this->db->setQuery("SELECT c.id, p.id AS remnant_id, n.name, n.dosage, n.unit, l.trade_name, l.series, l.expire_date, v6.value AS division_edrpou, v.value, p.quantity, p.invoice_date, p.invoice_num, r.id AS remnant_old_id, q.distributed_quantity
FROM med_dp_remnants r 
JOIN med_dp_logistics_supply s ON s.id = r.supply_id and s.inactive = 0
JOIN med_dp_logistics l ON l.id = s.remnants_id
JOIN med_name n ON n.id = l.mnn_id
JOIN med_dp_remnants p ON r.id = p.parent_id
left JOIN med_dp_consumption c ON r.id = c.remnant_id
LEFT JOIN jos_fields_values v ON v.item_id = p.userid AND v.field_id = 2
LEFT JOIN jos_fields_values v6 ON v6.item_id = p.userid AND v6.field_id = 6
LEFT JOIN (SELECT r.parent_id, sum(ifnull(r.quantity,0)) AS distributed_quantity FROM med_dp_remnants r WHERE r.parent_id IS NOT null GROUP BY r.parent_id) q ON q.parent_id = r.id
WHERE r.userid = $userId AND r.distributed_quantity > 0
Group by p.id;");

        $balance = $this->db->loadAssocList();
        $querylog[]=['type'=>'balance1','date'=>microtime(),'cnt'=>count($balance)];

		foreach ($balance as &$item) {
			$remnant_id = $item['remnant_id'];
			
			$this->db->setQuery("SELECT if(c.cons_quantity IS NOT NULL OR c.broken_quantity IS NOT NULL, 1,0) AS changing 
FROM med_dp_consumption c
WHERE c.remnant_id = $remnant_id AND c.active=1");

			$chang = $this->db->loadAssoc();
			$changing = $chang['changing'];
			$item['changing'] = $changing;
		}
        $querylog[]=['type'=>'balance2','date'=>microtime()];

		// Поточний звітний період
        $this->db->setQuery("SELECT c.accounting_date
from meddata.med_dp_consumption c
WHERE c.start_date = (SELECT max(c.start_date) FROM med_dp_consumption c)
GROUP BY c.accounting_date");
        $accounting_date = $this->db->loadAssocList();
        $querylog[]=['type'=>'acc_date','date'=>microtime()];

		$acc_date = $accounting_date[0]['accounting_date'];
		// Статус підписання КЕП
        $this->db->setQuery("SELECT * FROM (
SELECT jt.remnant_id
FROM med_dp_redistribution_sign , JSON_TABLE(data, '$[*]' COLUMNS (remnant_id VARCHAR(255) PATH '$.remnant_id')) AS jt
WHERE `status`='signed' AND `data`!='' AND `data` IS NOT null AND userid=".$userId."
UNION
SELECT r.parent_id AS remnant_id
FROM med_dp_remnants r
WHERE parent_id > 0 AND invoice_date < '2023-08-01') o");
        $statusResult = $this->db->loadAssocList();
		$statusIds = array_column($statusResult, 'remnant_id');
        $querylog[]=['type'=>'status','date'=>microtime()];

		foreach ($balance as &$item) {
			$id = $item['remnant_old_id'];
			
			if (in_array($id, $statusIds)) {
				$item['status'] = 'Підписано';
			}
		}

        return ['success' => true,'query'=>$querylog, 'data' => $balance];
    }
}