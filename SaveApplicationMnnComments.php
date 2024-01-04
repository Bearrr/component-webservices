<?php

class SaveApplicationMnnComments extends aJWSApiMethod
{
    protected function dispatch()
    {
        $user = JFactory::getUser();
        $userid = $this->getUserId();

        $dev = $this->app->input->get('dev', 0);
        $request = file_get_contents('php://input');
        $data = json_decode($request, true);

        if ($dev || isset($user->groups[268]) || isset($user->groups[328]) || isset($user->groups[274]) || isset($user->groups[327])) {
            $type_code = $this->app->input->get('type_code');
            $region = addslashes($_GET['region']);
            $zoz_name = addslashes($_GET['zoz_name']);
            $mnn_id = (int)$this->app->input->get('mnn_id');

            $curr_date = date("Y-m-d H:i:s");
            $object = new stdClass();
            //$object->id = (int)$data['id'];
            $object->row_id = (int)$data['row_id'];
            $object->type_id = (int)$data['type_id'];
            $object->mnn_id =  $mnn_id;
            $object->user_from =  $userid;
            $object->comment = $data['comment'];
            $object->sended = $curr_date;
            $object->notification = 1;

//            $object->row_id = 111;
//            $object->type_id = 474;
//            $object->mnn_id =  15115;
//            $object->user_from =  $userid;
//            $object->comment = 'Тестовий коментар з дашборду';
//            $object->sended = $curr_date;
//            $object->notification = 1;

            $query = "SELECT t.userid  FROM tmp_application_status_mnn t
WHERE t.zoz_name =  '".$zoz_name."' and mnn_id=$mnn_id";

            $this->db->setQuery($query);
            $object->user_to = $this->db->loadResult();

            $this->db->insertObject('med_application_comments', $object, 'id');
            $data['id'] = $this->db->insertid();


            return ['success'=>true, 'data'=>$data];
        }

        return $this->error('Для запиту інформації необхідно пройти авторизацію. Спробуйте оновити сторінку та авторизуватись.');
    }
}
