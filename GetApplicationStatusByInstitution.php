<?php


class GetApplicationStatusByInstitution extends aJWSApiMethod
{
    protected function dispatch()
    {
        $user = JFactory::getUser();

        $dev = $this->app->input->get('dev', 0);

        if ($dev == 1 || isset($user->groups[268]) || isset($user->groups[328]) || isset($user->groups[274]) || isset($user->groups[327])) {
            $type_code = $this->app->input->get('type_code');
            $region = addslashes($_GET['region']);
            $query = "SELECT * FROM tmp_application_status_zoz t WHERE t.type_code = '" . $type_code . "'";

            if (!empty($region)) {
                $query .= " AND t.region = '" . $region . "'";
            }

            $this->db->setQuery($query);

            return ['is_doz' => $dev == 2 || isset($user->groups[327]), 'data' => $this->db->loadAssocList()];
        }

        return $this->error('Для запиту інформації необхідно пройти авторизацію. Спробуйте оновити сторінку та авторизуватись.');
    }
}
