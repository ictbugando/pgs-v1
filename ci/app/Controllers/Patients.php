<?php

namespace App\Controllers;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\CMS\User\UserHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Patients extends BaseController
{
	public function index(){
		$viewData	= array();
		$start		= 0;

		//var_dump( $this->sesionRolesArray );

		$viewData["patArray"]		= get_instance()->Engine->fetchPatientList($start =0);
		$viewData["dispPatList"]	= view('Patients/dispPatList' , $viewData);

		$user_id	= $this->userInfo->id;
		$outData	= json_encode($viewData["patArray"]);
		
		$dataLogArr	= array("user_id" => $user_id, "item_id" => "0", "input_data" => "",
							"output_data" => $outData , "au_type" => "6" );
							
		$this->Engine->saveAuditActivityLogs($dataLogArr);

		return view('Headers/header')
            . view('Headers/page_header')
			. view('Patients/patients' , $viewData)
			. view('Headers/page_footer')
            . view('Headers/footer');
	}

	public function record($pat_id = null){
		$viewData	= array();
        $pat_id     = (int)$pat_id;

		$viewData["patRecArray"]  = get_instance()->Engine->fetchPatientRecord($pat_id);
		
		$user_id	= $this->userInfo->id;
		$outData	= json_encode($viewData["patRecArray"]  );
		
		$dataLogArr	= array("user_id" => $user_id, "item_id" => $pat_id, "input_data" => "",
							"output_data" => $outData , "au_type" => "10" );
							
		$this->Engine->saveAuditActivityLogs($dataLogArr);

		return view('Headers/header')
            . view('Headers/page_header')
			. view('Patients/record' , $viewData)
			. view('billing/billPreviewPop')
			. view('transPreviewPop')
			. view('Headers/page_footer')
            . view('Headers/footer');		
	}

	public function exportpatstatement($pat_id = 0){
		require FCPATH . '../ci/app/vendor/autoload.php';

		$savePath = FCPATH."downloads/";

		$fileName = strtotime( date("Y-m-d H:i:s") )."bmcreportfile.xlsx";
		$fullName = $savePath.$fileName;

		$spreadsheet = new Spreadsheet();
		
		$sheet = $spreadsheet->getActiveSheet();

		$rows	= 1;

		// --- Global Title ---
		$sheet->setCellValue('A' . $rows, 'BUGANDO PAYMENT GATEWAY SYSTEM - PATIENT STATEMENT');
		$sheet->mergeCells("A{$rows}:G{$rows}"); // span across columns
		$sheet->getStyle('A' . $rows)->applyFromArray([
			'font' => ['bold' => true,'size' => 17 ], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER ] ]);

		$rows+=2;

		// --- Global Title ---
		$sheet->setCellValue('B' . $rows, 'Patient Details');
		$sheet->mergeCells("B{$rows}:C{$rows}"); // span across columns
		$sheet->getStyle('B' . $rows)->applyFromArray([
			'font' => ['bold' => true,'size' => 17 ], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT ] ]);

		$rows++;

		$patRecArray	= get_instance()->Engine->fetchPatientRecord($pat_id);

		if( isset($patRecArray->pat_num) ){
			$patBalance		= 0;
			$walletBalArr   = get_instance()->Engine->getLastMasterEntry($patRecArray->pat_id , "99");

			if ( isset( $walletBalArr->close_bal ) ){
				$patBalance	= $walletBalArr->close_bal;
			}

			$sheet->setCellValue('B' . $rows, 'Date.');
			$sheet->getStyle('B' . $rows)->applyFromArray([	'font' => ['bold' => true] ]);

			$sheet->setCellValue('C' . $rows, date("Y-m-d H:i:s") );
			$rows++;

			$sheet->setCellValue('B' . $rows, 'File No.');
			$sheet->getStyle('B' . $rows)->applyFromArray([	'font' => ['bold' => true] ]);
			$sheet->setCellValue('C' . $rows, $patRecArray->pat_num );
			$sheet->getStyle('C' . $rows)->applyFromArray([	'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT ] ]);
			$rows++;

			$sheet->setCellValue('B' . $rows, 'Patient Name');
			$sheet->getStyle('B' . $rows)->applyFromArray([	'font' => ['bold' => true] ]);
			$sheet->setCellValue('C' . $rows, strtoupper($patRecArray->other_names." ".$patRecArray->sur_name) );
			$rows++;

			$sheet->setCellValue('B' . $rows, 'Wallet Balance');
			$sheet->getStyle('B' . $rows)->applyFromArray([	'font' => ['bold' => true] ]);
			$sheet->setCellValue('C' . $rows, $patBalance );
			$sheet->getStyle('C' . $rows)->getNumberFormat()
			->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
			$sheet->getStyle('C' . $rows)->applyFromArray([	'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT ] ]);

			$rows+=2;
		}
		
		$patPayArray	= get_instance()->Engine->getPatinetPaymentsCustLimit( $pat_id , $start =0 , $limit=0 );		
		$count			= 1;

		// --- Title ---
		$sheet->setCellValue('A' . $rows, 'PAYMENT HISTORY');
		$sheet->mergeCells("A{$rows}:E{$rows}"); // span across columns
		$sheet->getStyle('A' . $rows)->applyFromArray([
			'font' => ['bold' => true,'size' => 16 ], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER ] ]);

		$rows++;

		// --- Headers ---
		$headers	= ['#', 'RECEIPT', 'AMOUNT', 'BANK', 'DATE'];
		$col		= 'A';

		foreach ($headers as $header) {
			$sheet->setCellValue($col . $rows, $header);
			$sheet->getStyle($col . $rows)->applyFromArray([
				'font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER] ]);

			$col++;
		}

		$rows++;

		foreach($patPayArray AS $payArr){
			$sheet->setCellValue('A' . $rows, $count++ ."." );
			$sheet->setCellValue('B' . $rows, $payArr->receipt );
			$sheet->setCellValue('C' . $rows, $payArr->amount );
			$sheet->getStyle('C' . $rows)->getNumberFormat()
				  ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

			$sheet->setCellValue('D' . $rows, $payArr->wallet_alias );
			$sheet->setCellValue('E' . $rows, date_convert(date("Y-m-d H:i:s",$payArr->trans_date)) );

			$rows++;
		}

		$rows+=3;

		$patBillArray	= get_instance()->Engine->getPatienttBillsCustLimit( $pat_id , $start=0 , $limit=0 );
		$count			= 1;

		// --- Title ---
		$sheet->setCellValue('A' . $rows, 'BILLING HISTORY');
		$sheet->mergeCells("A{$rows}:E{$rows}"); // span across columns
		$sheet->getStyle('A' . $rows)->applyFromArray([
			'font' => ['bold' => true,'size' => 16 ], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER ] ]);

		$rows++;

		// --- Headers ---
		$headers	= ['#', 'CONTROL NUMBER', 'COST', 'PAID', 'DATE'];
		$col		= 'A';

		foreach ($headers as $header) {
			$sheet->setCellValue($col . $rows, $header);
			$sheet->getStyle($col . $rows)->applyFromArray([
				'font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER] ]);

			$col++;
		}

		$rows++;

		foreach($patBillArray AS $billArr){
			$sheet->setCellValue('A' . $rows, $count++ ."." );
			$sheet->setCellValue('B' . $rows, $billArr->bill_num );
			$sheet->setCellValue('C' . $rows, $billArr->bill_total_cost );
			$sheet->getStyle('C' . $rows)->getNumberFormat()
				  ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

			$sheet->setCellValue('D' . $rows, $billArr->bill_total_paid );
			$sheet->getStyle('D' . $rows)->getNumberFormat()
				  ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

			$sheet->setCellValue('E' . $rows, date_convert($billArr->bill_siku) );

			$rows++;
		}

		$rows+=3;

		$patWalletLog	= get_instance()->Engine->getWalletLogs($pat_id);
		$count			= 1;

		// --- Title ---
		$sheet->setCellValue('A' . $rows, 'CONTROL NUMBER STATEMENT');
		$sheet->mergeCells("A{$rows}:F{$rows}"); // span across columns
		$sheet->getStyle('A' . $rows)->applyFromArray([
			'font' => ['bold' => true,'size' => 16 ], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER ] ]);

		$rows++;

		// --- Headers ---
		$headers	= ['#', 'TYPE', 'REFERENCE', 'DEBIT', 'CREDIT' , 'BALANCE'];
		$col		= 'A';

		foreach ($headers as $header) {
			$sheet->setCellValue($col . $rows, $header);
			$sheet->getStyle($col . $rows)->applyFromArray([
				'font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER] ]);

			$col++;
		}

		$rows++;

		foreach($patWalletLog AS $patWalletArr){
			$sheet->setCellValue('A' . $rows, $count++ ."." );
			$sheet->setCellValue('B' . $rows, $patWalletArr->postName );
			$sheet->setCellValue('C' . $rows, $patWalletArr->myReference );
			$sheet->setCellValue('D' . $rows, $patWalletArr->bill_total_cost );
			$sheet->getStyle('D' . $rows)->getNumberFormat()
				  ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
			$sheet->setCellValue('E' . $rows, $patWalletArr->amount );
			$sheet->getStyle('C' . $rows)->getNumberFormat()
				  ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
			$sheet->setCellValue('F' . $rows, $patWalletArr->bal_amt );
			$sheet->getStyle('F' . $rows)->getNumberFormat()
				  ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

			$rows++;
		}

		$rows+=3;

		$globWalletMasterArr    = get_instance()->Engine->getGlobalMasterStatement($start = 0, $type = 0, $pat_id , $limit = 50 );
		$count					= 1;

		// --- Title ---
		$sheet->setCellValue('A' . $rows, 'WALLET STATEMENT');
		$sheet->mergeCells("A{$rows}:G{$rows}"); // span across columns
		$sheet->getStyle('A' . $rows)->applyFromArray([
			'font' => ['bold' => true,'size' => 16 ], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER ] ]);

		$rows++;

		// --- Headers ---
		$headers	= ['#', 'TYPE', 'REFERENCE', 'OPEN', 'AMOUNT' , 'CLOSE', 'DATE'];
		$col		= 'A';

		foreach ($headers as $header) {
			$sheet->setCellValue($col . $rows, $header);
			$sheet->getStyle($col . $rows)->applyFromArray([
				'font' => ['bold' => true], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER] ]);

			$col++;
		}

		$rows++;

		foreach($globWalletMasterArr AS $walletMasterArr){
			$sheet->setCellValue('A' . $rows, $count++ ."." );
			$sheet->setCellValue('B' . $rows, $walletMasterArr->myType );
			$sheet->setCellValue('C' . $rows, $walletMasterArr->myReference );
			$sheet->setCellValue('D' . $rows, $walletMasterArr->mstart_bal );
			$sheet->getStyle('D' . $rows)->getNumberFormat()
				  ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);			
			$sheet->setCellValue('E' . $rows, $walletMasterArr->amount );
			$sheet->getStyle('E' . $rows)->getNumberFormat()
				  ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);			
			$sheet->setCellValue('F' . $rows, $walletMasterArr->mclose_bal );
			$sheet->getStyle('F' . $rows)->getNumberFormat()
				  ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);			
			$sheet->setCellValue('G' . $rows, date_convert($walletMasterArr->siku) );

			$rows++;
		}

		// Auto-size columns
		foreach (range('A', 'E') as $col) {
			$sheet->getColumnDimension($col)->setAutoSize(true);
		}
		
		$writer = new Xlsx($spreadsheet);
		$writer->save($fullName);

		$path 		= base_url('/downloads/'.$fileName);

		return redirect()->to(base_url('/downloads/'.$fileName));

		//$responseArray["path"]		= $path;
		//$responseArray["print"]		= $tablPrint;
		//die(json_encode($responseArray));

	}	

	public function fetchpatform(){
		$viewData		= array();
		$viewData["mkoaArray"]  = fetchNewPatientForm($mkoa_id = 0 , $wilaya_id = 0)["mkoa"];
		$viewData["idTypeArr"]  = get_instance()->Engine->fetchIdTypes();

		return view('Patients/newPatientForm' , $viewData);
	}

	public function wallet($pat_id = null){
		require FCPATH . '../ci/app/vendor/autoload.php';

		$pat_id 		= (int)$pat_id;
		$trans_date		= strtotime(date('Y-m-d H:i:s'));
		$patRecArray	= get_instance()->Engine->fetchPatientRecord($pat_id);

		if( isset($patRecArray->pat_id) ){
			$patWalletLog	= get_instance()->Engine->getWalletLogs($pat_id);

			$savePath 		= FCPATH."downloads/";
			$fileName 		= $trans_date."bmcwalletstatement.xlsx";
			$fullName 		= $savePath.$fileName;

			$spreadsheet 	= new Spreadsheet();
			
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->setCellValue('A1', '#');
			$sheet->setCellValue('B1', 'TYPE');
			$sheet->setCellValue('C1', 'REFERENCE');
			$sheet->setCellValue('D1', 'DEBIT');
			$sheet->setCellValue('E1', 'CREDIT');
			$sheet->setCellValue('F1', 'BALANCE');

			$sheet->getStyle('A1:F1')->getFont()->setBold(true);
			
			$rows	= 2;
			$count	= 1;
			
			$totalCredit    = 0;
			$totalDebit     = 0;
			$countW         = 1;

			foreach($patWalletLog as $walletArr){

				$totalCredit    = $totalCredit+$walletArr->bill_total_cost;
				$totalDebit     = $totalDebit+$walletArr->amount;

				$sheet->setCellValue('A' . $rows, $countW++);
				$sheet->setCellValue('B' . $rows, $walletArr->postName);

				if($walletArr->post_type == 1){
					$sheet->setCellValue('C' . $rows, $walletArr->receipt);
				}
				else{
					$sheet->setCellValue('C' . $rows, $walletArr->bill_num);
				}
				
				$sheet->setCellValue('D' . $rows, number_format($walletArr->bill_total_cost));
				$sheet->setCellValue('E' . $rows, number_format($walletArr->amount));
				$sheet->setCellValue('F' . $rows, number_format($walletArr->bal_amt) );
				$rows++;
			}

			$sheet->setCellValue('B' . ($rows+1), "Statement Summary");

			$sheet->setCellValue('B' . ($rows+2), "Date.");
			$sheet->setCellValue('C' . ($rows+2), date("Y-m-d H:i:s") );

			$sheet->setCellValue('B' . ($rows+3), "File No.");
			$sheet->setCellValue('C' . ($rows+3), $patRecArray->pat_num);

			$sheet->setCellValue('B' . ($rows+4), "Patient Name.");
			$sheet->setCellValue('C' . ($rows+4), strtoupper($patRecArray->other_names." ".$patRecArray->sur_name) );

			$sheet->setCellValue('B' . ($rows+5), "Total Credit.");
			$sheet->setCellValue('C' . ($rows+5), number_format($totalCredit) );

			$sheet->setCellValue('B' . ($rows+6), "Total Debit.");
			$sheet->setCellValue('C' . ($rows+6), number_format($totalDebit) );

			$sheet->setCellValue('B' . ($rows+7), "Wallet Balance.");
			$sheet->setCellValue('C' . ($rows+7), number_format($patRecArray->wallet_bal) );

			$sheet->setCellValue('B' . ($rows+8), "Currency.");
			$sheet->setCellValue('C' . ($rows+8), "TZS");

			$sheet->getStyle('B'.($rows+1).':B'.($rows+8))->getFont()->setBold(true);
			
			$writer = new Xlsx($spreadsheet);
			$writer->save($fullName);

			$path = base_url('/downloads/'.$fileName);
			
			$responseArray["status"]	= "11";
			$responseArray["path"]		= $path;
			die(json_encode($responseArray));
		}

		$responseArray["status"]	= "00";
		$responseArray["path"]		= "";
		die(json_encode($responseArray));
		
	}

	public function search($start = 0 , $word = ""){
        $word		= $this->request->getPost('word');
		$viewData	= array();

		$viewData["patArray"]	= get_instance()->Engine->fetchPatientSearch($word , $start);

		$user_id	= $this->userInfo->id;
		$outData	= json_encode($viewData["patArray"]);
		
		$dataLogArr	= array("user_id" => $user_id, "item_id" => "0", "input_data" => "",
							"output_data" => $outData , "au_type" => "8" );
							
		$this->Engine->saveAuditActivityLogs($dataLogArr);

		return view('Patients/dispPatList' , $viewData);
	}

	public function fetchLocations($mkoa_id = 0, $wilaya_id = 0){
		$mkoa_id 	= (int)$mkoa_id;
		$wilaya_id	= (int)$wilaya_id;

		$responseArray = fetchNewPatientForm($mkoa_id, $wilaya_id);

		die(json_encode($responseArray));
	}
    
    public function processnewpat(){
        $siku		= date("Y-m-d H:i:s");
        $sname		= $this->request->getPost('sname');
        $otnames	= $this->request->getPost('names');
        $patNum	    = $this->request->getPost('patNum');
        $phone	    = $this->request->getPost('phone');
        $patDob	    = $this->request->getPost('patDob');
        $kinName	= $this->request->getPost('kinName');
        $kinPhone	= $this->request->getPost('kinPhone');
        $idType	    = (int)$this->request->getPost('idType');
        $idNum	    = $this->request->getPost('idNum');
        $kata	    = $this->request->getPost('kataselect');

        $patDob = "1990-11-23";

		if ( !$this->Engine->validateName( $sname ) || !$this->Engine->validateName( $otnames )){
			$responseArray["status"]	= "01";
			$responseArray["msg"]		= "Name entered is invalid";
			die(json_encode($responseArray));
		}

		if( !$this->Engine->validatePhone($phone) ){
			$responseArray["status"]	= "02";
			$responseArray["msg"]		= "Invalid Patient phone number entered";
			die(json_encode($responseArray));			
		}

        if(strlen($patNum) < 4){
			$responseArray["status"]	= "03";
			$responseArray["msg"]		= "Please enter a Valid Patient Number";
			die(json_encode($responseArray));	            
        }

        if( $this->Engine->isExistingPatientNumber($patNum) ){
			$responseArray["status"]	= "03b";
			$responseArray["msg"]		= "The Patient Number entered is already registered";
			die(json_encode($responseArray));	
        }

		/*
        if( !$this->Engine->validateDOB($patDob) ){
			$responseArray["status"]	= "04";
			$responseArray["msg"]		= "Please select a valid Birth Date";
			//die(json_encode($responseArray));	
        }
		
		if ( !$this->Engine->validateName( $kinName ) ){
			$responseArray["status"]	= "05";
			$responseArray["msg"]		= "Kin Name Entered Is Invalid";
			//die(json_encode($responseArray));
		}

		if( !$this->Engine->validatePhone($kinPhone) ){
			$responseArray["status"]	= "06";
			$responseArray["msg"]		= "Invalid Next of Kin phone number entered";
			//die(json_encode($responseArray));			
		}

        if( $idType > 0){
            if( !$this->Engine->isExistingIdType($idType) ){
                $responseArray["status"]	= "07";
                $responseArray["msg"]		= "Please select Patient's ID Type"; 
                //die(json_encode($responseArray));	
            }

            if( !$this->Engine->validateIDNum($idNum) ){
                $responseArray["status"]	= "08";
                $responseArray["msg"]		= "Please enter a Valid Patient ID";
                //die(json_encode($responseArray));	
            }           
        }

        if( !$this->Engine->isExistingKata($kata) ){
			$responseArray["status"]	= "09";
			$responseArray["msg"]		= "Please select a valid Adress Location of the Patient";
			//die(json_encode($responseArray));
        }
		*/

		$dataPatient	= array("pat_num" => $patNum, "sur_name" => $sname, "other_names" => $otnames, 
								"phone" => $phone,"last_updated" => $siku, "reg_date" => $siku);
		
		/*
        $dataPatient	= array("pat_num" => $patNum, "sur_name" => $sname, "other_names" => $otnames, "dob" => $patDob, "phone" => $phone,
                                "next_of_kin_name" => $kinName, "next_of_kin_phone" => $kinPhone, "id_type" => $idType, "id_number" => $idNum, 
                                "loc_kata" => $kata, "last_updated" => $siku, "reg_date" => $siku);
								*/
                                
        $pat_id	= $this->Engine->savePatientDetails($dataPatient);

        if ( $pat_id == 0) {
            $responseArray["status"]	= "09";
            $responseArray["msg"]		= "An error occured when saving record";
            die(json_encode($responseArray));
        }
        else{
			$user_id	= $this->userInfo->id;
			$inpData	= json_encode($dataPatient);
			
			$dataLogArr	= array("user_id" => $user_id, "item_id" => $pat_id, "input_data" => $inpData,
								"output_data" => "" , "au_type" => "9" );
								
			$this->Engine->saveAuditActivityLogs($dataLogArr);

            $responseArray["status"]	= "111";
			$responseArray["redLink"]	= base_url('patients/record/'.$pat_id);
            $responseArray["msg"]		= "Successfully registered new user";
            die(json_encode($responseArray));
        }                                

    }    

}