<?php

    public function saveGepatitsDetailsPatients()
    {

        $db = FabrikWorker::getDbo();
        $app = JFactory::getApplication();
        $request = file_get_contents('php://input');
        $data = json_decode($request, true);
        $user = JFactory::getUser();
        $userid = $user->id * 1;
        $input = $app->input;
        $dev = (int)$input->get('dev', 0);
        $diagnos = $input->get('diagnos', 0);
        $type_code = $input->get('type_code', 0);

        if ($dev == 1) $userid = 509;
        if ($userid == 0)
            return json_encode(['success' => false, 'error' => 'Для запиту інформації необхідно пройти авторизацію. Спробуйте оновити сторінку та авторизуватись.']);

        $db->setQuery("SELECT * FROM med_type a WHERE a.id = '" . $data['type_code'] . "'");
        $rows = $db->loadAssocList();
        if (count($rows) == 0) return json_encode(['success' => false, 'error' => 'Цього напряму не існує']);

        $curr_date = date("Y-m-d H:i:s");
        $object = new stdClass();
        $object->id = (int)$data['id'];
        $object->date_time = $curr_date;
        $object->num = (int)$data['num'];
        $object->type_code = $type_code;
        $object->name = $data['name'];
        $object->city = $data['city'];
        $object->cogort1 = $data['cogort1'];
        $object->cogort2 = $data['cogort2'];
        $object->cogort3 = $data['cogort3'];
        $object->cogort4 = $data['cogort4'];
        $object->cogort5 = $data['cogort5'];
        $object->cogort6 = $data['cogort6'];
        $object->cnt = (int)$data['cnt'];
        $object->patients = $data['patients'];
        $object->diagnos = $diagnos;
        $object->diagnos11 = $data['diagnos11'];
        $object->alt_ul = $data['alt_ul'];
        $object->virus_load = $data['virus_load'];
        $object->weight = $data['weight'];
        $object->userid = $userid;
        $updateNulls = true;

        if (array_key_exists('id', $data)) {
            $result = JFactory::getDbo()->updateObject('med_met_patients', $object, 'id', $updateNulls);
            $data['process'] = 'update';
        } else {
            $db = JFactory::getDbo();
            $data['process'] = 'insert';
            $result = $db->insertObject('med_met_patients', $object, 'id');
            $new_id = $object->id;
            $data['id'] = $new_id;
        }


        return json_encode(['success' => true, 'data' => $data]);
    }
