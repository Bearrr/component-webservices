<?php


class GetApplicationMnnComments extends aJWSApiMethod
{
    protected function dispatch()
    {
        $user = JFactory::getUser();

        $dev = $this->app->input->get('dev', 0);

        if ($dev || isset($user->groups[268]) || isset($user->groups[328]) || isset($user->groups[274]) || isset($user->groups[327])) {
            $type_code = $this->app->input->get('type_code');
            $region = addslashes($_GET['region']);
            $zoz_name = addslashes($_GET['zoz_name']);
            $mnn_id = (int)$this->app->input->get('mnn_id');

            $query = "SELECT t.userid  FROM tmp_application_status_mnn t
WHERE t.zoz_name =  '".$zoz_name."' and mnn_id=$mnn_id";

            $this->db->setQuery($query);
            $user_to = $this->db->loadResult();

            $this->db->setQuery("SELECT a.*, b1.value AS user_from_name, b2.value AS user_to_name from med_application_comments a
Left JOIN jos_fields_values b1 ON a.user_from = b1.item_id AND b1.field_id = 2
left JOIN jos_fields_values b2 ON a.user_to = b2.item_id AND b2.field_id = 2
where mnn_id = $mnn_id  and (user_from=$user_to or user_to=$user_to)");
            $rows = $this->db->loadAssocList();


            return ['success'=>true, 'data'=>$rows];
        }

        return $this->error('Для запиту інформації необхідно пройти авторизацію. Спробуйте оновити сторінку та авторизуватись.');
    }
}
