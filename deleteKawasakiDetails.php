<?php
public function deleteKawasakiDetails()
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

        $q = "delete from med_met_stat where id = $object->id";
        $db->setQuery($q);
        $db->execute();

        $query = "SELECT ROUND(yearly_sum*10) cnt
 from med_met_stat s
where userid=$userid and type_code='imuno_kavasaki_2024'
";

        $db->setQuery($query);
        $result = $db->loadAssocList();
        if (!empty($result)) {
            foreach ($result as $pr => $pill) {
                $q = "update med_application set yearly_need_multiplicity=" . $pill['cnt'] . " where userid=$userid and pillid=17167";
                $db->setQuery($q);
                $db->execute();
            }
        } else {
            $q = "update med_application set yearly_need_multiplicity=0 where userid=$userid and pillid=17167";
            $db->setQuery($q);
            $db->execute();
        }

        return json_encode(['success' => true]);
    }
