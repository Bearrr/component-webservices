<?php



require('/usr/sites/meddata.com.ua/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class GetApplicationStatusExport extends aJWSApiMethod
{
    protected function dispatch()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        ini_set('memory_limit', '10000M');
        set_time_limit(-1);
        $user = JFactory::getUser();

        $dev = $this->app->input->get('dev', 0);
        $institution = $this->app->input->get('institution', 0);

        $type_code = $this->app->input->get('type_code');
//        $region = $this->app->input->get('region');

        if ($dev || isset($user->groups[268]) || isset($user->groups[328]) || isset($user->groups[274])) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Рівень "Розрахунок - країна"');

            $query = "SELECT 
	n.num '№', 
	t.rep_name 'Напрям', 
	IFNULL(n.subtype,'') 'Піднапрям', 
	n.name 'Міжнародна непатентована назва лікарського засобу / Назва медичного виробу', 
	n.release_form 'Форма випуску', 
	n.dosage 'Дозування', 
	n.unit 'Одиниця виміру', 
	n.app_multiplicity 'Кратність', 
	n.price 'Орієнтовна ціна за одиницю, грн', 
	SUM(a.yearly_need) 'Річний обсяг 100% потреби', 
	SUM(IFNULL(a.balance_total,0)) 'Залишки всього', 
	SUM(IFNULL(a.balance_local,0)) 'Залишки в тому числі за кошти місцевого бюджету та інших джерел фінансування', 
	SUM(IFNULL(a.balance_lowterm,0)) 'Залишки, з них кількість одиниць, термін придатності яких до 6 місяців', 
	SUM(a.expected_deliveries) 'Очікувані поставки за кошти державного бюджету', 
	SUM(IFNULL(a.expected_deliveries_local,0)) 'Очікувані поставки за кошти місцевого бюджету та інших джерел фінансування', 
	SUM(a.calc_yearly_need_added) 'Річний обсяг 100% потреби за виключенням наявних залишків та очікуваних поставок', 
	SUM(IFNULL(a.expected_from_rejections,0)) AS 'Відмова (з величини очікуваних поставок за кошти держ.бюджету)'
FROM med_name n
JOIN med_type t ON n.type_code = t.type_code
join med_application a ON n.id = a.pillid
WHERE n.type_code IN ('" . $type_code . "') AND a.region <> 'Тестовое подразделение'
GROUP BY n.id;";

            $this->db->setQuery($query);
            $data = $this->db->loadAssocList();

            $sheet
                ->setCellValue('A1', '№')
                ->setCellValue('B1', 'Напрям')
                ->setCellValue('C1', 'Піднапрям')
                ->setCellValue('D1', 'Міжнародна непатентована назва лікарського засобу / Назва медичного виробу')
                ->setCellValue('E1', 'Форма випуску')
                ->setCellValue('F1', 'Дозування')
                ->setCellValue('G1', 'Одиниця виміру')
                ->setCellValue('H1', 'Кратність')
                ->setCellValue('I1', 'Орієнтовна ціна за одиницю, грн')
                ->setCellValue('J1', 'Річний обсяг 100% потреби')
                ->setCellValue('K1', 'Залишки всього')
                ->setCellValue('L1', 'Залишки в тому числі за кошти місцевого бюджету та інших джерел фінансування')
                ->setCellValue('M1', 'Залишки, з них кількість одиниць, термін придатності яких до 6 місяців')
                ->setCellValue('N1', 'Очікувані поставки за кошти державного бюджету')
                ->setCellValue('O1', 'Очікувані поставки за кошти місцевого бюджету та інших джерел фінансування')
                ->setCellValue('P1', 'Річний обсяг 100% потреби за виключенням наявних залишків та очікуваних поставок')
                ->setCellValue('Q1', 'Відмова (з величини очікуваних поставок за кошти держ.бюджету)');

            foreach ($data as $i => $row) {
                $sheet
                    ->setCellValue('A' . ($i + 2), $row['№'])
                    ->setCellValue('B' . ($i + 2), $row['Напрям'])
                    ->setCellValue('C' . ($i + 2), $row['Піднапрям'])
                    ->setCellValue('D' . ($i + 2), $row['Міжнародна непатентована назва лікарського засобу / Назва медичного виробу'])
                    ->setCellValue('E' . ($i + 2), $row['Форма випуску'])
                    ->setCellValue('F' . ($i + 2), $row['Дозування'])
                    ->setCellValue('G' . ($i + 2), $row['Одиниця виміру'])
                    ->setCellValue('H' . ($i + 2), $row['Кратність'])
                    ->setCellValue('I' . ($i + 2), $row['Орієнтовна ціна за одиницю, грн'])
                    ->setCellValue('J' . ($i + 2), $row['Річний обсяг 100% потреби'])
                    ->setCellValue('K' . ($i + 2), $row['Залишки всього'])
                    ->setCellValue('L' . ($i + 2), $row['Залишки в тому числі за кошти місцевого бюджету та інших джерел фінансування'])
                    ->setCellValue('M' . ($i + 2), $row['Залишки, з них кількість одиниць, термін придатності яких до 6 місяців'])
                    ->setCellValue('N' . ($i + 2), $row['Очікувані поставки за кошти державного бюджету'])
                    ->setCellValue('O' . ($i + 2), $row['Очікувані поставки за кошти місцевого бюджету та інших джерел фінансування'])
                    ->setCellValue('P' . ($i + 2), $row['Річний обсяг 100% потреби за виключенням наявних залишків та очікуваних поставок'])
                    ->setCellValue('Q' . ($i + 2), $row['Відмова (з величини очікуваних поставок за кошти держ.бюджету)']);
            }


            $sheet = $spreadsheet->createSheet();
            $spreadsheet->setActiveSheetIndex(1);
            $sheet->setTitle('Рівень "Розрахунок - регіон"');

            $query = "SELECT 
	n.num '№', 
	t.rep_name 'Напрям', 
	IFNULL(n.subtype,'') 'Піднапрям', 
	n.name 'Міжнародна непатентована назва лікарського засобу / Назва медичного виробу', 
	n.release_form 'Форма випуску', 
	n.dosage 'Дозування', 
	n.unit 'Одиниця виміру', 
	n.app_multiplicity 'Кратність', 
	n.price 'Орієнтовна ціна за одиницю, грн', 
	SUM(a.yearly_need) 'Річний обсяг 100% потреби', 
	SUM(IFNULL(a.balance_total,0)) 'Залишки всього', 
	SUM(IFNULL(a.balance_local,0)) 'Залишки в тому числі за кошти місцевого бюджету та інших джерел фінансування', 
	SUM(IFNULL(a.balance_lowterm,0)) 'Залишки, з них кількість одиниць, термін придатності яких до 6 місяців', 
	SUM(a.expected_deliveries) 'Очікувані поставки за кошти державного бюджету', 
	SUM(IFNULL(a.expected_deliveries_local,0)) 'Очікувані поставки за кошти місцевого бюджету та інших джерел фінансування', 
	SUM(a.calc_yearly_need_added) 'Річний обсяг 100% потреби за виключенням наявних залишків та очікуваних поставок', 
	SUM(IFNULL(a.expected_from_rejections,0)) AS 'Відмова (з величини очікуваних поставок за кошти держ.бюджету)'
FROM med_name n
JOIN med_type t ON n.type_code = t.type_code
join med_application a ON n.id = a.pillid
WHERE n.type_code IN ('" . $type_code . "') AND a.region <> 'Тестовое подразделение'
GROUP BY n.lov_id,a.region;";

            $this->db->setQuery($query);
            $data = $this->db->loadAssocList();

            $sheet
                ->setCellValue('A1', '№')
                ->setCellValue('B1', 'Напрям')
                ->setCellValue('C1', 'Піднапрям')
                ->setCellValue('D1', 'Міжнародна непатентована назва лікарського засобу / Назва медичного виробу')
                ->setCellValue('E1', 'Форма випуску')
                ->setCellValue('F1', 'Дозування')
                ->setCellValue('G1', 'Одиниця виміру')
                ->setCellValue('H1', 'Кратність')
                ->setCellValue('I1', 'Орієнтовна ціна за одиницю, грн')
                ->setCellValue('J1', 'Річний обсяг 100% потреби')
                ->setCellValue('K1', 'Залишки всього')
                ->setCellValue('L1', 'Залишки в тому числі за кошти місцевого бюджету та інших джерел фінансування')
                ->setCellValue('M1', 'Залишки, з них кількість одиниць, термін придатності яких до 6 місяців')
                ->setCellValue('N1', 'Очікувані поставки за кошти державного бюджету')
                ->setCellValue('O1', 'Очікувані поставки за кошти місцевого бюджету та інших джерел фінансування')
                ->setCellValue('P1', 'Річний обсяг 100% потреби за виключенням наявних залишків та очікуваних поставок')
                ->setCellValue('Q1', 'Відмова (з величини очікуваних поставок за кошти держ.бюджету)');

            foreach ($data as $i => $row) {
                $sheet
                    ->setCellValue('A' . ($i + 2), $row['№'])
                    ->setCellValue('B' . ($i + 2), $row['Напрям'])
                    ->setCellValue('C' . ($i + 2), $row['Піднапрям'])
                    ->setCellValue('D' . ($i + 2), $row['Міжнародна непатентована назва лікарського засобу / Назва медичного виробу'])
                    ->setCellValue('E' . ($i + 2), $row['Форма випуску'])
                    ->setCellValue('F' . ($i + 2), $row['Дозування'])
                    ->setCellValue('G' . ($i + 2), $row['Одиниця виміру'])
                    ->setCellValue('H' . ($i + 2), $row['Кратність'])
                    ->setCellValue('I' . ($i + 2), $row['Орієнтовна ціна за одиницю, грн'])
                    ->setCellValue('J' . ($i + 2), $row['Річний обсяг 100% потреби'])
                    ->setCellValue('K' . ($i + 2), $row['Залишки всього'])
                    ->setCellValue('L' . ($i + 2), $row['Залишки в тому числі за кошти місцевого бюджету та інших джерел фінансування'])
                    ->setCellValue('M' . ($i + 2), $row['Залишки, з них кількість одиниць, термін придатності яких до 6 місяців'])
                    ->setCellValue('N' . ($i + 2), $row['Очікувані поставки за кошти державного бюджету'])
                    ->setCellValue('O' . ($i + 2), $row['Очікувані поставки за кошти місцевого бюджету та інших джерел фінансування'])
                    ->setCellValue('P' . ($i + 2), $row['Річний обсяг 100% потреби за виключенням наявних залишків та очікуваних поставок'])
                    ->setCellValue('Q' . ($i + 2), $row['Відмова (з величини очікуваних поставок за кошти держ.бюджету)']);
            }

            if ($institution == 1) {
                $sheet = $spreadsheet->createSheet();
                $spreadsheet->setActiveSheetIndex(2);
                $sheet->setTitle('Рівень "Розрахунок - заклад"');

                $query = "SELECT 
	n.num '№', 
	t.rep_name 'Напрям', 
	IFNULL(n.subtype,'') 'Піднапрям', 
	n.name 'Міжнародна непатентована назва лікарського засобу / Назва медичного виробу', 
	n.release_form 'Форма випуску', 
	n.dosage 'Дозування', 
	n.unit 'Одиниця виміру', 
	n.app_multiplicity 'Кратність', 
	n.price 'Орієнтовна ціна за одиницю, грн', 
	SUM(a.yearly_need) 'Річний обсяг 100% потреби', 
	SUM(IFNULL(a.balance_total,0)) 'Залишки всього', 
	SUM(IFNULL(a.balance_local,0)) 'Залишки в тому числі за кошти місцевого бюджету та інших джерел фінансування', 
	SUM(IFNULL(a.balance_lowterm,0)) 'Залишки, з них кількість одиниць, термін придатності яких до 6 місяців', 
	SUM(a.expected_deliveries) 'Очікувані поставки за кошти державного бюджету', 
	SUM(IFNULL(a.expected_deliveries_local,0)) 'Очікувані поставки за кошти місцевого бюджету та інших джерел фінансування', 
	SUM(a.calc_yearly_need_added) 'Річний обсяг 100% потреби за виключенням наявних залишків та очікуваних поставок', 
	SUM(IFNULL(a.expected_from_rejections,0)) AS 'Відмова (з величини очікуваних поставок за кошти держ.бюджету)'
FROM med_name n
JOIN med_type t ON n.type_code = t.type_code
join med_application a ON n.id = a.pillid
WHERE n.type_code IN ('" . $type_code . "') AND a.region <> 'Тестовое подразделение'
GROUP BY n.lov_id,a.region,a.userid;";

                $this->db->setQuery($query);
                $data = $this->db->loadAssocList();

                $sheet
                    ->setCellValue('A1', '№')
                    ->setCellValue('B1', 'Напрям')
                    ->setCellValue('C1', 'Піднапрям')
                    ->setCellValue('D1', 'Міжнародна непатентована назва лікарського засобу / Назва медичного виробу')
                    ->setCellValue('E1', 'Форма випуску')
                    ->setCellValue('F1', 'Дозування')
                    ->setCellValue('G1', 'Одиниця виміру')
                    ->setCellValue('H1', 'Кратність')
                    ->setCellValue('I1', 'Орієнтовна ціна за одиницю, грн')
                    ->setCellValue('J1', 'Річний обсяг 100% потреби')
                    ->setCellValue('K1', 'Залишки всього')
                    ->setCellValue('L1', 'Залишки в тому числі за кошти місцевого бюджету та інших джерел фінансування')
                    ->setCellValue('M1', 'Залишки, з них кількість одиниць, термін придатності яких до 6 місяців')
                    ->setCellValue('N1', 'Очікувані поставки за кошти державного бюджету')
                    ->setCellValue('O1', 'Очікувані поставки за кошти місцевого бюджету та інших джерел фінансування')
                    ->setCellValue('P1', 'Річний обсяг 100% потреби за виключенням наявних залишків та очікуваних поставок')
                    ->setCellValue('Q1', 'Відмова (з величини очікуваних поставок за кошти держ.бюджету)');

                foreach ($data as $i => $row) {
                    $sheet
                        ->setCellValue('A' . ($i + 2), $row['№'])
                        ->setCellValue('B' . ($i + 2), $row['Напрям'])
                        ->setCellValue('C' . ($i + 2), $row['Піднапрям'])
                        ->setCellValue('D' . ($i + 2), $row['Міжнародна непатентована назва лікарського засобу / Назва медичного виробу'])
                        ->setCellValue('E' . ($i + 2), $row['Форма випуску'])
                        ->setCellValue('F' . ($i + 2), $row['Дозування'])
                        ->setCellValue('G' . ($i + 2), $row['Одиниця виміру'])
                        ->setCellValue('H' . ($i + 2), $row['Кратність'])
                        ->setCellValue('I' . ($i + 2), $row['Орієнтовна ціна за одиницю, грн'])
                        ->setCellValue('J' . ($i + 2), $row['Річний обсяг 100% потреби'])
                        ->setCellValue('K' . ($i + 2), $row['Залишки всього'])
                        ->setCellValue('L' . ($i + 2), $row['Залишки в тому числі за кошти місцевого бюджету та інших джерел фінансування'])
                        ->setCellValue('M' . ($i + 2), $row['Залишки, з них кількість одиниць, термін придатності яких до 6 місяців'])
                        ->setCellValue('N' . ($i + 2), $row['Очікувані поставки за кошти державного бюджету'])
                        ->setCellValue('O' . ($i + 2), $row['Очікувані поставки за кошти місцевого бюджету та інших джерел фінансування'])
                        ->setCellValue('P' . ($i + 2), $row['Річний обсяг 100% потреби за виключенням наявних залишків та очікуваних поставок'])
                        ->setCellValue('Q' . ($i + 2), $row['Відмова (з величини очікуваних поставок за кошти держ.бюджету)']);
                }
            }


            $writer = new Xlsx($spreadsheet);

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="Статус_Заявок.xlsx"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            die;
        }

        return $this->error('Для запиту інформації необхідно пройти авторизацію. Спробуйте оновити сторінку та авторизуватись.');
    }
}
