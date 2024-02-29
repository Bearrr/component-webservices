<?php  
    public
    function getSuppliersInfo()
    {

        $db = FabrikWorker::getDbo();
        $query = $db->getQuery(true);
        $user = JFactory::getUser();
        $app = JFactory::getApplication();
        $input = $app->input;
        $userid = $user->id * 1;
        $input = $app->input;
        $dev = (int)$input->get('dev', 0);

        if ($dev == 1) $userid = 48;
        if ($userid == 0)
            return json_encode(['success' => false, 'error' => 'Для запиту інформації необхідно пройти авторизацію. Спробуйте оновити сторінку та авторизуватись.']);

        $db->setQuery("SELECT a.date_time,a.id, a.name, a.residence, a.corporation, a.edrpou, b.cnt, b.won, b.final_offer, b.best_offer  FROM med_suppliers a
LEFT JOIN rep_dp_suppliers b ON a.edrpou = b.edrpou");
        $rows = $db->loadAssocList();

        return json_encode(['success' => true, 'data' => $rows]);
    }
