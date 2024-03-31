<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

class BalanceAccountingRedistributionUpdate extends aJWSApiMethod
{
    protected function dispatch()
    {
        $userId = $this->getUserId();
        $dev = $this->app->input->get('dev', 0);

        $request = file_get_contents('php://input');
        $data = json_decode($request, true);
        $object = new stdClass();

        $object->quantity = $data['quantity'];
        $object->balance_quantity = $data['balance_quantity'];
        $object->start_quantity = $data['start_quantity'];
        $object->division_edrpou = $data['division_edrpou'];
        $object->parent_id = (int)$data['parent_id'];
		$object->remnant_id = (int)$data['remnant_id'];
		$object->remnant_old_id = (int)$data['remnant_old_id'];
        $object->invoice_num = $data['invoice_num'];
        $object->invoice_date = $data['invoice_date'];

        $del = $_GET['del'];
        $update = $_GET['update'];

        if ($dev ==1 ) $userId = 539;
        if ($userId == 0) {
            return $this->error('Для запиту інформації необхідно пройти авторизацію. Спробуйте оновити сторінку та авторизуватись.');
        }

        if ($del == true) {
            $this->db->setQuery("DELETE FROM med_dp_remnants
WHERE id = '" . addslashes($data['remnant_id']) . "'");
            $this->db->execute();
            $this->db->setQuery("DELETE FROM med_dp_consumption
WHERE remnant_id = '" . addslashes($data['remnant_id']) . "'");
            $this->db->execute();
			
			$this->db->setQuery("SELECT sum(r.quantity) 
	FROM med_dp_remnants r
	WHERE r.parent_id = '" . addslashes($data['remnant_old_id']) . "'");
			$quantity_query = $this->db->loadAssocList();

			$distributed_quantity = NULL;
			if (!empty($quantity_query)) {
				$distributed_quantity = $quantity_query[0]['sum(r.quantity)'];
			}
			
			$this->db->setQuery("UPDATE med_dp_remnants SET date_time=now(),distributed_quantity='" . addslashes($distributed_quantity) . "' WHERE id='" . addslashes($data['remnant_old_id']) . "'"); //
            $this->db->execute();
			
			$this->db->setQuery("SELECT r.quantity - r.distributed_quantity - sum(ifnull(c.cons_quantity,0)+ ifnull(c.broken_quantity,0)) AS balance_quantity 
FROM med_dp_remnants r
JOIN med_dp_consumption c ON c.remnant_id = r.id
WHERE r.id = '" . addslashes($data['remnant_old_id']) . "'");
			$balance_query = $this->db->loadAssocList();

			$balance_quantity = NULL;
			if (!empty($balance_query)) {
				$balance_quantity = $balance_query[0]['balance_quantity'];
			}
			
			$this->db->setQuery("UPDATE med_dp_remnants SET date_time=now(),balance_quantity='" . addslashes($balance_quantity) . "' WHERE id='" . addslashes($data['remnant_old_id']) . "'"); //
            $this->db->execute();
			
			$this->db->setQuery("SELECT r.quantity- ifnull(r.distributed_quantity,0) - IFNULL(cons, 0) start_quantity
FROM med_dp_remnants r
left JOIN (SELECT sum(IFNULL(c.cons_quantity, 0)+IFNULL(c.broken_quantity, 0)) cons, c.remnant_id
FROM med_dp_consumption c
where c.active = 0 AND c.remnant_id = '" . addslashes($data['remnant_old_id']) . "'
GROUP BY c.remnant_id) k ON k.remnant_id = r.id
WHERE r.id = '" . addslashes($data['remnant_old_id']) . "'");
			$start_quantity_query = $this->db->loadAssocList();

			$start_quantity = NULL;
			if (!empty($start_quantity_query)) {
				$start_quantity = $start_quantity_query[0]['start_quantity'];
			}
			
			$this->db->setQuery("UPDATE med_dp_consumption SET date_time=now(), start_quantity='" . addslashes($start_quantity) . "' WHERE id='" . addslashes($data['parent_id']) . "'"); //
            $this->db->execute();
        } elseif ($update == True) {
            $this->db->setQuery("SELECT CONCAT(v6.value, ' / ', v.value), v.item_id
FROM jos_fields_values v
JOIN jos_fields_values v6 ON v6.item_id = v.item_id AND v6.field_id = 6 AND v.field_id = 2
JOIN jos_users u ON u.id = v.item_id AND u.block = 0
JOIN jos_user_usergroup_map m ON m.user_id = v.item_id AND m.group_id = 261
WHERE v6.value = '" . addslashes($data['division_edrpou']) . "'");
            $userid_query = $this->db->loadAssocList();

            $userid = '';
            if (!empty($userid_query)) {
                $userid = $userid_query[0]['item_id'];
            }

            $this->db->setQuery("UPDATE med_dp_remnants SET date_time=now(),quantity='" . addslashes($data['quantity']) . "',division_edrpou='" . addslashes($data['division_edrpou']) . "',userid=$userid,invoice_num='" . addslashes($data['invoice_num']) . "',invoice_date='" . addslashes($data['invoice_date']) . "' WHERE id='" . addslashes($data['remnant_id']) . "'"); //
            $this->db->execute();
			
			$this->db->setQuery("SELECT sum(r.quantity) 
	FROM med_dp_remnants r
	WHERE r.parent_id = '" . addslashes($data['remnant_old_id']) . "'");
			$quantity_query = $this->db->loadAssocList();

			$distributed_quantity = NULL;
			if (!empty($quantity_query)) {
				$distributed_quantity = $quantity_query[0]['sum(r.quantity)'];
			}
			
			$this->db->setQuery("UPDATE med_dp_remnants SET date_time=now(),distributed_quantity='" . addslashes($distributed_quantity) . "' WHERE id='" . addslashes($data['remnant_old_id']) . "'"); //
            $this->db->execute();
			
			$this->db->setQuery("SELECT r.quantity - r.distributed_quantity - sum(ifnull(c.cons_quantity,0)+ ifnull(c.broken_quantity,0)) AS balance_quantity 
FROM med_dp_remnants r
JOIN med_dp_consumption c ON c.remnant_id = r.id
WHERE r.id = '" . addslashes($data['remnant_old_id']) . "'");
			$balance_query = $this->db->loadAssocList();

			$balance_quantity = NULL;
			if (!empty($balance_query)) {
				$balance_quantity = $balance_query[0]['balance_quantity'];
			}
			
			$this->db->setQuery("UPDATE med_dp_remnants SET date_time=now(),balance_quantity='" . addslashes($balance_quantity) . "' WHERE id='" . addslashes($data['remnant_old_id']) . "'"); //
            $this->db->execute();
			
			$this->db->setQuery("SELECT r.quantity- ifnull(r.distributed_quantity,0) - IFNULL(cons, 0) start_quantity
FROM med_dp_remnants r
left JOIN (SELECT sum(IFNULL(c.cons_quantity, 0)+IFNULL(c.broken_quantity, 0)) cons, c.remnant_id
FROM med_dp_consumption c
where c.active = 0 AND c.remnant_id = '" . addslashes($data['remnant_old_id']) . "'
GROUP BY c.remnant_id) k ON k.remnant_id = r.id
WHERE r.id = '" . addslashes($data['remnant_old_id']) . "'");
			$start_quantity_query = $this->db->loadAssocList();

			$start_quantity = NULL;
			if (!empty($start_quantity_query)) {
				$start_quantity = $start_quantity_query[0]['start_quantity'];
			}
			
			$this->db->setQuery("UPDATE med_dp_consumption SET date_time=now(),userid=$userid, start_quantity='" . addslashes($start_quantity) . "' WHERE id='" . addslashes($data['parent_id']) . "'"); //
            $this->db->execute();
        }
        // список єдрпоу
        $this->db->setQuery("SELECT v6.value
FROM jos_fields_values v6
JOIN jos_users u ON u.id = v6.item_id AND u.block = 0
JOIN jos_user_usergroup_map m ON m.user_id = u.id AND m.group_id = 261
WHERE v6.field_id = 6");

        $edrpou_list = $this->db->loadAssocList();


        // дата накладної
        $this->db->setQuery("SELECT
  CONCAT(
    DATE_FORMAT(max(c.start_date), '%Y-%m-%d'),
    ' and ',
    DATE_FORMAT(MAX(c.end_date), '%Y-%m-%d')
  )
  AS `date`
FROM
  meddata.med_dp_consumption c;");

        $invoice_date = $this->db->loadAssocList();

        return ['success' => true, 'edrpou_list' => $edrpou_list, 'invoice_date' => $invoice_date];
    }
}