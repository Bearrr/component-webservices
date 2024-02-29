<?php
    public function getKawasakiDetails()
    {

        $db = FabrikWorker::getDbo();
        $app = JFactory::getApplication();

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

        $db->setQuery("SELECT a.id, a.type_code, a.userid, a.yearly_sum FROM med_met_stat a WHERE a.userid = $userid AND a.type_code = '" . $rows[0]['type_code'] . "'"); //
        $rows = $db->loadAssocList();

        return json_encode(['success' => true, 'data' => $rows]);
    }
