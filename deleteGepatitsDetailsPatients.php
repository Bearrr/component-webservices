<?php 
   public function deleteGepatitsDetailsPatients()
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


        $object = new stdClass();
        $object->id = (int)$data['id'];

        $q = "delete from med_met_patients where id = $object->id";
        $db->setQuery($q);
        $db->execute();

        $q = "delete from med_met_pill where patient_id = $object->id";
        $db->setQuery($q);
        $db->execute();

        return json_encode(['success' => true]);
    }
