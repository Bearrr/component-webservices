<?php 

    public function saveAnnualPlanForm()
    {

        $db = FabrikWorker::getDbo();
        $query = $db->getQuery(true);
        $app = JFactory::getApplication();

        $user = JFactory::getUser();
        $userid = $user->id * 1;
        $input = $app->input;
        $dev = (int)$input->get('dev', 0);
        if ($dev == 1) $userid = 48;
        if ($userid == 0)
            return json_encode(['success' => false, 'error' => 'Для запиту інформації необхідно пройти авторизацію. Спробуйте оновити сторінку та авторизуватись.']);
        $request = file_get_contents('php://input');
        $data = json_decode($request, true);

        $object = new stdClass();
        $object->id = (int)$data['id'];
        $object->type = $data['type'];
        $object->name = $data['name'];
        $object->tender_id = $data['tender_id'];
        $object->publisher = $data['publisher'];
        $object->estimated_value = (float)$data['estimated_value'];
        $object->tender_quantity = (int)$data['tender_quantity'];
        $object->tender_exp_cost_manual = $data['tender_exp_cost_manual'];
        $object->tender_requirements_manual = $data['tender_requirements_manual'];
        $object->tender_budg_purpose_manual = $data['tender_budg_purpose_manual'];
        $object->participants_email = $data['participants_email'];
        $object->lot_id = $data['lot_id'];
        $object->program = $data['program'];


        if (array_key_exists('id', $data)) {
            $result = JFactory::getDbo()->updateObject('med_dp_yearly_plan', $object, 'id', $updateNulls);
            $data['process'] = 'update';
        } else {
            $db = JFactory::getDbo();
            $data['process'] = 'insert';
            $result = $db->insertObject('med_dp_yearly_plan', $object, 'id');
            $new_id = $object->id;
            $data['id'] = $new_id;
        }

        return json_encode(['success' => true, 'data' => $data]);

    }
