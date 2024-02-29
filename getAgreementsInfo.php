<?php
 public
    function getAgreementsInfo()
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

        $db->setQuery("SELECT p.id, p.type, p.subtype, p.mnn_id, p.name, p.release_form, p.dosage, p.s20, p.publisher, p.year, p.tender_quantity, a.id AS ann_id, a.plan_id, a.tender_id, a.fact_announce_date, a.trade_name, a.manufacturer, a.manufacturer_country, a.agreement_fact_date, a.agreement_num, a.agreement_value, a.quantity, a.quantity_du, a.proc_sum_du, a.publisher_singing, a.delivery_date, a.agreement_final_date, a.prozorro_report_date, a.`status` FROM med_dp_announcement a
LEFT JOIN med_dp_yearly_plan p ON a.plan_id = p.id");
        $rows = $db->loadAssocList();

        return json_encode(['success' => true, 'data' => $rows]);
    }
