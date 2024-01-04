<?php


class GetApplicationStatusByPosition extends aJWSApiMethod
{
    protected function dispatch()
    {
        $user = JFactory::getUser();
        $userid = $this->getUserId();

        $dev = $this->app->input->get('dev', 0);

        if ($dev || isset($user->groups[268]) || isset($user->groups[328]) || isset($user->groups[274]) || isset($user->groups[327])) {
            $type_code = $this->app->input->get('type_code');
            $region = addslashes($_GET['region']);
            $zoz_name = addslashes($_GET['zoz_name']);
            $mnn_id = $this->app->input->get('mnn_id');

            $query = "SELECT 
	t.num,
	t.mnn_id,
	t.name,
	t.release_form,
	t.dosage,
	t.unit,
	SUM(IFNULL(t.balance_total, 0)) AS balance_total,
	SUM(IFNULL(t.balance_local, 0)) AS balance_local,
	SUM(IFNULL(t.balance_lowterm, 0)) AS balance_lowterm,
	SUM(IFNULL(t.expected_deliveries_local, 0)) AS expected_deliveries_local,
	SUM(IFNULL(t.yearly_need, 0)) AS yearly_need,
	SUM(IFNULL(t.yearly_need_2023, 0)) AS yearly_need_2023,
	SUM(IFNULL(t.yearly_need_2024, 0)) AS yearly_need_2024,
	MAX(t.updated) AS updated,
	MAX(t.last_auth) AS last_auth,
	t.sign,
	t.sign_date,
	SUM(t.coment_all) AS coment_all,
	SUM(t.coment_active) AS coment_active,
	MAX(t.coment_last_mpu) AS coment_last_mpu,
	MAX(t.coment_last_zoz) AS coment_last_zoz,
	c.notification_count
FROM tmp_application_status_mnn t
LEFT JOIN (SELECT mnn_id,SUM(notification) AS notification_count, user_from from med_application_comments  
where user_to=$userid GROUP BY mnn_id, user_from) c ON t.userid = c.user_from AND t.mnn_id = c.mnn_id
WHERE t.type_code = '" . $type_code . "'";

            if (!empty($region)) {
                $query .= " AND t.region = '" . $region . "'";
            }
            if (!empty($zoz_name)) {
                $query .= " AND t.zoz_name = '" . $zoz_name . "'";
            }

            if (!empty($mnn_id)) {
                $query .= " AND t.mnn_id = '" . $mnn_id . "'";
            }

            $query .= " GROUP BY t.num";

            $this->db->setQuery($query);

            return ['is_doz' => $dev == 2 || isset($user->groups[327]), 'data' => $this->db->loadAssocList()];
        }

        return $this->error('Для запиту інформації необхідно пройти авторизацію. Спробуйте оновити сторінку та авторизуватись.');
    }
}
