<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

class BalanceAccountingRedistributionSave extends aJWSApiMethod
{
    protected function dispatch()
    {

        $userId = $this->getUserId();
        $dev = $this->app->input->get('dev', 0);

        $request = file_get_contents('php://input');
        $data = json_decode($request, true);
        $object = new stdClass();


        if ($dev ==1 ) $userId = 539;
        if ($userId == 0) {
            return $this->error('Для запиту інформації необхідно пройти авторизацію. Спробуйте оновити сторінку та авторизуватись.');
        }
		
		foreach ($data as $array) {
			$quantity = $array['quantity'];
			$division_edrpou = $array['division_edrpou'];
			//$id = $array['id'];
			$remnant_id = $array['remnant_id'];
			$remnant_old_id = $array['remnant_old_id'];
			$invoice_num = $array['invoice_num'];
			$invoice_date = $array['invoice_date'];
            file_put_contents('/usr/sites/meddata.com.ua/logs/redistribution-'.$userId,
                "SELECT CONCAT(v6.value, ' / ', v.value), v.item_id
FROM jos_fields_values v
JOIN jos_fields_values v6 ON v6.item_id = v.item_id AND v6.field_id = 6 AND v.field_id = 2
JOIN jos_users u ON u.id = v.item_id AND u.block = 0
JOIN jos_user_usergroup_map m ON m.user_id = v.item_id AND m.group_id = 261
WHERE v6.value = '" . addslashes($division_edrpou) . "'");

			$this->db->setQuery("SELECT CONCAT(v6.value, ' / ', v.value), v.item_id
FROM jos_fields_values v
JOIN jos_fields_values v6 ON v6.item_id = v.item_id AND v6.field_id = 6 AND v.field_id = 2
JOIN jos_users u ON u.id = v.item_id AND u.block = 0
JOIN jos_user_usergroup_map m ON m.user_id = v.item_id AND m.group_id = 261
WHERE v6.value = '" . addslashes($division_edrpou) . "'");
			$userid_query = $this->db->loadAssocList();

			$userid = '';
			if (!empty($userid_query)) {
				$userid = $userid_query[0]['item_id'];
			}
            file_put_contents('/usr/sites/meddata.com.ua/logs/redistribution-'.$userId,
                "SELECT r.supply_id
	FROM med_dp_remnants r
	WHERE r.id = '" . addslashes($remnant_id) . "'");
			$this->db->setQuery("SELECT r.supply_id
	FROM med_dp_remnants r
	WHERE r.id = '" . addslashes($remnant_id) . "'");
			$supply_query = $this->db->loadAssocList();

			$supply_id = NULL;
			if (!empty($supply_query)) {
				$supply_id = $supply_query[0]['supply_id'];
			}
            file_put_contents('/usr/sites/meddata.com.ua/logs/redistribution-'.$userId,
                "insert into med_dp_remnants (date_time,supply_id,quantity,division_edrpou,parent_id,userid,invoice_num,invoice_date)
			values (now(),$supply_id,'" . addslashes($quantity) . "','" . addslashes($division_edrpou) . "','" . addslashes($remnant_id) . "', $userid,'" . addslashes($invoice_num) . "','" . addslashes($invoice_date) . "')");
			
			$this->db->setQuery("SELECT * FROM med_dp_remnants
								WHERE supply_id = $supply_id
								AND division_edrpou = '$division_edrpou'
								AND invoice_num = '$invoice_num'");

			$result = $this->db->execute();
			if ($result->num_rows > 0) {
				return ['message' => 'Запис з вказаними номером накладної вже існує!'];
			} else {
				$this->db->setQuery("insert into med_dp_remnants (date_time,supply_id,quantity,division_edrpou,parent_id,userid,invoice_num,invoice_date)
				values (now(),$supply_id,'" . addslashes($quantity) . "','" . addslashes($division_edrpou) . "','" . addslashes($remnant_id) . "', $userid,'" . addslashes($invoice_num) . "','" . addslashes($invoice_date) . "')"); //
				$this->db->execute();
				file_put_contents('/usr/sites/meddata.com.ua/logs/redistribution-'.$userId,"SELECT sum(r.quantity) 
		FROM med_dp_remnants r
		WHERE r.parent_id = '" . addslashes($remnant_id) . "'");


				$this->db->setQuery("SELECT sum(r.quantity) 
		FROM med_dp_remnants r
		WHERE r.parent_id = '" . addslashes($remnant_id) . "'");
				$quantity_query = $this->db->loadAssocList();

				$distributed_quantity = NULL;
				if (!empty($quantity_query)) {
					$distributed_quantity = $quantity_query[0]['sum(r.quantity)'];
				}
				file_put_contents('/usr/sites/meddata.com.ua/logs/redistribution-'.$userId,"UPDATE med_dp_remnants SET date_time=now(),distributed_quantity='" . addslashes($distributed_quantity) . "' WHERE id='" . addslashes($remnant_id) . "'"); //
				$this->db->setQuery("UPDATE med_dp_remnants SET date_time=now(),distributed_quantity='" . addslashes($distributed_quantity) . "' WHERE id='" . addslashes($remnant_id) . "'"); //
				$this->db->execute();
				file_put_contents('/usr/sites/meddata.com.ua/logs/redistribution-'.$userId,"SELECT r.quantity - r.distributed_quantity - sum(ifnull(c.cons_quantity,0)+ ifnull(c.broken_quantity,0)) AS balance_quantity 
	FROM med_dp_remnants r
	JOIN med_dp_consumption c ON c.remnant_id = r.id
	WHERE r.id = '" . addslashes($remnant_id) . "'");
				$this->db->setQuery("SELECT r.quantity - r.distributed_quantity - sum(ifnull(c.cons_quantity,0)+ ifnull(c.broken_quantity,0)) AS balance_quantity 
	FROM med_dp_remnants r
	JOIN med_dp_consumption c ON c.remnant_id = r.id
	WHERE r.id = '" . addslashes($remnant_id) . "'");
				$balance_query = $this->db->loadAssocList();

				$balance_quantity = NULL;
				if (!empty($balance_query)) {
					$balance_quantity = $balance_query[0]['balance_quantity'];
				}
				file_put_contents('/usr/sites/meddata.com.ua/logs/redistribution-'.$userId,"UPDATE med_dp_remnants SET date_time=now(),balance_quantity='" . addslashes($balance_quantity) . "' WHERE id='" . addslashes($remnant_id) . "'"); //
				$this->db->setQuery("UPDATE med_dp_remnants SET date_time=now(),balance_quantity='" . addslashes($balance_quantity) . "' WHERE id='" . addslashes($remnant_id) . "'"); //
				$this->db->execute();
				file_put_contents('/usr/sites/meddata.com.ua/logs/redistribution-'.$userId,"SELECT r.quantity- ifnull(r.distributed_quantity,0) - IFNULL(cons, 0) start_quantity
	FROM med_dp_remnants r
	left JOIN (SELECT sum(IFNULL(c.cons_quantity, 0)+IFNULL(c.broken_quantity, 0)) cons, c.remnant_id
	FROM med_dp_consumption c
	where c.active = 0 AND c.remnant_id = '" . addslashes($remnant_id) . "'
	GROUP BY c.remnant_id) k ON k.remnant_id = r.id
	WHERE r.id = '" . addslashes($remnant_id) . "'");
				$this->db->setQuery("SELECT r.quantity- ifnull(r.distributed_quantity,0) - IFNULL(cons, 0) start_quantity
	FROM med_dp_remnants r
	left JOIN (SELECT sum(IFNULL(c.cons_quantity, 0)+IFNULL(c.broken_quantity, 0)) cons, c.remnant_id
	FROM med_dp_consumption c
	where c.active = 0 AND c.remnant_id = '" . addslashes($remnant_id) . "'
	GROUP BY c.remnant_id) k ON k.remnant_id = r.id
	WHERE r.id = '" . addslashes($remnant_id) . "'");
				$start_quantity_query = $this->db->loadAssocList();

				$start_quantity = NULL;
				if (!empty($start_quantity_query)) {
					$start_quantity = $start_quantity_query[0]['start_quantity'];
				}
				file_put_contents('/usr/sites/meddata.com.ua/logs/redistribution-'.$userId,"UPDATE med_dp_consumption SET date_time=now(),start_quantity='" . addslashes($start_quantity) . "' WHERE active=1 AND remnant_id='" . addslashes($remnant_id) . "'"); //
				$this->db->setQuery("UPDATE med_dp_consumption SET date_time=now(),start_quantity='" . addslashes($start_quantity) . "' WHERE active=1 AND remnant_id='" . addslashes($remnant_id) . "'"); //
				$this->db->execute();
			}
		}
		return ['success' => true];

    }
}