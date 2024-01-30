<?php  
    public function getGepatitsDetailsPatients()
    {

        $db = FabrikWorker::getDbo();
        $app = JFactory::getApplication();

        $user = JFactory::getUser();
        $userid = $user->id * 1;
        $input = $app->input;
        $dev = (int)$input->get('dev', 0);
        $type_id = (int)$input->get('type_id', 0);
        $type_code = $input->get('type_code', 0);
        $diagnos = $input->get('diagnos', 0);
        if ($dev == 1) $userid = 509;
        if ($userid == 0)
            return json_encode(['success' => false, 'error' => 'Для запиту інформації необхідно пройти авторизацію. Спробуйте оновити сторінку та авторизуватись.']);

        $db->setQuery("SELECT * FROM med_type a WHERE a.id = $type_id");
        $rows = $db->loadAssocList();
        if (count($rows) == 0) return json_encode(['success' => false, 'error' => 'Цього напряму не існує']);

        if ($type_code == 'gepatit_c_2024') {
            $db->setQuery("SELECT a.id, a.num, a.type_code, a.cogort6,  a.city,  a.cogort1, a.cogort2, a.cogort3, a.cogort4, a.cnt, a.patients, a.userid FROM med_met_patients a WHERE a.userid = 509 AND a.type_code = 'gepatit_c_2024'");

            $cogort2 = ['всі генотипи',
                '1а, 3,4,5,6',
                '2,3,5,6',
                '2',
                '3',
                '1а, 1в,4',
                '1а, 1в',
                '4'
                ];

            $cogort3 = ['з цирозом',
                'незалежно від стадії фіброзу',
                'F0-F2',
                'з рівнем фіброзу ≥ 3'];

            $cogort4 = ['без досвіду лікування',
                'без досвіду лікування ПППД та/або досвідом лікування пегінтерферон–альфа та рибавірином',
                'неуспішне лікування ПППД'];

        }
        elseif ($type_code == 'gepatit_b_2024') {
            $db->setQuery("SELECT a.id, a.num, a.type_code, a.cogort6,  a.city,  a.cogort1, a.cogort2, a.cogort3,a.diagnos11, a.alt_ul, a.virus_load, a.cnt, a.patients, a.userid FROM med_met_patients a WHERE a.userid = 509 AND a.type_code = 'gepatit_b_2024'");

            $cogort2 = ['А',
                'Не має значення'];


            $cogort3 = ['незалежно від стадії фіброзу',
                'з рівнем фіброзу ≥ F2',
                'з цирозом'];

            $virus_load = ['> 2 000',
                'Не має значення'];
        }
        elseif ($type_code == 'gepatit_child_2024')
            if ($diagnos == 'HVB') {
                $db->setQuery("SELECT a.id, a.num, a.type_code, a.cogort6,  a.city,  a.cogort1, a.cogort2, a.cogort3,a.cogort5, a.weight,a.diagnos, a.diagnos11, a.alt_ul, a.virus_load, a.cnt, a.patients, a.userid FROM med_met_patients a WHERE a.userid = 509 AND (a.type_code = 'gepatit_child_2024'
AND a.diagnos = 'HVB')"); //
                $cogort5 = ['> 3 років',
                    '≥ 12 років',
                    '> 6 років (>30кг)'];

                $weight = ['<30',
                    '> 30'];

                $cogort2 = ['А',
                    'Не має значення'];


                $cogort3 = ['незалежно від стадії фіброзу',
                    'з рівнем фіброзу ≥ F2',
                    'з цирозом'];

                $virus_load = ['> 2 000',
                    'Не має значення'];
            }
            else {
                $db->setQuery("SELECT a.id, a.num, a.type_code, a.cogort6,  a.city,  a.cogort1, a.cogort2, a.cogort3,a.cogort4,a.cogort5, a.weight,a.diagnos, a.cnt, a.patients,a.duration, a.userid FROM med_met_patients a WHERE a.userid = 509 AND (a.type_code = 'gepatit_child_2024'
AND a.diagnos = 'HVC')"); //
                $cogort5 = ['віком 3-11 років',
                    'віком 12+ років'];

                $weight = ['17-30',
                    '17-35',
                    '≥ 30',
                    '≥ 35'];

                $cogort2 = ['2',
                    '3', '1а, 1б, 2, 3, 4, 5, 6',
                    '1а, 1б, 4, 5, 6'];


                $cogort3 = ['без цирозу',
                    'з компенсованим цирозом',
                    'з декомпенсованим цирозом'];

                $cogort4 = ['неуспішне лікування ПППД',
                    'без досвіду лікування або з досвідом лікування пегінтерферон–альфа та рибавірином'];

            }

        $rows = $db->loadAssocList();

        $cogort6 = ['Так'=>1,
        'Ні'=>0];

        $patients = ['наявні пацієнти',
            'очікувані'];

        $diagnos11 = ['Так',
            'Ні',
            'Не має значення'];

        $cogort1 = ['без ниркової недостатності',
            'з нирковою недостатністю'];

        $alt_ul = ['Так',
            'Ні',
            'Не має значення'];

        $duration = ['12 тижнів',
            '24 тижні'];

        return json_encode(['success' => true, 'data' => $rows,'cogort1'=>$cogort1,'cogort2'=>$cogort2,'cogort3'=>$cogort3,'cogort4'=>$cogort4,'cogort5'=>$cogort5,'cogort6'=>$cogort6, 'patients'=>$patients,'diagnos11'=>$diagnos11,'alt_ul'=>$alt_ul,'duration'=>$duration,'weight'=>$weight, 'virus_load'=>$virus_load]);
    }
