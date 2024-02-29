<?php  
public function dozGetApplication()
    {

        $db = FabrikWorker::getDbo();
        $app = JFactory::getApplication();

        $user = JFactory::getUser();
        $userid = $user->id * 1;
        $input = $app->input;
        $dev = (int)$input->get('dev', 0);
        $type_id = (int)$input->get('type_id', 0);
        $data = [];
        if ($dev == 1)
            $userid = 509;
        if ($userid == 0)
            return json_encode(['success' => false, 'error' => 'Для запиту інформації необхідно пройти авторизацію. Спробуйте оновити сторінку та авторизуватись.']);

        $db->setQuery("
SELECT min(case when app=content then 'Погоджено' ELSE 'Розгляд' END) status
FROM (SELECT a.id,a.userid,a.date_time,a.type_id,CONCAT(ifnull(sum(a.balance_total),0),';',ifnull(sum(a.balance_local),0),';',ifnull(sum(a.balance_lowterm),0),';',ifnull(sum(a.expected_deliveries_local),0),';',
ifnull(sum(a.yearly_need_multiplicity),0),';',
ifnull(sum(a.yearly_need_met),0),';',
ifnull(sum(a.yearly_need_2023),0),';',ifnull(sum(a.yearly_need_2024),0),';',ifnull(sum(a.balance_total_centralized),0),';',ifnull(sum(a.balance_total_6m),0),';',ifnull(sum(a.balance_local_6m),0)) app,IFNULL(v.content,'') content,v.created FROM med_application a
LEFT JOIN med_application_sign_verification_doz v ON a.id = v.id

WHERE a.type_id =$type_id and a.doz_userid =$userid
GROUP BY a.pillid) d");
        $status = $db->loadResult();
        if ($type_id == 484) {
            $db->setQuery("SELECT t.description type,t.year,t.basic_type,a.pillid,a.name,a.subtype,a.release_form,a.dosage,a.unit,sum(a.yearly_need) AS yearly_need,a.id,a.num,a.multiplicity,sign(ifnull(d.id,0)) type_size,
v1.value division_name,v6.value division_edrpou,sum(a.balance_total) AS balance_total,sum(a.balance_local) AS balance_local,sum(a.balance_lowterm) AS balance_lowterm,sum(a.expected_deliveries_local) AS expected_deliveries_local,sum(a.yearly_need_2023) AS yearly_need_2023,sum(a.yearly_need_2024) AS yearly_need_2024,sum(a.yearly_need_multiplicity) AS yearly_need_multiplicity,sum(a.yearly_need_met) as yearly_need_met, a.userid, n.lov_id,sum(a.balance_total_centralized) as balance_total_centralized, sum(a.balance_total_6m) as balance_total_6m, sum(a.balance_local_6m) as balance_local_6m, a.doz_userid
FROM med_application a
JOIN med_type t ON a.type_code = t.type_code
LEFT JOIN jos_fields_values v1 ON a.userid = v1.item_id AND v1.field_id=1
LEFT JOIN jos_fields_values v6 ON a.userid = v6.item_id AND v6.field_id=6
LEFT JOIN med_name_details_type d ON a.pillid=d.id AND d.table_num = 1
LEFT JOIN med_name n ON a.pillid=n.id 
WHERE t.id=$type_id AND doz_userid =$userid and a.pillid IN(16582, 16601, 16625, 16660, 16679)
GROUP BY a.pillid ");
        } else {
            $db->setQuery("SELECT t.description type,t.year,t.basic_type,a.pillid,a.name,a.subtype,a.release_form,a.dosage,a.unit,sum(a.yearly_need) AS yearly_need,a.id,a.num,a.multiplicity,sign(ifnull(d.id,0)) type_size,
	v1.value division_name,v6.value division_edrpou,sum(a.balance_total) AS balance_total,sum(a.balance_local) AS balance_local,sum(a.balance_lowterm) AS balance_lowterm,sum(a.expected_deliveries_local) AS expected_deliveries_local,sum(a.yearly_need_2023) AS yearly_need_2023,sum(a.yearly_need_2024) AS yearly_need_2024,sum(a.yearly_need_multiplicity) AS yearly_need_multiplicity,sum(a.yearly_need_met) as yearly_need_met, a.userid, n.lov_id,sum(a.balance_total_centralized) as balance_total_centralized, sum(a.balance_total_6m) as balance_total_6m, sum(a.balance_local_6m) as balance_local_6m, a.doz_userid
	FROM med_application a
	JOIN med_type t ON a.type_code = t.type_code
	LEFT JOIN jos_fields_values v1 ON a.userid = v1.item_id AND v1.field_id=1
	LEFT JOIN jos_fields_values v6 ON a.userid = v6.item_id AND v6.field_id=6
	LEFT JOIN med_name_details_type d ON a.pillid=d.id AND d.table_num = 1
	LEFT JOIN med_name n ON a.pillid=n.id 
	WHERE t.id=$type_id AND doz_userid =$userid
	GROUP BY a.pillid ");
        }
        $rows = $db->loadAssocList();
        $type = [];
        foreach ($rows as $in => $row) {
            $type['facilityName'] = $row['division_name'];
            $type['directionTypeName'] = $row['basic_type'];
            $type['facilityTaxNumber'] = $row['division_edrpou'];
            $type['id'] = $type_id;
            $type['year'] = $row['year'];
            $type['requisitionNumber'] = crc32($row['type_code']);
            $type['treatmentDirectionName'] = $row['type'];
            $type['status'] = $status;
            if ($row['type_size'] == 1) $typesize = true;
            else $typesize = false;
            $type['lines'][] = ['id' => $row['id'] * 1, 'nomenclatureDictionaryId' => $row['num'] * 1,
                'nomenclatureName' => $row['name'], 'nomenclatureDosage' => $row['dosage'], 'nomenclatureMultiplicity' => $row['multiplicity'] * 1, 'nomenclatureReleaseForm' => $row['release_form'], 'nomenclatureSubtype' => $row['subtype'],
                'nomenclatureUnit' => $row['unit'], 'type_size' => $typesize, 'mnn_id' => $row['pillid'],
                "onStock" => $row['balance_total'],
                "onStockPublicFunding" => $row['balance_local'],
                "onStockExpired" => $row['balance_lowterm'],
                "expectedDeliveries" => $row['expected_deliveries_local'],
                "required100Percents" => $row['yearly_need_multiplicity'],
                "required100PercentsWithMultiplicity" => $row['yearly_need_met'],
                "required100PercentsInOneYear" => $row['yearly_need_2023'],
                "required100PercentsInTwoYears" => $row['yearly_need_2024'],
                "balance_total_centralized" => $row['balance_total_centralized'],
                "balance_total_6m" => $row['balance_total_6m'],
                "balance_local_6m" => $row['balance_local_6m'],
                "notification_count" => $row['notification_count'],
                "lov_id" => $row['lov_id'],

            ];


        }
        $data[$row['year']][] = $type;

        return json_encode($type);

    }
