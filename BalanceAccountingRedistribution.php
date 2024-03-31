<?php


class BalanceAccountingRedistribution extends aJWSApiMethod
{
    protected function dispatch()
    {
        $userId = $this->getUserId();
        $dev = $this->app->input->get('dev', 0);

        $rid = $_GET['rid'];

        if ($dev ==1 ) $userId = 539;
        if ($userId == 0) {
            return $this->error('Для запиту інформації необхідно пройти авторизацію. Спробуйте оновити сторінку та авторизуватись.');
        }


        $this->db->setQuery("SELECT r.id AS remnants_id, p.id AS remnant_old_id, n.name, n.dosage, n.unit, l.trade_name, l.series, l.expire_date, l.multiplicity, r.balance_quantity, n.excl_mult
FROM meddata.med_dp_remnants r 
JOIN meddata.med_dp_logistics_supply s ON s.id = r.supply_id and s.inactive = 0
JOIN meddata.med_dp_logistics l ON l.id = s.remnants_id
JOIN meddata.med_name n ON n.id = l.mnn_id
LEFT JOIN med_dp_remnants p ON p.id = r.parent_id
Where r.id IN ($rid)");

        $balance = $this->db->loadAssocList();

        // список отримувачів
        $this->db->setQuery("SELECT CONCAT(v6.value, ' / ', v.value) AS recipient
FROM  jos_fields_values v 
JOIN jos_fields_values v6 ON v6.item_id = v.item_id AND v6.field_id = 6 AND v.field_id = 2
JOIN jos_users u ON u.id = v.item_id AND u.block = 0
JOIN jos_user_usergroup_map m ON m.user_id = v.item_id AND m.group_id = 261
");
        $recipient = $this->db->loadAssocList();

        // дата накладної
        $this->db->setQuery("SELECT max(c.end_date) AS date_from, CAST(DATE_FORMAT(DATE_add(NOW(),INTERVAL 14 day) ,'%Y-%m-%d') as DATE) AS date_to
FROM med_dp_consumption c");

        $invoice_date = $this->db->loadAssocList();
		

        return ['success' => true, 'data' => $balance, 'recipient' => $recipient, '$invoice_date' => $invoice_date];
    }
}