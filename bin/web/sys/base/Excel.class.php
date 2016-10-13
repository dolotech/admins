<?php
/**
 * Spreadsheet_Excel_Writer 类
 * 用于导出数据为Excel文件
 * @author 彭 qq:249828165
 */

require_once dirname(__FILE__).'/Spreadsheet/Excel/Writer.php';

class Excel extends Spreadsheet_Excel_Writer
{
	public function __construct()
	{
		parent::__construct();
		/**
		// Creating a workbook
		$workbook = new Spreadsheet_Excel_Writer();

		// sending HTTP headers
		$workbook->send('test.xls');

		// Creating a worksheet
		$worksheet =& $workbook->addWorksheet('My first worksheet');

		// The actual data
		$worksheet->write(0, 0, 'Name');
		$worksheet->write(0, 1, 'Age');
		$worksheet->write(1, 0, 'John Smith');
		$worksheet->write(1, 1, 30);
		$worksheet->write(2, 0, 'Johann Schmidt');
		$worksheet->write(2, 1, 31);
		$worksheet->write(3, 0, 'Juan Herrera');
		$worksheet->write(3, 1, 32);

		// Let's send the file
		$workbook->close();
		*/
	}
}

