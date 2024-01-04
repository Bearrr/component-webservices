<?php


class GetApplicationStatusByType extends aJWSApiMethod
{
    protected function dispatch()
    {
        $user = JFactory::getUser();

        $userId = $this->getUserId();
        $dev = $this->app->input->get('dev', 0);

        $type_code = $this->app->input->get('type_code');

        if ($dev == 2) {
            $userId = 2269;
        }

        $query = "SELECT t.type_code,t.collection_type FROM med_type t WHERE t.year >= 2023 AND t.collection_type = 'zoz'";
        $this->db->setQuery($query);

        $allowedTypes = [];
        foreach ($this->db->loadAssocList() as $_type) {
            $allowedTypes[] = $_type['type_code'];
        }

        if ($dev == 1 || isset($user->groups[268]) || isset($user->groups[328]) || isset($user->groups[274])) {
            $query = "SELECT
t.type,
t.type_code,
t.`year`,
SUM(t.all_num) AS 'all_num',
SUM(t.active) AS 'active',
SUM(t.app_num) AS 'app_num',
SUM(t.kep_num) AS 'kep_num',
SUM(t.problems_num) AS 'problems_num',
CASE WHEN COUNT(DISTINCT t.kep_doz) > 1 THEN 'Ні' ELSE t.kep_doz END AS 'kep_doz',
MAX(t.updated) AS 'updated',
SUM(t.coment_all) AS 'coment_all',
SUM(t.coment_active) AS 'coment_active',
MAX(t.coment_last_mpu) AS 'coment_last_mpu',
MAX(t.coment_last_zoz) AS 'coment_last_zoz',
MAX(t.coment_trigger) AS 'coment_trigger',
t.system_name,
t.start_date,
t.end_date
FROM tmp_application_status_type t";

            if (!empty($type_code)) {
                $query .= " WHERE t.type_code LIKE '%" . $type_code . "%'";
            }

            $query .= " GROUP BY t.type_code";

            $this->db->setQuery($query);

            $collected = [];
            foreach ($this->db->loadAssocList() as $data) {
                $data['zoz_clickable'] = in_array($data['type_code'], $allowedTypes);
                $collected[] = $data;
            }

            return ['is_doz' => false, 'data' => $collected];
        }

        // DOZ
        if ($dev == 2 || isset($user->groups[327])) {

            $query = "SELECT 
	CASE WHEN t.collection_type = 'zoz' THEN r.doz_userid ELSE r.userid END AS userid,
	r.region, 
	t.type_code
FROM med_roles r
JOIN med_type t ON t.id = r.type_id
WHERE t.year >= 2023 AND r.userid = " . $userId . "
GROUP BY CASE WHEN t.collection_type = 'zoz' THEN r.doz_userid ELSE r.userid END, r.region, t.type_code";

            $this->db->setQuery($query);

            $list = $this->db->loadAssocList();

            if (!$list) {
                return [];
            }

            $region = $list[0]['region'];

            $query = "SELECT
t.type,
t.type_code,
t.`year`,
SUM(t.all_num) AS 'all_num',
SUM(t.active) AS 'active',
SUM(t.app_num) AS 'app_num',
SUM(t.kep_num) AS 'kep_num',
SUM(t.problems_num) AS 'problems_num',
CASE WHEN COUNT(DISTINCT t.kep_doz) > 1 THEN 'Ні' ELSE t.kep_doz END AS 'kep_doz',
MAX(t.updated) AS 'updated',
SUM(t.coment_all) AS 'coment_all',
SUM(t.coment_active) AS 'coment_active',
MAX(t.coment_last_mpu) AS 'coment_last_mpu',
MAX(t.coment_last_zoz) AS 'coment_last_zoz',
MAX(t.coment_trigger) AS 'coment_trigger',
t.system_name,
t.start_date,
t.end_date
FROM tmp_application_status_type t WHERE t.region = '" . $region . "'";

            if (!empty($type_code)) {
                $query .= " AND t.type_code LIKE '%" . $type_code . "%'";
            }

            $this->db->setQuery($query);

            $collected = [];
            foreach ($this->db->loadAssocList() as $data) {
                $data['zoz_clickable'] = in_array($data['type_code'], $allowedTypes);
                $collected[] = $data;
            }

            return ['is_doz' => true, 'data' => $collected];
        }

        return $this->error('Для запиту інформації необхідно пройти авторизацію. Спробуйте оновити сторінку та авторизуватись.');
    }
}
