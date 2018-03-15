<?php
/**
 * PHPExcel
 *
 * Copyright (C) 2006 - 2014 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    ##VERSION##, ##DATE##
 */

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');

if (PHP_SAPI == 'cli') die('This example should only be run from a Web Browser');

require_once 'PHPExcel/Classes/PHPExcel.php';
require_once 'config.php';
require_once 'common.php';


// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()
    ->setCreator("EGO")
    ->setLastModifiedBy("EGO")
    ->setTitle("EGO")
    ->setSubject("EGO")
    ->setDescription("EGO")
    ->setKeywords("EGO")
    ->setCategory("EGO");

$con = startDBcon();

$sql_base = "SELECT ";
$sql_base .= "  yp.id AS 'JUGADOR_ID', ";
$sql_base .= "  CONCAT('http://stage.hattrick.org/goto.ashx?path=/Club/Players/YouthPlayer.aspx?YouthPlayerID=',yp.id) AS 'JUGADOR_LINK', ";
$sql_base .= "  CONCAT(yp.first_name,' ',yp.last_name) AS 'NOMBRE', ";
$sql_base .= "  yp.age AS 'AÑOS', ";
$sql_base .= "  yp.days AS 'DIAS', ";
$sql_base .= "  yp.specialty AS 'ESPECIALIDAD', ";
$sql_base .= "  ym.id AS 'PARTIDO_ID', ";
$sql_base .= "  CONCAT('http://stage.hattrick.org/goto.ashx?path=/Club/Matches/Match.aspx?SourceSystem=Youth&MatchID=',ym.id) AS 'PARTIDO_LINK', ";
$sql_base .= "  yml.stars AS 'ESTRELLAS', ";
$sql_base .= "  st.id AS 'EQUIPO_ID', ";
$sql_base .= "  CONCAT('http://stage.hattrick.org/goto.ashx?path=/Club/?TeamID=',st.id) AS 'EQUIPO_LINK' ";
$sql_base .= "FROM youthmatchlineup yml ";
$sql_base .= "JOIN youthplayer yp ON yp.id=yml.youthPlayer_id ";
$sql_base .= "JOIN youthmatch ym ON ym.id=yml.youthMatch_id ";
$sql_base .= "JOIN youthteam yt ON yt.id=yp.youthTeam_id ";
$sql_base .= "JOIN seniorteam st ON st.id=yt.seniorTeam_id ";
$sql_base .= "WHERE yml.position in (%s) AND '2017-09-07'<ym.date AND ym.date<'2017-09-18' ORDER BY yml.stars DESC;";

sheet($objPHPExcel, $con, 0, 'PORTEROS', sprintf($sql_base, "100"));
sheet($objPHPExcel, $con, 1, 'LATERALES', sprintf($sql_base, "101,105"));
sheet($objPHPExcel, $con, 2, 'CENTRALES', sprintf($sql_base, "102,103,104"));
sheet($objPHPExcel, $con, 3, 'EXTREMOS', sprintf($sql_base, "106,110"));
sheet($objPHPExcel, $con, 4, 'INNERS', sprintf($sql_base, "107,108,109"));
sheet($objPHPExcel, $con, 5, 'DELANTEROS', sprintf($sql_base, "111,112,113"));
endDBcon($con);

// Auto size columns for each worksheet
foreach ($objPHPExcel->getAllSheets() as $sheet) {
    for ($col = 0; $col <= PHPExcel_Cell::columnIndexFromString($sheet->getHighestDataColumn()); $col++) {
        $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
    }
}

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="EGO.xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;

function sheet(PHPExcel $objPHPExcel, $con, $pageIndex, $pageName, $sql)
{
    if ($pageIndex > 0) $objPHPExcel->createSheet($pageIndex);
    $objPHPExcel->setActiveSheetIndex($pageIndex)
        ->setCellValue('A1', 'JUGADOR')
        ->setCellValue('B1', 'NOMBRE')
        ->setCellValue('C1', 'AÑOS')
        ->setCellValue('D1', 'DIAS')
        ->setCellValue('E1', 'ESPECIALIDAD')
        ->setCellValue('F1', 'PARTIDO')
        ->setCellValue('G1', 'ESTRELLAS')
        ->setCellValue('H1', 'EQUIPO')
        ->getStyle('A1:H1')->getFont()->setBold(true);
    $i = 1;
    $results = query($con, $sql);
    while ($row = $results->fetch_assoc()) {
        $i++;
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('A' . $i, $row['JUGADOR_ID'], PHPExcel_Cell_DataType::TYPE_STRING2, TRUE)->getHyperlink()->setUrl($row['JUGADOR_LINK']);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $i, $row['NOMBRE'], PHPExcel_Cell_DataType::TYPE_STRING2, TRUE);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['AÑOS']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $row['DIAS']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $row['ESPECIALIDAD']);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('F' . $i, $row['PARTIDO_ID'], PHPExcel_Cell_DataType::TYPE_STRING2, TRUE)->getHyperlink()->setUrl($row['PARTIDO_LINK']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, $row['ESTRELLAS']);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit('H' . $i, $row['EQUIPO_ID'], PHPExcel_Cell_DataType::TYPE_STRING2, TRUE)->getHyperlink()->setUrl($row['EQUIPO_LINK']);
    }
    $objPHPExcel->getActiveSheet()->setTitle($pageName);
}