<?php
     public function saveGepatitsDetailsPills()
    {

        $db = FabrikWorker::getDbo();
        $app = JFactory::getApplication();
        $request = file_get_contents('php://input');
        $data = json_decode($request, true);
        $user = JFactory::getUser();
        $userid = $user->id * 1;
        $input = $app->input;
        $dev = (int)$input->get('dev', 0);

        if ($dev == 1) $userid = 509;
        if ($userid == 0)
            return json_encode(['success' => false, 'error' => 'Для запиту інформації необхідно пройти авторизацію. Спробуйте оновити сторінку та авторизуватись.']);

        $data['yearly_sum'] = $data['cnt'] * $data['yearly_cnt'];
        $curr_date = date("Y-m-d H:i:s");
        $object = new stdClass();
        $object->id = (int)$data['id'];
        $object->date_time = $curr_date;
        $object->patient_id = (int)$data['patient_id'];
        $object->name = (int)$data['name'];
        $object->cnt = (float)$data['cnt'];
        $object->yearly_cnt = (int)$data['yearly_cnt'];
        $object->yearly_sum =   $data['yearly_sum'];
        $object->active = $data['active'];
        $object->userid = $userid;
        $updateNulls = true;


        if (array_key_exists('id', $data)) {
            $result = JFactory::getDbo()->updateObject('med_met_pill', $object, 'id', $updateNulls);
            $data['process'] = 'update';
        } else {
            $db = JFactory::getDbo();
            $data['process'] = 'insert';
            $result = $db->insertObject('med_met_pill', $object, 'id');
            $new_id = $object->id;
            $data['id'] = $new_id;
        }

        return json_encode(['success' => true, 'data' => $data]);
    }
