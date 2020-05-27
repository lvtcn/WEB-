<?php
// 数据导出
require "{$document_root}/inc/PHPExcel/PHPExcel.php";
$chkid = strpost("chkid");
if (empty($chkid)) {
	echo "请选择要导出的数据项";
	return;	
}
$objPHPExcel = new PHPExcel();
$objPHPExcel->getProperties()->setCreator("Miscuz!")
							 ->setLastModifiedBy("Miscuz!")
							 ->setTitle("Office 2007 XLSX Document")
							 ->setSubject("Office 2007 XLSX Document")
							 ->setDescription("Document for Office 2007 XLSX")
							 ->setKeywords("Document for Office 2007 XLSX")
							 ->setCategory("");
$rowindex = 1;
$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue("A{$rowindex}", '代理商')
		->setCellValue("B{$rowindex}", '用户编号')
		->setCellValue("C{$rowindex}", '学员姓名')
		->setCellValue("D{$rowindex}", '联系电话')
		->setCellValue("E{$rowindex}", '身份证号')
		->setCellValue("F{$rowindex}", '电子邮箱')
		->setCellValue("G{$rowindex}", '购买商品')
		->setCellValue("H{$rowindex}", '开通时间');

$data = $db->getall("select * from #@__us where id in ({$chkid})");
foreach ($data as $val) {
	$rowindex += 1;
	$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue("A{$rowindex}", nostr($db->v("#@__proxy|proxy_name|id='{$val['proxy_id']}'")))
			->setCellValue("B{$rowindex}", nostr($val['num']))
			->setCellValue("C{$rowindex}", nostr($val['realname']))
			->setCellValue("D{$rowindex}", nostr($val['uname']))
			->setCellValue("E{$rowindex}", nostr($val['idcard']))
			->setCellValue("F{$rowindex}", nostr($val['email']))
			->setCellValue("G{$rowindex}", nostr(getseletedproducttxt($val['id'])))
			->setCellValue("H{$rowindex}", date("Y-m-d H:i:s", $val['createtime']));
}

$objPHPExcel->getActiveSheet()->setTitle('汇总表');	
$objPHPExcel->setActiveSheetIndex(0);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . time() . '.xlsx"');
header('Cache-Control: max-age=0');
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); 
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header ('Cache-Control: cache, must-revalidate');
header ('Pragma: public');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');	
exit();


// 数据导入
$filepath = strpost("filepath");
$filepath = explode(".", $filepath);
if (!isset($filepath[1])) {
    jsonecho("error", "请上传导入文件");
}
if ($filepath[1] != "xls") {
    jsonecho("error", "数据格式错误");
}
require_once "{$_SERVER['DOCUMENT_ROOT']}/inc/PHPExcel/PHPExcel.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/inc/PHPExcel/PHPExcel/IOFactory.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/inc/PHPExcel/PHPExcel/Reader/Excel5.php";

$filepaths = $_SERVER['DOCUMENT_ROOT'] . $filepath[0] . "." . $filepath[1];
$objReader = PHPExcel_IOFactory::createReader('Excel5');
$objPHPExcel = $objReader->load($filepaths); //$filename可以是上传的表格，或者是指定的表格
$sheet = $objPHPExcel->getSheet(0);
$highestRow = $sheet->getHighestRow(); // 取得总行数

//循环读取excel表格,读取一条,插入一条
//j表示从哪一行开始读取  从第二行开始读取，因为第一行是标题不保存
//$a表示列号
$i = 0;
for ($j = 3; $j <= 200; $j++) {
    try {
        $formdata = [];
        $formdata['name'] = $objPHPExcel->getActiveSheet()->getCell("A" . $j)->getValue();
        $formdata['sex'] = $objPHPExcel->getActiveSheet()->getCell("B" . $j)->getValue();
        $formdata['licennumber'] = $objPHPExcel->getActiveSheet()->getCell("C" . $j)->getValue();
        $formdata['xueli'] = $objPHPExcel->getActiveSheet()->getCell("D" . $j)->getValue();
        $formdata['zxjn'] = $objPHPExcel->getActiveSheet()->getCell("E" . $j)->getValue();
        $formdata['jndj'] = $objPHPExcel->getActiveSheet()->getCell("F" . $j)->getValue();
        $formdata['zsnumber'] = $objPHPExcel->getActiveSheet()->getCell("G" . $j)->getValue();
        $fzcreatetime = $objPHPExcel->getActiveSheet()->getCell("H" . $j)->getValue();
        $formdata['fzcreatetime'] = PHPExcel_Shared_Date::ExcelToPHP($fzcreatetime);
        $formdata['fzjg'] = $objPHPExcel->getActiveSheet()->getCell("I" . $j)->getValue();
        $formdata['sjfzdw'] = $objPHPExcel->getActiveSheet()->getCell("J" . $j)->getValue();

        //数据限制
        if (is_null($formdata['licennumber'])) {
            jsonecho("ok", "恭喜您，成功导入更新（{$i}）条有效信息！", "", ma($m, "index"));
        }

        $formdata['sex'] = $formdata['sex'] == "男" ? 1 : 2;

        $i += 1;
        $id = $db->v("{$table}|id|licennumber='{$formdata['licennumber']}'");
        $id = $id ? $id : 0;
        // 数据合并处理
        $formget = getformdata($tbl, $id);
        foreach ($formdata as $key => $val) {
            $formget[$key] = $val;
        }
        saveformdata($formget, $tbl);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}
