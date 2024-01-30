<?php 
public
    function getAnnualPlanForm()
    {

        $db = FabrikWorker::getDbo();
        $query = $db->getQuery(true);
        $user = JFactory::getUser();
        $app = JFactory::getApplication();
        $input = $app->input;
        $userid = $user->id * 1;
        $input = $app->input;
        $dev = (int)$input->get('dev', 0);
        $year = (int)$input->get('year', 0);

        if ($dev == 1) $userid = 48;
        if ($userid == 0)
            return json_encode(['success' => false, 'error' => 'Для запиту інформації необхідно пройти авторизацію. Спробуйте оновити сторінку та авторизуватись.']);

        $db->setQuery("SELECT * FROM med_dp_yearly_plan a 
WHERE a.year ='".$year."'");
        $rows = $db->loadAssocList();

        $db->setQuery("SELECT * FROM med_type a 
WHERE year='".$year."' and io='ДП \"МЗУ\"'");
        $types = $db->loadAssocList();

        $db->setQuery("SELECT * FROM med_dp_staff a 
WHERE dep = 'procurements'
ORDER BY name");
        $publisher = $db->loadAssocList();

        $program = ['U24', '1400'];

        return json_encode(['success' => true, 'data' => $rows,'types'=>$types, 'publisher'=>$publisher, 'program'=>$program]);
    }
