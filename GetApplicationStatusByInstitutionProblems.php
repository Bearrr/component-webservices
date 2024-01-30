<?php


class GetApplicationStatusByInstitutionProblems extends aJWSApiMethod
{
    protected function dispatch()
    {
        $user = JFactory::getUser();

        $dev = $this->app->input->get('dev', 0);

        if ($dev || isset($user->groups[268]) || isset($user->groups[328]) || isset($user->groups[274]) || isset($user->groups[327])) {
            $type_code = $this->app->input->get('type_code');
            $userid = $this->app->input->get('userid');

            $query = "SELECT
	t.region, 
	t.zoz_name, 
	t.num, 
	t.name, 
	t.release_form, 
	t.dosage, 
	t.unit, 
	IFNULL(t.balance_total, 'Не вказано') AS balance_total,
	IFNULL(t.balance_local, 'Не вказано') AS balance_local,
	IFNULL(t.balance_lowterm, 'Не вказано') AS balance_lowterm,
	IFNULL(t.expected_deliveries_local, 'Не вказано') AS expected_deliveries_local,
	IFNULL(t.yearly_need, 'Не вказано') AS yearly_need,
	IFNULL(t.yearly_need_2023, 'Не вказано') AS yearly_need_2023,
	IFNULL(t.yearly_need_2024, 'Не вказано') AS yearly_need_2024                                                                                                                                                                                                        
FROM tmp_application_status_mnn t                                                                                                                                                                                
WHERE t.type_code = '" . $type_code . "' AND t.userid = '" . $userid . "'                                                                                                                                  
AND (
		(t.balance_total IS NULL 
  	 OR t.balance_local IS NULL 
  	 OR t.balance_lowterm IS NULL 
  	 OR t.expected_deliveries_local IS NULL 
  	 OR t.yearly_need IS NULL 
	 OR t.yearly_need_2023 IS NULL 
  	 OR t.yearly_need_2024 IS NULL)
OR (t.yearly_need > 0 AND (t.yearly_need_2023 = 0 OR t.yearly_need_2024 = 0))
)";
            $this->db->setQuery($query);

            return ['is_doz' => $dev == 2 || isset($user->groups[327]), 'data' => $this->db->loadAssocList()];
        }

        return $this->error('Для запиту інформації необхідно пройти авторизацію. Спробуйте оновити сторінку та авторизуватись.');
    }
}
