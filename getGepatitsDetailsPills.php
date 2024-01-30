<?php 
   public function getGepatitsDetailsPills()
    {

        $db = FabrikWorker::getDbo();
        $app = JFactory::getApplication();

        $user = JFactory::getUser();
        $userid = $user->id * 1;
        $input = $app->input;
        $dev = (int)$input->get('dev', 0);
        $patient_id = (int)$input->get('patient_id', 0);
        $type_code = $input->get('type_code', 0);
        $diagnos = $input->get('diagnos', 0);
        if ($dev == 1) $userid = 509;
        if ($userid == 0)
            return json_encode(['success' => false, 'error' => 'Для запиту інформації необхідно пройти авторизацію. Спробуйте оновити сторінку та авторизуватись.']);


        $db->setQuery("SELECT a.id, a.patient_id, a.name, a.yearly_cnt,a.yearly_sum, a.active, a.cnt  FROM med_met_pill a WHERE a.patient_id =$patient_id"); //
        $rows = $db->loadAssocList();
        $dat = [];

        if ($type_code == 'gepatit_c_2024')
            $db->setQuery("SELECT id AS mnn_id, concat(NAME,' ', a.subtype, ' / ', a.dosage) AS name FROM med_name a WHERE a.type_code = 'gepatit_2024' AND a.id BETWEEN 17152 AND 17156 or a.id = 17165"); //
        elseif ($type_code == 'gepatit_b_2024')
            $db->setQuery("SELECT id AS mnn_id, concat(NAME,' ', a.subtype, ' / ', a.dosage) AS name FROM med_name a WHERE a.type_code = 'gepatit_2024' AND a.id BETWEEN 17143 AND 17145 OR a.id = 17147 or a.id =17166"); //
        elseif ($type_code == 'gepatit_child_2024'){
            if ($diagnos == 'HVB')
                $db->setQuery("SELECT id AS mnn_id, concat(NAME,' ', a.subtype, ' / ', a.dosage) AS name FROM med_name a WHERE a.type_code = 'gepatit_2024' AND a.id BETWEEN 17143 AND 17147 or a.id =17166"); //
            else
                $db->setQuery("SELECT id AS mnn_id, concat(NAME,' ', a.subtype, ' / ', a.dosage) AS name FROM med_name a WHERE a.type_code = 'gepatit_2024' AND (a.id BETWEEN 17158 AND 17162 OR a.id = 17146 OR a.id = 17152 OR a.id = 17154 or a.id = 17165)"); //

        }
        $pills = $db->loadAssocList();

        return json_encode(['success' => true, 'data' => $rows, 'pills' => $pills]);
    }
