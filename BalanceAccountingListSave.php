<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');


class BalanceAccountingListSave extends aJWSApiMethod
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
		
		foreach ($data as $item) {
			$cons_quantity = $item['cons_quantity'];
			$broken_quantity = $item['broken_quantity'];
			$balance_quantity = $item['balance_quantity'];
			$parent_id = $item['parent_id'];
			$remnant_id = $item['remnant_id'];

			$update_consumption_query = "UPDATE med_dp_consumption SET date_time=now()";
			$update_remnants_query = "UPDATE med_dp_remnants SET date_time=now()";

			if (isset($item['cons_quantity'])) {
				if ($item['cons_quantity'] !== "") {
					$cons_quantity = $item['cons_quantity'];
					$update_consumption_query .= ", cons_quantity='" . addslashes($cons_quantity) . "'";
				} else {
					$update_consumption_query .= ", cons_quantity=NULL";
				}
				$update_consumption_query .= ", user_updated=now()";
			}

			if (isset($item['broken_quantity'])) {
				if ($item['broken_quantity'] !== "") {
					$cons_quantity = $item['broken_quantity'];
					$update_consumption_query .= ", broken_quantity='" . addslashes($broken_quantity) . "'";
				} else {
					$update_consumption_query .= ", broken_quantity=NULL";
				}
				$update_consumption_query .= ", user_updated=now()";
			}

			if (isset($item['balance_quantity'])) {
				$balance_quantity = $item['balance_quantity'];
				$update_consumption_query .= ", balance_quantity='" . addslashes($balance_quantity) . "'";
				$update_remnants_query .= ", balance_quantity='" . addslashes($balance_quantity) . "'";
			}

			$update_consumption_query .= " WHERE id='" . addslashes($parent_id) . "'";
			$update_remnants_query .= " WHERE id='" . addslashes($remnant_id) . "'";

			$this->db->setQuery($update_consumption_query);
			$this->db->execute();
			$this->db->setQuery($update_remnants_query);
			$this->db->execute();
		}

        return ['success' => true];
    }
}