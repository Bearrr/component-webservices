<?php 
    public function CalcGepatit()
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

        $query = 'select a.id,round(max(ifnull(a.yearly_need_no_met,0)),0) yearly_need_mult,
ifnull(round(sum(l.active*ifnull(l.yearly_sum,0)*ifnull(p.cnt,1)),0),0) yearly_s
from med_application a
join med_type t on t.type_code=a.type_code and t.met=1

left join med_met_patients p on a.userid=p.userid and (a.type_code=p.type_code or (a.type_code = \'gepatit_2024\' and p.type_code like  \'gepatit%2024\'))
left join med_met_pill l on a.pillid=l.name and  l.patient_id=p.id
where a.userid="'.$userid.'" AND (a.type_code LIKE \'%2024\'  or (a.type_code = \'gepatit_2024\' and p.type_code like  \'gepatit%2024\'))
group by a.id
having yearly_need_mult <> yearly_s';

        $db->setQuery($query);
        $result = $db->loadAssocList();
        foreach ($result as $pr => $pill)
        {
            $cnt = $pill['yearly_s']*1;
            $q = "update med_application set yearly_need_no_met=".$cnt." where id=".$pill['id'];
            $db->setQuery($q);
            $db->execute();
        }

        return json_encode(['success' => true, 'test'=>$q]);
    }
