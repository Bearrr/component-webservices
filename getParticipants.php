<?php
    public
    function getParticipants()
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

        $db->setQuery("SELECT a.id, a.date_time, a.tender_id, a.mnn_name, a.status_participants, a.edrpou_participants, a.names_participants, a.participants_country, a.offers_count, a.`type`, a.applicant, a.trade_name FROM med_dp_participants a ");
        $rows = $db->loadAssocList();

        $program = ['Виробник', 'Дистрибьютор'];

        return json_encode(['success' => true, 'data' => $rows, 'program'=> $program]);
    }
