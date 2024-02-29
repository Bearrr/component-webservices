<?php
    public function saveKawasakiDetails()
    {

        $db = FabrikWorker::getDbo();
        $app = JFactory::getApplication();
        $request = file_get_contents('php://input');
        $data = json_decode($request, true);
        $user = JFactory::getUser();
        $userid = $user->id * 1;
        $input = $app->input;
        $dev = (int)$input->get('dev', 0);
        $type_id = (int)$input->get('type_id', 0);

        if ($dev == 1) $userid = 509;
        if ($userid == 0)
            return json_encode(['success' => false, 'error' => 'Для запиту інформації необхідно пройти авторизацію. Спробуйте оновити сторінку та авторизуватись.']);


        $db->setQuery("SELECT * FROM med_type a WHERE a.id = $type_id");
        $rows = $db->loadAssocList();
        if (count($rows) == 0) return json_encode(['success' => false, 'error' => 'Цього напряму не існує']);

        $curr_date = date("Y-m-d H:i:s");
        $object = new stdClass();
        $object->id = (int)$data['id'];
        $object->date_time = $curr_date;
        $object->type_code = $rows[0]['type_code'];
        $object->userid = $userid;
        $object->yearly_sum = (int)$data['yearly_sum'];
        $updateNulls = true;

        if (array_key_exists('id', $data)) {
            $result = JFactory::getDbo()->updateObject('med_met_stat', $object, 'id', $updateNulls);
            $data['process'] = 'update';
        } else {
            $db = JFactory::getDbo();
            $data['process'] = 'insert';
            $result = $db->insertObject('med_met_stat', $object, 'id');
            $new_id = $object->id;
            $data['id'] = $new_id;
        }

        $query = "SELECT ROUND(yearly_sum*10) cnt
 from med_met_stat s
where userid=$userid and type_code='imuno_kavasaki_2024'
";

        $db->setQuery($query);
        $result = $db->loadAssocList();
        foreach ($result as $pr => $pill) {
            $q = "update med_application set yearly_need_multiplicity=" . $pill['cnt'] . " where userid=$userid and pillid=17167";
            $db->setQuery($q);
            $db->execute();
        }

        $db->execute();

        return json_encode(['success' => true, 'data' => $data]);
    }
