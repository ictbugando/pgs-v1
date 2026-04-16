<?php
namespace App\Models;

use CodeIgniter\Model;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Application\ApiApplication;

class Engine extends Model{
	
	public function docurl($url , $myXML){
		$arrayResult['error1'] = '';
		$arrayResult['error2'] = '';
		$arrayResult['data']   = '';

		$headers 	= array("Content-type: text/xml");		
		$ch 		= curl_init($url);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $myXML);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//curl_setopt($ch, CURLOPT_HEADER, $headers);
		//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers );

		$returndata = curl_exec($ch);

		if (curl_errno($ch)) {
			$arrayResult['error1'] = 'Couldn\'t send request: ' . curl_error($ch);
		} 
		else {
			$resultStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if ($resultStatus == 200) {
				$arrayResult['data'] = $returndata;
			} 
			else {
				$arrayResult['error2'] = 'Request failed: HTTP status code: ' . $resultStatus;
			}
		}
		curl_close($ch);
		
		return $arrayResult;
	}

	public function doCurljson($url , $myJsonData){
		$ch = curl_init();

		$arrayResult['error1'] = '';
		$arrayResult['error2'] = '';
		$arrayResult['data']   = '';
		
		$headers 	= array("Content-Type: application/json",'Content-Length: ' . strlen($myJsonData) );
		$ch 		= curl_init($url);
		
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $myJsonData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers );
		
		$returndata = curl_exec($ch);

		if (curl_errno($ch)) {
			$arrayResult['error1'] = 'Couldn\'t send request: ' . curl_error($ch);
		} 
		else {
			$resultStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if ($resultStatus == 200) {
				$arrayResult['data'] = $returndata;
			} 
			else {
				$arrayResult['error2'] = 'Request failed: HTTP status code: ' . $resultStatus;
			}
		}
		curl_close($ch);
		
		return $arrayResult;		
	}

	public function getGeneratedReference($getRefArray){
		$start		= (int)$getRefArray["start"];
		$mwezi		= $getRefArray["mwezi"];
		$mwaka		= $getRefArray["mwaka"];
		$rstatus	= $getRefArray["rstatus"];
		$extendQury	= "";
		$date		= date($mwaka.'-'.$mwezi.'-01');
		$mwezi_id	= getMweziId($date);
		$limit		= "30";
		
		$extendQury	= " WHERE mwezi_id = '".$mwezi_id."' ";
		
		if($rstatus != "99"){
			$extendQury	= $extendQury . " && tb1.st_code  = '".$rstatus."' ";
		}
		
		$resArray			= array();
		$resArray['load']	= "0";
		
		$sql		= "SELECT tb1.* , tb2.* FROM bmc_pay_tokens tb1 
					   INNER JOIN bmc_pay_tokens_status tb2 ON tb2.st_code = tb1.st_code
					   ".$extendQury." ORDER BY tb1.token_id DESC 
					   LIMIT $start , $limit ";

		$q1			= $this->db->query($sql);
		$ct			= $q1->getNumRows();
		
		if ($ct > 0){
			$queryRows	= $q1->getResult();
			
			foreach ($queryRows as $row){
				$resArray[] = $row;
			}
		}
		
		if($ct  >= $limit && $ct%$limit == 0){
			$resArray['load']	= "1";
		}		
		
		$resArray['ct'] = $ct;
		$resArray['start'] = $ct+$start;
		
		return $resArray;
	}
	
	public function srchReferenceData( $getRefArray ){
		$resArray	= array();
		$start		= $getRefArray["start"];
		$keyWord	= $getRefArray["keyWord"];
		$limit		= "30";
		
		$resArray['load']	= "0";
		
		$sql		= "SELECT tb1.* , tb2.* FROM bmc_pay_tokens tb1 
					   INNER JOIN bmc_pay_tokens_status tb2 ON tb2.st_code = tb1.st_code
					   WHERE (tb1.token_ext_reference LIKE '%$keyWord%')  || (tb1.token_ext_phone LIKE '%$keyWord%' ) || 
						  (tb1.token_unique_id LIKE '%$keyWord%') 
					   ORDER BY tb1.token_id DESC LIMIT  $start , $limit ";		
		
					
		$q1		= $this->db->query($sql);
		$ct		= $q1->getNumRows();
		
		if ($ct > 0){
			$queryRows	= $q1->getResult();
			
			foreach ($queryRows as $row){
				$resArray[] = $row;
			}
		}
		
		if($ct  >= $limit && $ct%$limit == 0){
			$resArray['load']	= "1";
		}		
		
		$resArray['ct'] = $ct;
		$resArray['start'] = $ct+$start;
		
		return $resArray;			
	}
	
	public function getPaymentData($getRefArray){
		$start		= (int)$getRefArray["start"];
		$wallet		= $getRefArray["wallet"];
		$pstatus	= $getRefArray["pstatus"];
		$mwezi		= $getRefArray["mwezi"];
		$mwaka		= $getRefArray["mwaka"];
		$extendQury	= "";
		$date		= date($mwaka.'-'.$mwezi.'-01');
		$mwezi_id	= getMweziId($date);
		$limit		= "50";
		
		$extendQury	= " WHERE mwezi_id = '".$mwezi_id."' ";

		if($wallet != "99"){
			$extendQury	= $extendQury . " && tb2.trans_sp = '".$wallet."' ";
		}
		
		if($pstatus != "99"){
			$extendQury	= $extendQury . " && tb1.trans_status = '".$pstatus."' ";
		}
		
		$resArray			= array();
		$resArray['load']	= "0";
		
		
		$sql	= " SELECT tb1.trans_id , tb1.msisdn , tb1.receipt , tb1.reference , tb1.trans_status , tb1.amount , 
						   tb2.trans_date , tb3.* , tb4.wallet_alias , tb4.wallet_name , tb5.*
					FROM bmc_malipo tb1
					INNER JOIN bmc_malipo_details tb2	ON tb2.trans_id = tb1.trans_id
					INNER JOIN bmc_patient_profile tb3 ON tb3.pat_id = tb1.pat_id
					INNER JOIN bmc_malipo_wallets tb4 ON tb4.wallet_id = tb2.trans_sp
					INNER JOIN bmc_malipo_status tb5 ON tb5.st_code = tb1.trans_status
					".$extendQury." ORDER BY tb1.trans_id DESC LIMIT $start , $limit ";
				
		$q1		= $this->db->query($sql);
		$ct		= $q1->getNumRows();
		
		if ($ct > 0){
			$queryRows	= $q1->getResult();
			
			foreach ($queryRows as $row){
				$resArray[] = $row;
			}
		}
		
		if($ct  >= $limit && $ct%$limit == 0){
			$resArray['load']	= "1";
		}
		
		$resArray['ct'] = $ct;
		$resArray['start'] = $ct+$start;
		return $resArray;
	}
	
	public function getAuditLogs($start){
		$start		= 0;
		$limit		= 10;
		$resArray	= array();
		$q1			= $this->db->query("SELECT tb1.* , tb2.au_type_desc , tb3.name
										FROM bmc_audit_activity_logs tb1
										INNER JOIN bmc_audit_activity_type tb2	ON tb2.au_type_id = tb1.au_type
										LEFT JOIN bmc_users tb3 ON tb3.id = tb1.user_id
										ORDER BY tb1.au_log_id DESC LIMIT $start , $limit");

		if ($q1->getNumRows() > 0){
			foreach ($q1->getResult() as $row){
				$resArray[]	= $row;
			}
		}

		return $resArray;
	}

	public function srchPaymentData($getRefArray){
		$resArray	= array();
		$start		= $getRefArray["start"];
		$keyWord	= $getRefArray["keyWord"];
		$limit		= "50";
		
		$resArray['load']	= "0";
		
		$sql	= " SELECT tb1.trans_id , tb1.msisdn , tb1.receipt , tb1.reference , tb1.trans_status , tb1.amount , 
						   tb2.trans_date , tb3.wallet_name , tb3.wallet_alias , tb4.* , tb5.*
					FROM bmc_malipo tb1
					INNER JOIN bmc_malipo_details tb2	ON tb2.trans_id = tb1.trans_id
					INNER JOIN bmc_malipo_wallets tb3 ON tb3.wallet_id = tb2.trans_sp
					INNER JOIN bmc_malipo_status tb4 ON tb4.st_code = tb1.trans_status
					INNER JOIN bmc_patient_profile tb5 ON tb5.pat_id = tb1.pat_id
					WHERE (tb1.reference LIKE '%$keyWord%')  || (tb1.receipt LIKE '%$keyWord%' ) || 
						  (tb1.msisdn LIKE '%$keyWord%') || (tb5.pat_num LIKE '%$keyWord%')  ||
						  (tb5.sur_name LIKE '%$keyWord%'  || (tb5.other_names  LIKE '%$keyWord%'))
					ORDER BY tb1.trans_id DESC LIMIT  $start , $limit ";
					
		$q1		= $this->db->query($sql);
		$ct		= $q1->getNumRows();
		
		if ($ct > 0){
			$queryRows	= $q1->getResult();
			
			foreach ($queryRows as $row){
				$resArray[] = $row;
			}
		}
		
		if($ct  >= $limit && $ct%$limit == 0){
			$resArray['load']	= "1";
		}
		
		$resArray['ct'] = $ct;
		$resArray['start'] = $ct+$start;
		
		return $resArray;		
	}

	public function doSynchBillPaymentsEHMS(){
		$this->Engine	= new \App\Models\Engine();
		$newBillArr		= $this->Engine->getBillToSynch();
		
        if( isset($newBillArr->bill_id) > 0){
            $hashArr        = getApiKeyHashPair();
            $billNum        = $newBillArr->control_num;
            $controlNum     = $newBillArr->bill_num;
            $bill_id        = $newBillArr->bill_id;

            $requestData    = array(
                "RequestCode" => "BATCH_PAYMENT_CONFIRM",
                "RequestTimestamp" => date("Y-m-d H:i:s"),
                "RequestKey" => $hashArr["key"],
                "RequestHash" => $hashArr["hash"],
                "BillNumber" => $billNum,
                "ControlNumber" => $controlNum,
                "AmountPaid" => $newBillArr->bill_total_paid,
                "BillTotal" => $newBillArr->bill_total_cost,
                "PaymentReceipt" => $newBillArr->bill_receipt,
                "PayStatus" => $newBillArr->is_closed,
                "PaymentChannel" => $newBillArr->payArray 
            );

            $requestJson    = json_encode($requestData);

            $status     = "2";
            $statusMsg  = "Unable to Synch";
            $respDate   = "";
			/*
			echo $requestJson;
			
			if($bill_id == "342958"){
				return;
			}*/

            $url        = "http://10.15.0.20:30005/ehms/payment/BillPayment";
            $sXML       = $this->Engine->doCurljson($url , $requestJson);
            $jsonData   = $sXML["data"];

            $globLogId  = saveApiLogs( json_encode(array($requestJson, $jsonData)) );

            if( !json_validator($jsonData) ){
                $statusMsg  = "Invalid Json From EHMS"; //Invalid Json
            }

            $responseArr = json_decode($jsonData , 1);

            if( !validateApiPayResponseData($responseArr) ){
                $statusMsg  = "Invalid Data Format From EHMS"; //Missing data in request
            }
            else{
                $reqKey         = $responseArr["ResponseKey"];
                $reqHash        = $responseArr["ResponseHash"];
                $statusCode     = $responseArr["StatusCode"];
                $statusMsg      = $responseArr["StatusMsg"];
                $retBillNum     = $responseArr["BillNumber"];
    
                if(!verifyApiKeyHashPair($reqKey , $reqHash)){
                    //Invalid Hash
                    $statusMsg  = "Invalid Security Hash From EHMS";
                }
                else{
                    if($retBillNum == $billNum){
                        $status     = "1";
                    }
                }
            }

            $this->Engine->doApiPaymentBillUpdate($bill_id , $status, $statusMsg, $respDate , $globLogId);
        }
	}

	public function cronstart($id){
		$q1	= $this->db->query("SELECT cronstart , cronend , cronduration  FROM bmc_cron_log 
								WHERE cron_id = '$id' LIMIT 1");
		
		$row			= $q1->getResult()[0];
		$nowTime		= strtotime(date("Y-m-d H:i:s"));
		$cronstart		= strtotime($row->cronstart);
		$cronend		= strtotime($row->cronend);
		$cronduration	= $row->cronduration;
		$curr_ip		= getCurrIps();
		
		if($cronend >= $cronstart || ($nowTime - $cronstart) > $cronduration){
			$this->db->query("UPDATE bmc_cron_log SET sessip = '$curr_ip' , cronstart = NOW() WHERE cron_id = '$id' ");//start cron service
		}
		else{
			die("Denied. Another instance of this service is active.");//die another cron is in session
		}
	}

	public function cronend($id){
		$this->db->query("UPDATE bmc_cron_log SET cronend = NOW() WHERE cron_id = '$id' ");//end cron service
	}
	
	public function getPaymentItemData( $req_item_id ){
		$resArray	= array();
		
		$sql	= " SELECT tb1.trans_id , tb1.msisdn , tb1.receipt , tb1.reference , tb1.trans_status , tb1.amount , 
						   tb2.trans_date , tb3.* , tb4.* , tb5.*
					FROM bmc_malipo tb1
					INNER JOIN bmc_malipo_details tb2	ON tb2.trans_id = tb1.trans_id
					INNER JOIN bmc_malipo_wallets tb3 ON tb3.wallet_id = tb2.trans_sp
					INNER JOIN bmc_malipo_status tb4 ON tb4.st_code = tb1.trans_status
					INNER JOIN bmc_patient_profile tb5 ON tb5.pat_id = tb1.pat_id
					WHERE tb1.trans_id = '$req_item_id' LIMIT 1";
				
		$q1		= $this->db->query($sql);
		$ct		= $q1->getNumRows();
		
		if ($ct > 0){
			$resArray	= $q1->getResult()[0];
		}
		
		return $resArray;		
	}

	public function verifyReceipt( $trans_id , $receipt ){
		$resArray	= array();
		$q1			= $this->db->query("SELECT tb1.trans_id FROM bmc_malipo tb1
										WHERE tb1.trans_id = '$trans_id' && tb1.receipt = '$receipt' LIMIT 1 ");

		if ($q1->getNumRows() > 0){			
			$resArray	= get_instance()->Engine->getPaymentItemData( $trans_id ); //Get Payment Details
		}

		return $resArray;
	}	

	public function getPatinetPayments( $pat_id , $start ){
		$resArray	= array();
		$limit		= 10;
		
		$sql	= " SELECT tb1.trans_id , tb1.msisdn , tb1.receipt , tb1.reference , tb1.trans_status , tb1.amount , 
						   tb2.trans_date , tb3.wallet_alias , tb3.wallet_name  , tb4.*
					FROM bmc_malipo tb1
					INNER JOIN bmc_malipo_details tb2	ON tb2.trans_id = tb1.trans_id
					INNER JOIN bmc_malipo_wallets tb3 ON tb3.wallet_id = tb2.trans_sp
					INNER JOIN bmc_malipo_status tb4 ON tb4.st_code = tb1.trans_status
					WHERE (tb1.pat_id = '$pat_id') ORDER BY tb1.trans_id DESC LIMIT $start , $limit ";
				
		$q1		= $this->db->query($sql);
		
		if ($q1->getNumRows() > 0){
			foreach ($q1->getResult() as $row){
				$resArray[] = $row;
			}
		}
		
		return $resArray;		
	}

	public function getPatienttBills( $pat_id ){
		$resArray	= array();
		$q1			= $this->db->query("SELECT * FROM bmc_bills tb1 
										INNER JOIN bmc_patient_profile tb2 ON tb2.pat_id = tb1.pat_id
										WHERE tb1.pat_id = '$pat_id' && tb1.is_hidden = '0' 
										ORDER BY tb1.bill_id DESC LIMIT 10");

		if ($q1->getNumRows() > 0){			
			foreach ($q1->getResult() as $row){
				$resArray[] = $row;
			}
		}

		return $resArray;
	}		

	public function getProductTypes(){
		$resArray	= array();
				
		$q1		= $this->db->query("SELECT * FROM bmc_bill_product_type");
		
		if ($q1->getNumRows() > 0){
			foreach ($q1->getResult() as $row){
				$resArray[] = $row;
			}			
		}
		
		return $resArray;		
	}

	public function getPayTerms(){
		$resArray	= array();
				
		$q1		= $this->db->query("SELECT * FROM bmc_bill_payment_terms WHERE is_active = '1' ");
		
		if ($q1->getNumRows() > 0){
			foreach ($q1->getResult() as $row){
				$resArray[] = $row;
			}			
		}
		
		return $resArray;		
	}	
	
	public function getSearchProduct($word){
		$resArray	= array();
				
		$q1		= $this->db->query("SELECT * FROM bmc_bill_product tb1
									INNER JOIN bmc_bill_product_type tb2 ON tb2.prod_type_id = tb1.prod_type_id
									INNER JOIN bmc_bill_product_cost tb3 ON tb3.prod_id = tb1.prod_id
									WHERE (tb1.prod_name LIKE '%$word%') || (tb2.type_name LIKE '%$word%')
									ORDER BY tb1.prod_id DESC");
		
		if ($q1->getNumRows() > 0){
			foreach ($q1->getResult() as $row){
				$row->tax		= 0;
				$row->count		= 0;
				$row->qty		= 1;
				$row->discount	= 0;

				$row->total			= $row->cost_amt;
				$row->taxHtml		= getTaxDropDown($row->prod_id);
				$row->discHtml		= getDiscountInput($row->prod_id);
				$row->costHtml		= getCostInput($row->cost_amt , $row->prod_id);
				$row->countHtml		= getCountInput($row->prod_id);
				$row->totalHtml		= getTotalInput($row->cost_amt , $row->prod_id);
				$row->delItemHtml	= getDeleteHtmlt($row->prod_id);

				$resArray[] = $row;
			}			
		}
		
		return $resArray;
	}
	
	public function getmonth(){
		$monthArr	 = array();
		$monthArr['leo']		  = (int)date('d');
		$monthArr['thsMonth']     = date('F');
		$monthArr['curr_year']    = date('Y');
		$monthArr['curr_month']   = (int)date('m');
		$monthArr['dayOfsale']    = (int)date('d');
		$crMonth1    = $monthArr['curr_month'];
		$crMonth2    = ($crMonth1 - 1);
		
		if($crMonth1 == 1)
			$crMonth2    = 12;
			
		$monthArr['crMonth1']    = $crMonth1;
		$monthArr['crMonth2']    = $crMonth2;		
		$monthArr['months']		 = array('1' => "January", '2' => "Febuary", '3' => "March", '4' => "April", '5' => "May", 
									     '6' => "June", '7' => "July", '8' => "August", '9' => "September", '10' =>  "October",
										 '11' => "November", '12' => "December");
		
		return $monthArr;
	}
	
	public function getWallets(){
		$resArray	= array();
		$q1			= $this->db->query("SELECT * FROM bmc_malipo_wallets ");
		
		if ($q1->getNumRows() > 0){
			$queryRows	= $q1->getResult();
			
			foreach ($queryRows as $row){
				$resArray[] = $row;
			}
		}
		
		return $resArray;		
	}
	
	public function getTxStatus(){
		$resArray	= array();
		$q1			= $this->db->query("SELECT * FROM bmc_malipo_status ");
		
		if ($q1->getNumRows() > 0){
			$queryRows	= $q1->getResult();
			
			foreach ($queryRows as $row){
				$resArray[] = $row;
			}
		}
		
		return $resArray;		
	}

	public function getLanguages(){
		$resArray	= array();
		$engObject 	= new \stdClass();
		$engObject2	= new \stdClass();
		$swaObject 	= new \stdClass();
		$swaObject2	= new \stdClass();
		
		$q1			= $this->db->query("SELECT * FROM bmc_language_strings ");

		if ($q1->getNumRows() > 0){
			$queryRows	= $q1->getResult();
			
			foreach ($queryRows as $row){
				$code = $row->string_code;

				$engObject->$code 		= $row->st_eng;
				$engObject2->$code		= $row->st_eng_2;
				$swaObject->$code		= $row->st_swa;
				$swaObject2->$code		= $row->st_swa_2;
			}
		}
		
		$resArray["eng"] = array($engObject , $engObject2);
		$resArray["swa"] = array($swaObject , $swaObject2);

		return $resArray;
	}	

	public function fetchAllUserList($start , $word , $type ){
		$resArray	= array();
		$limit		= 10;
		$nextstart	= $start;
		$extend		= "";
		$load		= "1";

		if($type > 0){
			$extend	= " && tb3.id = '$type' ";
		}

		if($word != ""){
			$extend	= " && (tb3.title LIKE '%$word%' || tb1.name LIKE '%$word%') ";
		}

		$q1			= $this->db->query("SELECT tb1.* , tb3.title , tb2.group_id , tb4.* FROM bmc_1.bmc_users tb1 
										INNER JOIN bmc_1.bmc_user_usergroup_map tb2 ON tb2.user_id = tb1.id
										INNER JOIN bmc_1.bmc_usergroups tb3 ON tb3.id = tb2.group_id
										LEFT JOIN bmc_user_profile_custom tb4 ON tb4.user_id = tb1.id
										WHERE (tb3.id != 8) ".$extend." 
										LIMIT  $start , $limit ");
		
		$ct	= $q1->getNumRows();

		if ($q1->getNumRows() > 0){			
			foreach ($q1->getResult() as $row){
				$resArray[] = $row;
				$nextstart++;
			}
		}
		
		if($ct  >= $limit && $ct%$limit == 0){
			$load	= "1";
		}

		$resArray["start"]	= array("prevstart" => $start , "nextstart" => $nextstart , 
									"word" => $word , "type" => $type , "load" => $load);
		
		return $resArray;
	}

	public function fetchAllUserGroups(){
		$resArray	= array();
		$q1			= $this->db->query("SELECT * FROM bmc_usergroups WHERE id > 8");
		
		if ($q1->getNumRows() > 0){
			
			foreach ($q1->getResult() AS $row){
				$id		= $row->id;

				$q2		= $this->db->query("SELECT COUNT(role_id) as rolesCount 
											FROM bmc_roles_group WHERE group_id = '$id' ");

				$rolesCount			= $q2->getResult()[0]->rolesCount;
				$row->rolesCount	= $rolesCount;

				$q3		= $this->db->query("SELECT COUNT(user_id) as userCount 
											FROM bmc_user_usergroup_map WHERE group_id = '$id' ");

				$userCount			= $q3->getResult()[0]->userCount;
				$row->userCount		= $userCount;				
				
				$resArray[] = $row;
			}
		}
		
		return $resArray;
	}

	public function fetchGroupsRolesArr($group_id){
		$resArray	= array();
		$q1			= $this->db->query("SELECT id as group_id , title 
										FROM bmc_usergroups WHERE id = '$group_id' LIMIT 1");
		
		if ($q1->getNumRows() > 0){
			$row		= $q1->getResult()[0];
			$title		= $row->title;
			$group_id	= $row->group_id;
			$roleArr	= array();
			
			$q2		= $this->db->query("SELECT role_id FROM bmc_roles_group WHERE group_id = '$group_id' ");

			if ($q2->getNumRows() > 0){
				foreach ($q2->getResult() AS $row){
					$roleArr[]	= $row->role_id;
				}
			}

			$row->roleArr	= $roleArr;
			
			$resArray	= array("group_id" => $group_id, "title" => $title, "roleArr" => $roleArr);
		}
		
		return $resArray;
	}

	public function fetchAllRoles(){
		$resArray	= array();
		$q1			= $this->db->query("SELECT * FROM bmc_roles tb1
										INNER JOIN bmc_roles_type tb2 ON tb2.role_type_id = tb1.role_group_id");
		
		if ($q1->getNumRows() > 0){
			foreach ($q1->getResult() AS $row){
				$rgroupdId	= $row->role_group_id;

				if( !isset($resArray[$rgroupdId]) ){
					$resArray[$rgroupdId] = array();
				}

				$resArray[$rgroupdId][]	= $row;
			}
		}

		return $resArray;
	}

	public function updateUserGroupRoles($rolesArr , $group_id){
		$dataMap	= array();

		if($rolesArr != null){
			foreach($rolesArr AS $role){
				$role		= (int)$role;
				
				$dataMap[]	= array("role_id" => $role, "group_id" => $group_id);
			}
		}

		$this->db->transStart();
		$builder = $this->db->table('bmc_roles_group');

		$builder->where('group_id', $group_id);
		$builder->delete();
		
		$builder = $this->db->table('bmc_roles_group');
		$builder->delete(['group_id' => $group_id]);

		if( sizeof($dataMap) > 0){
			$builder = $this->db->table('bmc_roles_group');
			$builder->insertBatch($dataMap);
		}

		$this->db->transComplete();
	}

	public function getAllUserRoles($userGroupsArr){
		$resArray	= array();

		foreach($userGroupsArr AS $group_id){
			
			if($group_id == 8){
				//$q1	= $this->db->query("SELECT role_id FROM bmc_roles ");//Allocate All roles
				$q1	= $this->db->query("SELECT role_id FROM bmc_roles_group WHERE role_group_id = '99' ");
			}
			else{
				$q1	= $this->db->query("SELECT role_id FROM bmc_roles_group WHERE role_group_id = '$group_id' ");
			}
			
			if ($q1->getNumRows() > 0){
				foreach ($q1->getResult() AS $row){

					if( !isset($resArray[$group_id]) ){
						$resArray[$group_id] = array();
					}

					$resArray[$group_id][]	= $row->role_id;
				}
			}
		}

		return $resArray;
	}

	public function fetchAllUserSearch($filter , $word){
		$resArray	= array();
		$limit		= 20;

		if($filter == "1"){
			$extend = "&& tb2.group_id = '$word')";
		}
		else{
			$extend = "&& (tb1.username LIKE '%$word%' || tb1.name LIKE '%$word%')";
		}

		$q1			= $this->db->query("SELECT tb1.* , tb3.title , tb2.group_id , tb4.* FROM bmc_1.bmc_users tb1 
										INNER JOIN bmc_1.bmc_user_usergroup_map tb2 ON tb2.user_id = tb1.id
										INNER JOIN bmc_1.bmc_usergroups tb3 ON tb3.id = tb2.group_id
										LEFT JOIN bmc_user_profile_custom tb4 ON tb4.user_id = tb1.id
										WHERE (tb2.group_id > 8) ".$extend."
										LIMIT  $limit ");
		
		if ($q1->getNumRows() > 0){
			$queryRows	= $q1->getResult();
			
			foreach ($queryRows as $row){
				$resArray[] = $row;
			}
		}
		
		return $resArray;
	}	

	public function fetchUserDetails($user_id){
		$resArray	= array();
		
		$q1			= $this->db->query("SELECT tb1.* , tb3.title , tb2.group_id , tb4.* , tb5.lang_code  FROM bmc_1.bmc_users tb1 
										INNER JOIN bmc_1.bmc_user_usergroup_map tb2 ON tb2.user_id = tb1.id
										INNER JOIN bmc_1.bmc_usergroups tb3 ON tb3.id = tb2.group_id
										LEFT JOIN bmc_user_profile_custom tb4 ON tb4.user_id = tb1.id
										LEFT JOIN bmc_language_type tb5 ON tb5.id = tb4.lang_id
										WHERE tb1.id = '$user_id' LIMIT 1");

		if ($q1->getNumRows() > 0){		
			$resArray	= $q1->getResult()[0];
		}
		
		return $resArray;
	}
	
	public function fetchUserTypes(){
		$resArray	= array();
		$q1			= $this->db->query("SELECT * FROM bmc_usergroups WHERE id > 8");
		
		if ($q1->getNumRows() > 0){
			$queryRows	= $q1->getResult();
			
			foreach ($queryRows as $row){
				$resArray[] = $row;
			}
		}
		
		return $resArray;	
	}

	public function fetchUserName($user_string){
		$q1	= $this->db->query("SELECT username  FROM bmc_users WHERE username = '$user_string' || email = '$user_string' LIMIT 1");

		if ($q1->getNumRows() > 0){
			return $q1->getResult()[0]->username;
		}

		return null;
	}

	public function fetchUserId($user_string){
		$q1	= $this->db->query("SELECT id  FROM bmc_users WHERE username = '$user_string' || email = '$user_string' LIMIT 1");

		if ($q1->getNumRows() > 0){
			return $q1->getResult()[0]->id;
		}

		return null;
	}

	public function fetchSiteLanguages(){
		$resArray	= array();
		$q1			= $this->db->query("SELECT *  FROM bmc_language_type ");

		if ($q1->getNumRows() > 0){
			$queryRows	= $q1->getResult();
			
			foreach ($queryRows as $row){
				$resArray[] = $row;
			}
		}

		return $resArray;
	}

	public function isExistingPatientNumber($patnum){
		$q1	= $this->db->query("SELECT pat_num  FROM bmc_patient_profile WHERE pat_num = '$patnum' LIMIT 1");

		if($q1->getNumRows() > 0){
			return TRUE;
		}

		return FALSE;
	}

	public function isExistingIdType($idType){
		$q1	= $this->db->query("SELECT type_id  FROM bmc_id_types WHERE type_id = '$idType' LIMIT 1");

		if($q1->getNumRows() > 0){
			return TRUE;
		}

		return FALSE;
	}

	public function isExistingKata($kata){
		$q1	= $this->db->query("SELECT kata_id  FROM bmc_mkoa_wilaya_kata WHERE kata_id = '$kata' LIMIT 1");

		if($q1->getNumRows() > 0){
			return TRUE;
		}

		return FALSE;
	}

	public function isExistingUsername($user_string){
		$q1	= $this->db->query("SELECT email  FROM bmc_1.bmc_users WHERE username = '$user_string' || email = '$user_string' LIMIT 1");

		if($q1->getNumRows() > 0){
			return TRUE;
		}

		return FALSE;
	}

	public function isExistingUserId($user_id){
		$q1	= $this->db->query("SELECT id  FROM bmc_users WHERE id = '$user_id' LIMIT 1");

		if($q1->getNumRows() > 0){
			return TRUE;
		}

		return FALSE;
	}
	
	public function isExistLanguage($userlang){
		$q1	= $this->db->query("SELECT id  FROM bmc_language_type WHERE id = '$userlang' LIMIT 1");

		if($q1->getNumRows() > 0){
			return TRUE;
		}

		return FALSE;
	}

	public function validateDOB($patDob){
		$dob		= strtotime(date($patDob));
		$tomorrow	= strtotime(date('Y-m-d', strtotime('+1 days')));
		$past120	= strtotime(date('Y-m-d', strtotime('-120 year')));

		if($dob > $past120 && $dob < $tomorrow){
			return TRUE;
		}

		return FALSE;
	}

	public function validateIDNum($idNum){
		if(strlen($idNum) > 0 && strlen($idNum) < 30){
			return TRUE;
		}

		return FALSE;
	}

	public function validateName( $name ){
		if ( strlen($name) < 4 || strlen($name) > 30 ){
			return FALSE;
		}

		return TRUE;
	}

	public function validateUsername( $username ){
		if ( strlen($username) < 4 || strlen($username) > 10 ){
			return FALSE;
		}

		return TRUE;
	}

	public function validateEmail($email){
		if ( !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return FALSE;
		}

		return TRUE;
	}

	public function validatePhone($phone){
		if( !preg_match('/^[0-9]{10}+$/', $phone) && !preg_match('/^[0-9]{12}+$/', $phone)){
			return FALSE;
		}

		return TRUE;
	}	

	public function isPassSecure($password){
		// Validate password strength
		$uppercase = preg_match('@[A-Z]@', $password);
		$lowercase = preg_match('@[a-z]@', $password);
		$number    = preg_match('@[0-9]@', $password);
		$specialChars = preg_match('@[^\w]@', $password);

		if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
			return false;
		}

		return true;		
	}

	public function passAuth($passhash , $user_id , $pass){
		$hashOld = \Joomla\CMS\User\UserHelper::verifyPassword($pass, $passhash , $user_id);

		if($hashOld == 1){
			return TRUE;
		}

		return FALSE;
	}

	public function saveBillDetails($dataMap , $billArray){
		$pat_id	= $dataMap["pat_id"];

		$dataMap["bill_total_cost"]	= $billArray["grandTotal"];
		$dataMap["bill_num"]		= getBillNumber();
		$dataMap["bill_user_id"]	= "977";
		$dataMap["bill_items_json"]	= json_encode($billArray);

		if(!isset($dataMap["control_num"]))
			$dataMap["control_num"] = $dataMap["bill_num"];

		unset($billArray["grandTotal"]);

        $this->db->transStart();

        $builder = $this->db->table('bmc_bills');
        $builder->insert($dataMap);

        $bill_id    = $this->db->insertID();

        foreach($billArray AS $billArr){
			$item_id = 0;

			if(isset($billArr['id'])){
				$item_id = $billArr['id'];
			}
			else if(isset($billArr['itemId'])){
				$item_id = $billArr['itemId'];
			}
			
            $dataMap    = array("bill_id" => $bill_id, 
							    "item_id" => $item_id, 
								"item_qty" => $billArr["qty"], 
								"item_cost" => $billArr["cost"], 
                                "item_tax" => $billArr["tax"], 
								"item_discount" => $billArr["discount"], 
								"total_cost" => $billArr["total"]  );
			
            $builder = $this->db->table('bmc_bills_items');
            $builder->insert($dataMap);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return 0;
        }

		$row = get_instance()->Engine->getLastMasterEntry($pat_id , $entType = "99");
		
		if ( isset( $row->close_bal ) && $row->close_bal > 0 ){
			get_instance()->Engine->updatePayBillUsingWallet($pat_id , $bill_id);
		}

		return $bill_id;
	}

	public function fetchLocalItemId($itemCode , $desc , $cost){
		$prod_id	= 0;

		$q1	= $this->db->query("SELECT * FROM bmc_bill_product tb1
								INNER JOIN bmc_bill_product_cost tb2
								ON tb2.prod_id = tb1.prod_id
								WHERE tb1.prod_code = '$itemCode' LIMIT 1");

		if($q1->getNumRows() > 0){
			$prod_id	= $q1->getResult()[0]->prod_id;
		}
		else{
			$data = array("prod_name" => $desc, "prod_code" => $itemCode , "siku" => date("Y-m-d H:i:s") );

			$builder = $this->db->table('bmc_bill_product');
			$builder->insert($data);

			$prod_id	= $this->db->insertID();
			$dataMap	= array("prod_id" => $prod_id , "cost_amt" => $cost, "siku" => date("Y-m-d H:i:s") );
			
			$builder = $this->db->table('bmc_bill_product_cost');
			$builder->insert($dataMap);
			
		}

		return $prod_id;
	}

	public function saveProductDetails($data , $pCost){
		$this->db->transStart();

		$builder = $this->db->table('bmc_bill_product');
		$builder->insert($data);
		$prod_id	= $this->db->insertID();
		
		$dataMap  = array("prod_id" => $prod_id , "cost_amt" => $pCost, "rec_by" => $data["rec_by"], "siku" => $data["siku"]);
		
		$builder = $this->db->table('bmc_bill_product_cost');
		$builder->insert($dataMap);

		$this->db->transComplete();

		if ($this->db->transStatus() === false) {
			return false;
		}
		
		return true;			

	}

	public function savePatientDetails($dataPatient){
		$this->db->transStart();

		$builder = $this->db->table('bmc_patient_profile');
		$builder->insert($dataPatient);
		$pat_id	= $this->db->insertID();

		$dataMap["pat_id"] = $pat_id;
		
		$builder = $this->db->table('bmc_patient_wallet');
		$builder->insert($dataMap);

		$this->db->transComplete();

		if ($this->db->transStatus() === false) {
			return 0;
		}
		
		return $pat_id;		
	}
	
	public function saveUserDetails($dataUsers , $dataMap , $dataProfile ){
		$this->db->transStart();

		$builder = $this->db->table('bmc_users');
		$builder->insert($dataUsers);
		$user_id	= $this->db->insertID();

		$dataMap["user_id"] = $user_id;
		$dataProfile["user_id"] = $user_id;
		
		$builder = $this->db->table('bmc_1.bmc_user_usergroup_map');
		$builder->insert($dataMap);

		$builder = $this->db->table('bmc_1.bmc_user_profile_custom');
		$builder->insert($dataProfile);

		$this->db->transComplete();

		if ($this->db->transStatus() === false) {
			return false;
		}
		
		return true;
	}


	public function updateUserPassword($user_id , $pass ){
		$passhash	= password_hash($pass, 1);

		$this->db->transStart();
		$q1	= $this->db->query("UPDATE bmc_users SET password = '$passhash' WHERE id = '$user_id' "); //Update Password
		//Log change activity
		$this->db->transComplete();

		if ($this->db->transStatus() === false) {
			return false;
		}

		$killSession = \Joomla\CMS\User\UserHelper::destroyUserSessions($user_id, false, null);
		
		return true;		
	}

	public function updateUserGroup($user_id , $usergroup , $dataLog ){
		//Check if group is valid
		$q1	= $this->db->query("SELECT title  FROM bmc_usergroups WHERE id = '$usergroup' LIMIT 1");

		if( $q1->getNumRows() == 0 ){
			return false;
		}

		$transStatus 	= FALSE;
		$this->Engine	= new \App\Models\Engine();
		
		//Save new group
		$this->db->transStart();
		$q2	= $this->db->query("UPDATE bmc_user_usergroup_map SET group_id = '$usergroup' WHERE user_id = '$user_id' ");
		$this->db->transComplete();

		$transStatus	= $this->db->transStatus();
		$this->Engine->saveActivityLogs($dataLog , $transStatus );

		//dO LOGS
		
		return $transStatus;
		//Log change activity
	}

	public function updateUserProfile($dataUpdate , $profUpdate , $user_id){
		//Update user details
		$this->db->transStart();

		if( sizeof($dataUpdate) > 0){
			$builder = $this->db->table('bmc_users');		
			$builder->where('user_id', $user_id);
			$builder->update($dataUpdate);
		}

		if( sizeof($profUpdate) > 0){
			$builder2 = $this->db->table('bmc_user_profile_custom');		
			$builder2->where('user_id', $user_id);
			$builder2->update($profUpdate);	
		}

		$this->db->transComplete();

		if ($this->db->transStatus() === false) {
			return false;
		}
		
		return true;		
	}

	public function saveActivityLogs($dataLog , $transStatus ){
		$logStatus	= ( ($transStatus == TRUE) ? "1":"0");
		
		$dataLog["act_status"]	= $logStatus;

		$this->db->transStart();

		if( sizeof($dataLog) > 0){
			$builder = $this->db->table('bmc_user_activity_logs');
			$builder->insert($dataLog);
		}

		$this->db->transComplete();

		if ($this->db->transStatus() === false) {
			return false;
		}
		
		return true;
	}	

	public function permitedChangeUserProfile($globalObject , $user_id){
		$permitedObject 	= new \stdClass();
		$s_user_id			= $globalObject->userInfo->id;

		$permitedObject->groupChange	= FALSE;
		$permitedObject->profChange		= FALSE;
		$permitedObject->passChange		= FALSE;
		$permitedObject->passSendReset	= FALSE;
		$permitedObject->emailChange	= FALSE;
		$permitedObject->logsView		= FALSE;
		
		if( ($s_user_id != $user_id) && ($globalObject->permitSuperUser || $globalObject->permitGLOBALManager ) ){
			$permitedObject->groupChange	= TRUE; //PERMITED GROUP CHANGE
		}
		
		if( ($s_user_id != $user_id || $globalObject->permitSuperUser || $globalObject->permitGLOBALManager || $globalObject->permitICTManager) ){
			$permitedObject->profChange	= TRUE; //PERMITED PROFILE CHANGE
			$permitedObject->logsView	= TRUE; //PERMITED LOGS VIEW
		}

		if( ($globalObject->permitSuperUser || $globalObject->permitGLOBALManager || $globalObject->permitICTManager) ){
			$permitedObject->passSendReset		= TRUE; //PERMITED SEND PASSWORD RESET
		}
		
		if( ($s_user_id == $user_id) ){
			$permitedObject->passChange		= TRUE; //PERMITED PASSWORD CHANGE
			$permitedObject->emailChange	= TRUE; //PERMITED EMAIL CHANGE
		}

		return $permitedObject;
	}

	public function fetchIdTypes(){
		$resArray	= array();
		$q1			= $this->db->query("SELECT *  FROM bmc_id_types ");

		if ($q1->getNumRows() > 0){
			$queryRows	= $q1->getResult();
			
			foreach ($queryRows as $row){
				$resArray[] = $row;
			}
		}

		return $resArray;
	}

	public function fetchMkoaList(){
		$resArray	= array();
		$q1			= $this->db->query("SELECT *  FROM bmc_mkoa ");

		if ($q1->getNumRows() > 0){
			$queryRows	= $q1->getResult();
			
			foreach ($queryRows as $row){
				$resArray[] = $row;
			}
		}

		return $resArray;
	}

	public function fetcMkoaWilayaList($mkoa_id){
		$resArray	= array();
		$q1			= $this->db->query("SELECT *  FROM bmc_mkoa_wilaya WHERE mkoa_id = '$mkoa_id' ");

		if ($q1->getNumRows() > 0){
			$queryRows	= $q1->getResult();
			
			foreach ($queryRows as $row){
				$resArray[] = $row;
			}
		}

		return $resArray;
	}
	
	public function fetcWilayaKataList($wilaya_id){
		$resArray	= array();
		$q1 = $this->db->query("SELECT *  FROM bmc_mkoa_wilaya_kata WHERE wilaya_id = '$wilaya_id' ");

		if ($q1->getNumRows() > 0){
			$queryRows	= $q1->getResult();
			
			foreach ($queryRows as $row){
				$resArray[] = $row;
			}
		}

		return $resArray;
	}

	public function isExistingPatID($pat_id){
		$q1	= $this->db->query("SELECT pat_id  FROM bmc_patient_profile WHERE pat_id = '$pat_id' LIMIT 1");

		if($q1->getNumRows() > 0){
			return TRUE;
		}

		return FALSE;
	}

	public function isExistingPayTerms($pay_term){
		$q1	= $this->db->query("SELECT terms_id  FROM bmc_bill_payment_terms WHERE terms_id = '$pay_term' LIMIT 1");

		if($q1->getNumRows() > 0){
			return TRUE;
		}

		return FALSE;
	}

	public function getWalletLogs($pat_id){

		$resArray	= array();

		$q1 = $this->db->query("SELECT * FROM bmc_profile_bal_log tb1
								INNER JOIN bmc_malipo tb2 ON tb2.trans_id = tb1.trans_id
								LEFT JOIN bmc_bills tb3 ON tb3.bill_id = tb1.bill_id
								WHERE tb1.pat_id = '$pat_id' ORDER BY log_id DESC ");

		if ($q1->getNumRows() > 0){			
			foreach ($q1->getResult() as $row){
				$billCost	= (int)$row->bill_total_cost;
				$billCost > 0 ? $row->amount = 0 : $row->bill_total_cost = 0;

				$row->postName = $row->post_type == "2" ? "Bill Pay" : "Top Up";

				if( $row->bill_total_cost != $row->item_amt && $row->bill_id > 0){
					$row->bill_total_cost = $row->item_amt;
				}

				$resArray[] = $row;
			}
		}

		return $resArray;
	}

	public function doSaveTemporaryNewTotal($billNum , $newTotal){
		$dataMap	= array("bill_total_cost" => $newTotal );

		$builder = $this->db->table('bmc_bills');
		$builder->where('bill_num', $billNum);
		$builder->update($dataMap);
	}

	public function doGlobalPushPayments(){
		
	}

	public function fetchPatientSearch($word , $start){
		return get_instance()->Engine->doFetchPatients($type = 2 , $word , $start);
	}

	public function fetchPatientList($start){
		return get_instance()->Engine->doFetchPatients($type = 1 , $word = "" , $start );
	}

	public function doFetchPatients($type , $word , $start ){
		$nextstart	= $start;
		$resArray	= array();
		$extSql 	= "";
		$loadMore	= "1";
		$limit		= 50;

		if($type == 2){
			$extSql = " WHERE (tb1.pat_num LIKE '%$word%') || (tb1.sur_name LIKE '%$word%') || 
							  (tb1.other_names LIKE '%$word%') || (tb1.phone LIKE '%$word%') ";
		}

		$q1 = $this->db->query("SELECT tb1.* , tb2.* , 
									(SELECT SUM(sbt.bill_total_cost - sbt.bill_total_paid ) 
									 FROM bmc_bills AS sbt WHERE (sbt.pat_id = tb1.pat_id) && (sbt.is_closed != '1') ) 
									AS bill_amount
								FROM bmc_patient_profile tb1
								INNER JOIN bmc_patient_wallet tb2 ON tb2.pat_id = tb1.pat_id
								LEFT JOIN bmc_mkoa_wilaya_kata tb3 ON tb1.loc_kata = tb3.kata_id
								LEFT JOIN bmc_mkoa_wilaya tb4 ON tb4.wilaya_id = tb3.wilaya_id
								LEFT JOIN bmc_mkoa tb5 ON tb5.mkoa_id = tb4.mkoa_id
								".$extSql." ORDER BY tb1.pat_id DESC LIMIT $start  , $limit ");

		$ct		= $q1->getNumRows();

		if ($q1->getNumRows() > 0){			
			foreach ($q1->getResult() as $row){
				$row->bill_amount = $row->bill_amount ?? 0;

				$resArray[] = $row;
				$nextstart++;
			}
		}

		if($ct  >= $limit && $ct%$limit == 0){
			$loadMore	= "0";
		}

		$resArray["start"]	= array("prevstart" => $start , "nextstart" => $nextstart , 
									"word" => $word, "loadMore" => $loadMore );

		return $resArray;
	}

	public function fetchPatientRecord($pat_id){
		$resArray	= array();
		$q1 = $this->db->query("SELECT tb1.* , tb2.* , tb3.kata_name, tb4.wilaya_name , tb5.mkoa_name  FROM bmc_patient_profile tb1
								INNER JOIN bmc_patient_wallet tb2 ON tb2.pat_id = tb1.pat_id
								LEFT JOIN bmc_mkoa_wilaya_kata tb3 ON tb1.loc_kata = tb3.kata_id
								LEFT JOIN bmc_mkoa_wilaya tb4 ON tb4.wilaya_id = tb3.wilaya_id
								LEFT JOIN bmc_mkoa tb5 ON tb5.mkoa_id = tb4.mkoa_id
								WHERE (tb1.pat_id = '$pat_id') || (tb1.pat_num) = '$pat_id' LIMIT 1");

		if ($q1->getNumRows() > 0){	
			$resArray = $q1->getResult()[0];
		}

		return $resArray;
	}

	public function fetchPatientRecordNew($pat_id , $ext){
		$resArray	= array();
		$extend		= ($ext == "1") ? "(tb1.pat_id = '$pat_id') || " : "";

		$q1 = $this->db->query("SELECT tb1.* , tb2.* , tb3.kata_name, tb4.wilaya_name , tb5.mkoa_name  FROM bmc_patient_profile tb1
								INNER JOIN bmc_patient_wallet tb2 ON tb2.pat_id = tb1.pat_id
								LEFT JOIN bmc_mkoa_wilaya_kata tb3 ON tb1.loc_kata = tb3.kata_id
								LEFT JOIN bmc_mkoa_wilaya tb4 ON tb4.wilaya_id = tb3.wilaya_id
								LEFT JOIN bmc_mkoa tb5 ON tb5.mkoa_id = tb4.mkoa_id
								WHERE ".$extend." (tb1.pat_num = '$pat_id') LIMIT 1");

		if ($q1->getNumRows() > 0){	
			$resArray = $q1->getResult()[0];
		}

		return $resArray;
	}

	public function fetchBillProducts($word , $start , $type){
		$resArray	= array();
		$limit		= 50;
		$nextstart	= $start;
		$load		= "0";

		$q1 = $this->db->query("SELECT tb1.* , tb2.*   FROM bmc_bill_product tb1
								INNER JOIN bmc_bill_product_cost tb2 ON tb2.prod_id = tb1.prod_id
								WHERE (tb1.prod_name LIKE '%$word%') || (tb1.prod_desc LIKE '%$word%') 
								ORDER BY tb1.prod_id DESC LIMIT $start, $limit");

		$ct	= $q1->getNumRows();

		if ($q1->getNumRows() > 0){	
			foreach ($q1->getResult() as $row){
				$resArray[] = $row;
				$nextstart++;
			}
		}

		if($ct  >= $limit && $ct%$limit == 0){
			$load	= "1";
		}
		
		$resArray["start"]	= array("prevstart" => $start , "nextstart" => $nextstart , 
									"word" => $word , "type" => $type , "load" => $load);

		return $resArray;
	}	

	public function retMweziID($date){
		$mwezi_id	= 0;
		$mwaka		= (int)date("Y" , strtotime($date));
		$mwezi		= (int)date("m" , strtotime($date));
		
		$q1	= $this->db->query("SELECT mwezi_id FROM bmc_mwezi WHERE mwezi = '$mwezi' && mwaka = '$mwaka' LIMIT 1");

		if ($q1->getNumRows() > 0){
			$mwezi_id = $q1->getResult()[0]->mwezi_id;
		}
		else{
			$mwezi_id = get_instance()->Engine->genMweziID($date);
		}

		return $mwezi_id;
	}
	
	public function genMweziID($date){
		$mwezi_id	= 0;
		$mwaka		= (int)date("Y" , strtotime($date));
		$mwezi		= (int)date("m" , strtotime($date));

		$dataMap = array("mwaka" => $mwaka, "mwezi" => $mwezi, "siku_made" => date("Y-m-d H:i:s"));
						 
		$builder = $this->db->table('bmc_mwezi');
		$builder->insert($dataMap);
		$mwezi_id	= $this->db->insertID();

		return $mwezi_id;
	}

	public function doSaveGwayLogs($dataLog){
		$builder = $this->db->table('bmc_gway_api_logs');
		$builder->insert($dataLog);
		
		return $this->db->insertID();
	}

	public function doValidateReference($pat_ref){
		$resObject 	= get_instance()->Engine->getBillDetailsForMalipoValidation($pat_ref);

		if( isset($resObject->bill_amount) ){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}

	public function getBillDetailsForMalipoValidation($ref){
		$resArray 	= new \stdClass();
		$siku		= date("Y-m-d H:i:s");
		$flag		= 1;

		$resArray->typ	= "";

		while($flag <= 2){
			$q1 		= $this->db->query("SELECT tb2.sur_name , tb2.other_names , tb2.pat_num , tb1.bill_num , tb1.control_num , tb1.bill_num AS reference , 
												(SELECT SUM(sbt.bill_total_cost - sbt.bill_total_paid ) 
												FROM bmc_bills AS sbt 
												WHERE ( (sbt.bill_num = '$ref') || (sbt.control_num  = '$ref') ) && (tb1.is_closed != '1') ) AS bill_amount		
											FROM bmc_bills tb1 
											INNER JOIN bmc_patient_profile tb2 ON tb2.pat_id = tb1.pat_id
											WHERE ((tb1.bill_num = '$ref') || (tb1.bill_id  = '$ref') ||
												(tb1.control_num  = '$ref') ) && (tb1.is_closed != '1') LIMIT 1");
			
			if ($q1->getNumRows() > 0){
				$resArray		= $q1->getResult()[0];
				$resArray->typ	= "cnum";
				$flag			= 3;
			}
			else{
				$q2		= $this->db->query("SELECT tb2.sur_name , tb2.other_names , tb2.pat_num , tb1.bill_num ,  tb2.pat_num AS reference , 
												(SELECT SUM(sbt.bill_total_cost - sbt.bill_total_paid ) 
												FROM bmc_bills AS sbt WHERE (sbt.pat_id = tb2.pat_id) && (sbt.is_closed != '1') ) AS bill_amount 
											FROM bmc_bills tb1 
											INNER JOIN bmc_patient_profile tb2 ON tb2.pat_id = tb1.pat_id
											WHERE (tb2.pat_num  = '$ref') && (tb1.is_closed != '1') LIMIT 1");

				if ($q2->getNumRows() > 0){
					$resArray				= $q2->getResult()[0];
					
					$resArray->typ	= "wallet";
					$flag			= 3;
				}
				else{
					$pat_id		= get_instance()->Engine->retPatIdFromPatref( $ref );

					if($pat_id > 0){
						//Create dummy bill
						
						$billArray	= array("grandTotal" => 1);
						$dataMap    = array("pat_id" => $pat_id, "bill_terms" => "1", "bill_creation" => $siku , 
											"bill_comments" => "" , "is_hidden" => "1");

						get_instance()->Engine->saveBillDetails($dataMap , $billArray);
					}
					
					$flag++;
				}
				
			}

			if(isset($resArray->bill_amount)){
				$resArray->bill_amount	= $resArray->bill_amount ?? 0;
			}
		}

		return $resArray;
	}

	public function getBillUsingId($bill_id){
		$resArray 	= new \stdClass();
		$q1 		= $this->db->query("SELECT tb1.bill_num , tb1.control_num 
										FROM bmc_bills tb1 
										INNER JOIN bmc_patient_profile tb2 ON tb2.pat_id = tb1.pat_id
										WHERE tb1.bill_id  = '$bill_id' LIMIT 1");

		if ($q1->getNumRows() > 0){
			$resArray	= $q1->getResult()[0];
		}

		return $resArray;
	}

	public function isProdtype($typeId){
		$q1 = $this->db->query("SELECT prod_type_id FROM bmc_bill_product_type WHERE prod_type_id = '$typeId' LIMIT 1");
	
		if ($q1->getNumRows() > 0){	
			return TRUE;
		}
		
		return FALSE;
	}	

	public function isValidTxn($txnId){
		$q1 = $this->db->query("SELECT tb1.trans_id FROM bmc_malipo tb1 
							  INNER JOIN bmc_malipo_details tb2 ON tb2.trans_id = tb1.trans_id
							  WHERE tb1.receipt = '$txnId' LIMIT 1");
	
		if ($q1->getNumRows() > 0){	
			return TRUE;
		}
		
		return FALSE;
	}

	public function getBillFetchDetails($billFetchArray){
		$pat_num	= $billFetchArray["ref"];
		$resArray	= array();
		$q1 		= $this->db->query("SELECT tb1.sur_name , tb1.other_names , tb2.bill_num , tb2.bill_total_cost, tb2.bill_siku 
										FROM bmc_patient_profile tb1 
										INNER JOIN bmc_bills tb2 ON tb2.pat_id = tb1.pat_id
										WHERE tb1.pat_num = '$pat_num' LIMIT 1");

		if ($q1->getNumRows() > 0){
			$resArray	= $q1->getResult()[0];
		}
		
		return $resArray;
	}

	public function getBillFetchAllBills($start){
		$word = "";
		get_instance()->Engine->getBillFetchAllBillsWithWord($start , $word);
	}

	public function getBillFetchAllBillsWithWord($start , $word){
		$limit		= 50;
		$resArray	= array("load" => "0");
		$nextstart	= $start;
		$extend		= "";
		
		if(strlen($word) > 0){
			$extend	= "WHERE tb2.bill_num LIKE '%$word%' || tb1.pat_num LIKE '%$word%'  
			           || tb1.pat_id LIKE '%$word%' || tb1.sur_name LIKE '%$word%' || tb1.other_names LIKE '%$word%' ";
		}
		
		$q1 		= $this->db->query("SELECT tb1.sur_name , tb1.other_names , tb1.pat_id , tb2.bill_id , tb2.bill_num , tb2.bill_total_cost, tb2.bill_siku, 
											   tb2.bill_total_paid, tb2.is_closed ,
											   tb2.is_hidden , tb3.name AS staffname
										FROM bmc_patient_profile tb1 
										INNER JOIN bmc_bills tb2 ON tb2.pat_id = tb1.pat_id
										LEFT JOIN bmc_users tb3 ON tb3.id = tb2.bill_user_id
										".$extend." ORDER BY tb2.bill_id  DESC LIMIT $start, $limit");

		$ct	= $q1->getNumRows();

		if ($q1->getNumRows() > 0){
			foreach ($q1->getResult() as $row){
				$resArray[] = $row;
				$nextstart++;
			}			
		}

		if($ct  >= $limit && $ct%$limit == 0){
			$resArray['load']	= "1";
		}
		
		$resArray['ct']		= $ct;
		$resArray['start']	= $ct+$start;
		$resArray['word']	= $word;
		
		return $resArray;
	}

	public function doBillSearch($word){
		$limit		= 50;
		$start		= 0;
		$resArray	= array();
		$nextstart	= $start;

		$q1 		= $this->db->query("SELECT tb1.sur_name , tb1.other_names , tb1.pat_id , tb2.bill_id , tb2.bill_num , tb2.bill_total_cost, tb2.bill_siku, 
											   tb2.bill_total_paid, tb2.is_closed , tb3.name AS staffname
										FROM bmc_patient_profile tb1 
										INNER JOIN bmc_bills tb2 ON tb2.pat_id = tb1.pat_id
										LEFT JOIN bmc_users tb3 ON tb3.id = tb2.bill_user_id
										WHERE tb2.bill_num LIKE '%$word%' || tb1.pat_num LIKE '%$word%'  || tb1.pat_id LIKE '%$word%'
										ORDER BY tb2.bill_id  DESC LIMIT $start, $limit");

		if ($q1->getNumRows() > 0){
			foreach ($q1->getResult() as $row){
				$resArray[] = $row;
				$nextstart++;
			}			
		}
		
		return $resArray;
	}


	public function getLastBillNumber(){
		$q1 = $this->db->query("SELECT bill_num FROM bmc_bills ORDER BY bill_id DESC LIMIT 1");
	
		if ($q1->getNumRows() > 0){	
			return $q1->getResult()[0]->bill_num;
		}
		
		return "0";
	}	

	public function isValidNewBillNumber($new_id){
		$q1 = $this->db->query("SELECT bill_num FROM bmc_bills WHERE bill_num = '$new_id' LIMIT 1");
	
		if ($q1->getNumRows() > 0){	
			return FALSE;
		}
		
		return TRUE;
	}

	function isDuplicateTxn($receipt){
		$q1 = $this->db->query("SELECT tb1.trans_id FROM bmc_malipo tb1 
							  INNER JOIN bmc_malipo_details tb2 ON tb2.trans_id = tb1.trans_id
							  WHERE tb1.receipt = '$receipt' LIMIT 1");
	
		if ($q1->getNumRows() > 0){	
			return TRUE;
		}
		
		return FALSE;
	}
	
	function isDoublePay($reqDataArr){	
		$reference	= $reqDataArr["reference"];
		$amount		= $reqDataArr["amount"];
		
		$q1 = $this->db->query("SELECT tb1.amount , tb2.trans_date FROM bmc_malipo tb1 
							  INNER JOIN bmc_malipo_details tb2 ON tb2.trans_id = tb1.trans_id
							  WHERE tb1.reference = '$reference' ORDER BY tb1.trans_id DESC LIMIT 1");

		if ($q1->getNumRows() > 0){
			$resArray	= $q1->getResult()[0];
			
			$oldTxAmt	= $resArray->amount;
			$oldTxStamp	= $resArray->trans_date;
			$nowTxStamp	= strtotime(date("Y-m-d H:i:s"));
			$txTmDiff	= (($nowTxStamp-$oldTxStamp)/60);

			//Same amount within 2 minutes. Reject
			if( ($oldTxAmt == $amount) && ($txTmDiff < 2) ){
				return TRUE;
			}
		}
		
		return FALSE;
	}

	public function saveTransData($pat_id, $trans_sp , $xmlOrg , $reqDataArr){
		$now		= strtotime( date("Y-m-d H:i:s") );
		$ipAdress	= get_instance()->ipAddress;
		$date		= date("Y-m-d");
		$mwezi_id	= getMweziId($date);
		$saveArr	= array();

		//return TRUE;
		
		if( get_instance()->Engine->isDuplicateTxn($reqDataArr["receipt"]) ){
			return array("status" => TRUE, "msg" => "Duplicate"); //TXN Already Exists & Saved Return True To complete request
		}
		
		if( get_instance()->Engine->isDoublePay($reqDataArr) ){
			return array("status" => FALSE, "msg" => "Double Payment"); //TXN Has found similar trans. Prevent double pay
		}
		
		//Do save data
		$this->db->transStart();

		$dataMap = array("pat_id" => $pat_id, "mwezi_id" => $mwezi_id, "reference" => $reqDataArr["reference"], 
						 "receipt" => $reqDataArr["receipt"], "amount" => $reqDataArr["amount"], 
						 "msisdn" => $reqDataArr["msisdn"] , "pay_type" => $reqDataArr["payType"] );
		
		$builder = $this->db->table('bmc_malipo');
		$builder->insert($dataMap);
		$trans_id	= $this->db->insertID();
		
		$dataMap	= array("trans_id" => $trans_id , "trans_sp" => $trans_sp, "trans_ip" => $ipAdress, "fullXml" => $xmlOrg, "trans_date" => $now );
		$builder2	= $this->db->table('bmc_malipo_details');		
		$builder2->insert($dataMap);
		
		$this->db->transComplete();
		
		if ($this->db->transStatus() === false) {
			return array("status" => FALSE, "msg" => "Could not process Transaction");
		}
		
		return array("status" => TRUE, "msg" => "Success" , 
					 "data" => array("receipt" => $reqDataArr["receipt"] ) , "trans_id" => $trans_id );
	}

	public function getLastMasterEntry($pat_id , $entType){
		//Get last transaction of this patient
		$resArray 	= new \stdClass();
		$extend		= "";

		if( $entType != "99"){
			$extend	= " && (entry_type = '$entType') ";
		}
		
		$q1			= $this->db->query("SELECT * FROM bmc_wallet_master	
										WHERE (pat_id  = '$pat_id')  ".$extend."
										ORDER BY master_entry_id DESC LIMIT 1");

		if ($q1->getNumRows() > 0){
			$resArray	= $q1->getResult()[0];
		}

		return $resArray;
	}

	public function saveWalletTransaction($pat_id , $trans_id ,  $reqDataArr ){
		$amount		= $reqDataArr["amount"];
		$openBal	= 0;
		$siku		= date("Y-m-d H:i:s");
		$entType	= "1";

		//Check if transaction has been saved to master
		$q1	= $this->db->query("SELECT * FROM bmc_wallet_master	
								WHERE (pat_id  = '$pat_id') && (ext_reference  = '$trans_id') && (entry_type = '$entType') LIMIT 1");

		if ($q1->getNumRows() > 0){
			return;
		}

		//Get last transaction of this patient
		$row	= get_instance()->Engine->getLastMasterEntry($pat_id , "99");

		if ( isset( $row->close_bal ) ){
			$openBal	= $row->close_bal;
		}

		$closeBal	= ($openBal + $amount);

		$this->db->transStart();

		$builder = $this->db->table('bmc_malipo');
		$builder->where('trans_id', $trans_id);
		$builder->update(array("wallet_sync" => "1"));

		$dataMap = array("pat_id" => $pat_id,"entry_type" => $entType, "ext_reference" => $trans_id, "amount" => $amount, 
						 "start_bal" => $openBal, "close_bal" => $closeBal, "siku" => $siku);

		$builder = $this->db->table('bmc_wallet_master');
		$builder->insert($dataMap);

		$this->db->transComplete();
	}

	public function doGlobalMasterUpdate(){
		//Get last entry global master
		$globOpenBal	= 0;
		$siku			= date("Y-m-d H:i:s");

		$q1		= $this->db->query("SELECT * FROM bmc_wallet_master_global	ORDER BY wallet_master_id  DESC LIMIT 1");

		if ($q1->getNumRows() > 0){
			$row			= $q1->getResult()[0];
			$globOpenBal	= $row->close_bal;
		}

		echo 'Global Master :: '.$globOpenBal;

		//Get non synched payments
		$q1	= $this->db->query("SELECT * FROM bmc_wallet_master WHERE is_synch = '0' ORDER BY master_entry_id ASC LIMIT 10");

		$this->db->transStart();

		echo 'Not Synched :: '. $q1->getNumRows();

		if ($q1->getNumRows() > 0){
			foreach($q1->getResult() AS $row){
				$entryId	= $row->master_entry_id;
				$amount		= $row->amount;
				$entType	= $row->entry_type;

				$globCloseBal	= ($globOpenBal + $amount);

				if($entType == "0"){
					$globCloseBal	= ($globOpenBal - $amount);
				}

				$builder = $this->db->table('bmc_wallet_master');
				$builder->where('master_entry_id', $entryId);
				$builder->update(array("is_synch" => "1"));

				$dataMap = array("master_entry_id " => $entryId, "entry_type" => $entType, "amount" => $amount, "start_bal" => $globOpenBal, 
								 "close_bal" => $globCloseBal, "siku" => $siku);

				$builder = $this->db->table('bmc_wallet_master_global');
				$builder->insert($dataMap);

				$globOpenBal	= $globCloseBal;
			}
		}

		$this->db->transComplete();
	}

	public function getGlobalMasterStatement($start , $type , $pat_id , $limit  ){
		$extend		= "";

		if($pat_id > 0){
			$extend = " WHERE tb2.pat_id = '$pat_id' ";
		}
		
		$resArray 	= array();//new \stdClass();
		$q1			= $this->db->query("SELECT tb1.wallet_master_id , tb1.master_entry_id , tb1.entry_type , tb1.amount , tb1.start_bal ,
											   tb1.close_bal , tb1.siku , tb2.pat_id , tb2.start_bal AS mstart_bal , tb2.close_bal AS mclose_bal,
											   tb3.reference , tb4.control_num , tb5.other_names  , tb5.sur_name 
										FROM bmc_wallet_master_global tb1	
										INNER JOIN bmc_wallet_master tb2 ON tb2.master_entry_id  = tb1.master_entry_id 
										LEFT JOIN bmc_malipo tb3 on tb3.trans_id = tb2.ext_reference 
										LEFT JOIN bmc_bills tb4 ON tb4.bill_id = tb2.ext_reference
										LEFT JOIN bmc_patient_profile tb5 ON tb5.pat_id = tb2.pat_id ".$extend."
										ORDER BY tb1.wallet_master_id  DESC LIMIT $start , $limit ");

		if ($q1->getNumRows() > 0){
			foreach($q1->getResult() AS $row){
				$resArray[] = $row;
			}
		}

		return $resArray;
	}

	public function getFullBillDetails( $pat_id  , $bill_id ){
		$resArray 	= new \stdClass();
		$q1			= $this->db->query("SELECT * FROM bmc_bills tb1
										INNER JOIN bmc_patient_profile tb2 ON tb2.pat_id = tb1.pat_id
										INNER JOIN bmc_bill_payment_terms tb3 ON tb3.terms_id = tb1.bill_terms
										WHERE tb1.pat_id = '$pat_id' 
										ORDER BY tb1.bill_id DESC LIMIT 1");

		if ($q1->getNumRows() > 0){
			$resArray = $q1->getResult()[0];
		}

		return $resArray;
	}

	public function getFullBillDetailsByBillId( $bill_id ){
		$resArray 	= new \stdClass();
		$q1			= $this->db->query("SELECT * FROM bmc_bills tb1
										INNER JOIN bmc_patient_profile tb2 ON tb2.pat_id = tb1.pat_id
										INNER JOIN bmc_bill_payment_terms tb3 ON tb3.terms_id = tb1.bill_terms
										LEFT JOIN bmc_bill_cleared tb4 ON tb4.bill_id = tb1.bill_id
										WHERE tb1.bill_id = '$bill_id' 
										ORDER BY tb1.bill_id DESC LIMIT 1");

		if ($q1->getNumRows() > 0){
			$resArray = $q1->getResult()[0];
		}

		return $resArray;
	}

	public function getAllBillItems($bill_id){
		$resArray 	= array();
		$q1			= $this->db->query("SELECT * FROM bmc_bills_items tb1
										INNER JOIN bmc_bills tb2 ON tb2.bill_id = tb1.bill_id
										INNER JOIN bmc_bill_product tb3 ON tb3.prod_id = tb1.item_id
										WHERE tb1.bill_id = '$bill_id' ORDER BY tb1.bill_item_id ASC ");

		if ($q1->getNumRows() > 0){
			foreach ($q1->getResult() as $row){
				$resArray[] = $row;
			}			
		}

		return $resArray;
	}

	public function prosPatientUnpaidBills($pat_id , $globBillId){
		$patArray		= get_instance()->Engine->fetchPatientRecord($pat_id);
		
		if( isset($patArray->pat_id) ){
			$lastId			= 0;
			$balrecArray	= get_instance()->Engine->getPatientBalRecord($pat_id);
			
			if( isset($balrecArray->trans_id) ){
				if( !checkBalValidity($balrecArray) || ($balrecArray->wallet_key != $balrecArray->bal_hash) ){
					return FALSE; //Balance not valid exit patient. Corrupt record. Enter into logs
				}

			}
			
			return get_instance()->Engine->updatePayPatBills($pat_id, $patArray, $balrecArray , $globBillId);
			
		}
		else{
			return FALSE;//Invalid Patient
		}		
	}

	public function updatePayBillUsingWallet($pat_id, $globBillId){
		//Check bill to see amount owed
		$patBalance = 0;
		$tmStamp	= strtotime( date("Y-m-d H:i:s") );
		$siku		= date("Y-m-d H:i:s");
		
		$billArray	= $this->fetchPatUnpaidBills($pat_id , $globBillId);
		$row		= get_instance()->Engine->getLastMasterEntry($pat_id , $entType = "99");
		
		if ( isset( $row->close_bal ) ){
			$patBalance	= $row->close_bal;
		}
		
		if( ($patBalance > 0 ) && (sizeof($billArray) > 0) ){
			//Do save data

			$billArr	= $billArray[0];
			$bill_id	= $billArr->bill_id;
			$totalCost	= (int)$billArr->bill_total_cost;
			$totalPaid	= (int)$billArr->bill_total_paid;

			$billBal	= ($totalCost - $totalPaid);

			if( $patBalance >= $billBal){
				$openBal	= $patBalance;
				$patBalance = ($patBalance - $billBal);
				$billClose	= "1";
				$balHash	= returnMyHash( $patBalance , $tmStamp );
				$someKey	= "W".$tmStamp;
				
				$this->db->transStart();
				
				//Update patient balance after each bill payment
				$dataMap	= array("bill_total_paid" => $billBal, "bill_receipt" => $someKey);
				
				$builder = $this->db->table('bmc_bills');
				$builder->where('bill_id', $bill_id);
				$builder->update($dataMap);
				
				$this->db->query("DELETE FROM bmc_bill_cleared WHERE bill_id = '$bill_id' ");

				//Save to synch database
				$futureTimestamp = time() + 5; 

				$dataMap = array("bill_id" => $bill_id , "cleared_date" => $siku , "synch_queue" => $futureTimestamp);
				$builder = $this->db->table('bmc_bill_cleared');
				$builder->insert($dataMap);
				
				//log into wallet master
				$dataMap	= array("pat_id" => $pat_id,"entry_type" => "0", "ext_reference" => $bill_id, "amount" => $billBal, 
									"start_bal" => $openBal, "close_bal" => $patBalance, "siku" => $siku, "some_key" => $someKey);
				
				$builder = $this->db->table('bmc_wallet_master');
				$builder->insert($dataMap);
				
				$this->db->transComplete();

				if ($this->db->transStatus() === false) {
					return false;
				}
			}
			
		}
		
		return true;
	}

	public function updatePayPatBills($pat_id, $patArray, $balrecArray , $globBillId){
		$patBalance	= 0;
		$balType	= "0";
		$siku		= date("Y-m-d H:i:s");
		$tmStamp	= strtotime( date("Y-m-d H:i:s") );
		$pat_num	= $patArray->pat_id;
		$trans_id	= 0;
		
		$billArray	= get_instance()->Engine->fetchPatUnpaidBills($pat_id , $globBillId);
		
		if( (isset($balrecArray->wallet_bal) ) ){
			$patBalance = (int)$balrecArray->bal_amt;
			$trans_id	= (int)$balrecArray->trans_id;
			$balType	= "1";
		}
		
		if( ($patBalance > 0 ) && (sizeof($billArray) > 0) ){
			//Do save data
			$this->db->transStart();

			foreach($billArray AS $billArr){
				$billClose	= "3";
				$bill_id	= $billArr->bill_id;
				$totalCost	= (int)$billArr->bill_total_cost;
				$totalPaid	= (int)$billArr->bill_total_paid;

				$billBal	= ($totalCost - $totalPaid);
				$item_amt	= $billBal;

				if( $patBalance >= $billBal){
					$patBalance = ($patBalance - $billBal);
					$billBal	= 0;
					$billClose	= "1";
				}
				else{
					$billBal	= ($billBal - $patBalance);
					$item_amt	= $patBalance;
					$patBalance	= 0;
				}

				if( ($billClose == "1") || ($billBal == 0) ){
					$q99	= $this->db->query("SELECT bill_id FROM bmc_bill_cleared 
												WHERE bill_id = '$bill_id' LIMIT 1");

					if ($q99->getNumRows() > 0){
						$this->db->query("DELETE FROM bmc_bill_cleared WHERE bill_id = '$bill_id' ");
					}

					//Save to synch database
					$dataMap = array("bill_id" => $bill_id , "cleared_date" => $siku );
					$builder = $this->db->table('bmc_bill_cleared');
					$builder->insert($dataMap);
				}

				$balHash	= returnMyHash( $patBalance , $tmStamp );
				$totAmtPaid	= ($totalCost-$billBal);

				//Update patient balance after each bill payment
				$dataMap	= array("bill_total_paid" => $totAmtPaid,"is_closed" => $billClose, 
									"bill_receipt" => $balHash);

				$builder = $this->db->table('bmc_bills');
				$builder->where('bill_id', $bill_id);
				$builder->update($dataMap);
				
				$dataMap	= array("pat_id" => $pat_id, "item_amt" => $item_amt , "bal_amt" => $patBalance, 
									"bill_id" => $bill_id, "post_type" => "2", "bal_timestamp" => $tmStamp, 
									"bal_hash" => $balHash, "bal_type" => $balType , "siku" => $siku , 
									"trans_id" => $trans_id);

				$builder = $this->db->table('bmc_profile_bal_log');
				$builder->insert($dataMap);

				$dataMap	= array("wallet_bal" => $patBalance,"wallet_key" => $balHash ,"last_updated" => $siku);

				$builder = $this->db->table('bmc_patient_wallet');
				$builder->where('pat_id', $pat_id);
				$builder->update($dataMap);


				if($patBalance < 1){
					break; //Break from foreach loop
				}

			}

			$this->db->transComplete();
	
			if ($this->db->transStatus() === false) {
				return false;
			}			
		}
		
		return true;
	}

	public function fetchPatUnpaidBills($pat_id , $globBillId){
		$resArray	= array();
		$extSql		= "";

		if($globBillId > 0){
			$extSql = "&& tb1.bill_id = '$globBillId' LIMIT 1";
		}
		else{
			$extSql = "&& (tb1.pat_id = '$pat_id') ORDER BY tb1.bill_id DESC LIMIT 10";
		}
		
		$q1			= $this->db->query( "SELECT * FROM bmc_bills tb1
										INNER JOIN bmc_patient_profile tb2 ON tb2.pat_id = tb1.pat_id
										WHERE (tb1.is_closed != '1') && (is_hidden = '0') ".$extSql );

		if ($q1->getNumRows() > 0){
			foreach ($q1->getResult() as $row){
				$resArray[] = $row;
			}
		}

		return $resArray;
	}

	public function calcPatientBalance($pat_id){
		$patArray		= get_instance()->Engine->fetchPatientRecord($pat_id);
		
		if( isset($patArray->pat_id) ){
			$lastId			= 0;
			$balrecArray	= get_instance()->Engine->getPatientBalRecord($pat_id);
			
			if( isset($balrecArray->trans_id) ){
				if( !checkBalValidity($balrecArray) || ($balrecArray->wallet_key != $balrecArray->bal_hash) ){
					return FALSE; //Balance not valid exit patient. Corrupt record. Enter into logs
				}
			}
			
			return get_instance()->Engine->updatePatLatestMalipo($pat_id, $patArray, $balrecArray);
			
		}
		else{
			return FALSE;//Invalid Patient
		}
	}

	public function getGlobalBillId($reference){
		$globBillId	= 0;
		$q1 		= $this->db->query("SELECT bill_id FROM bmc_bills tb1 
										WHERE tb1.bill_num = '$reference' LIMIT 1");

		if ($q1->getNumRows() > 0){
			$globBillId	= $q1->getResult()[0]->bill_id;
		}

		return $globBillId;
	}

	public function getPatientBalRecord($pat_id){
		$resArray 	= new \stdClass();

		$resArray->bal_hash			= "";
		$resArray->wallet_key		= "";
		$resArray->wallet_bal		= "";
		$resArray->log_id			= "";
		$resArray->bal_amt			= "";
		$resArray->trans_id			= "";
		$resArray->post_type		= "";
		$resArray->bal_timestamp	= "";
		$resArray->bal_type			= "";
		$resArray->siku				= "";

		$q1 = $this->db->query("SELECT * FROM bmc_patient_wallet WHERE pat_id = '$pat_id' LIMIT 1");

		if ($q1->getNumRows() > 0){
			$resArray	= $q1->getResult()[0];
		}

		$q2 = $this->db->query("SELECT * FROM bmc_profile_bal_log 
								WHERE (pat_id = '$pat_id') && (trans_id > 0) && log_id > 134
							    ORDER BY log_id DESC LIMIT 1");
		
		if ($q2->getNumRows() > 0){
			$res2	= $q2->getResult()[0];

			$resArray->log_id			= $res2->log_id;
			$resArray->bal_amt			= $res2->bal_amt;
			$resArray->trans_id			= $res2->trans_id;
			$resArray->post_type		= $res2->post_type;
			$resArray->bal_timestamp	= $res2->bal_timestamp;
			$resArray->bal_hash			= $res2->bal_hash;
			$resArray->bal_type			= $res2->bal_type;
			$resArray->siku				= $res2->siku;
		}

		if( !isset($resArray->bal_hash) || ($resArray->bal_hash == NULL) || ($resArray->wallet_key != $resArray->bal_hash)){
			$resArray	= get_instance()->Engine->getValidBalLog($resArray);
		}

		return $resArray;
	}

	public function getValidBalLog($resArray){
		$pat_id		= $resArray->pat_id;
		$wallet_key	= $resArray->wallet_key;

		$q1 = $this->db->query("SELECT * FROM bmc_profile_bal_log 
								WHERE (pat_id = '$pat_id') && bal_hash = '$wallet_key'
								ORDER BY log_id DESC LIMIT 1");
		
		if ($q1->getNumRows() > 0){			
			$row	= $q1->getResult()[0];
			
			$resArray->bal_hash		 = $row->bal_hash;
			$resArray->bal_amt		 = $row->bal_amt;
			$resArray->trans_id  	 = $row->trans_id;
			$resArray->bal_timestamp = $row->bal_timestamp;
			$resArray->bal_type 	 = $row->bal_type;
			$resArray->siku 		 = $row->siku;
			
		}
		else{
			$resArray->bal_hash		 = returnMyHash(0 , 0);
			$resArray->wallet_key	 = returnMyHash(0 , 0);
			$resArray->bal_amt		 = 0;
			$resArray->bal_timestamp = 0;
			$resArray->trans_id  	 = 0;
		}
		
		return $resArray;
	}

	public function fetchPatLatestMalipo($pat_num , $trans_id ){
		$resArray	= array();
		$subQuery	= "";
		$subQuery	= " && tb1.trans_id NOT IN (SELECT tbb.trans_id FROM bmc_profile_bal_log tbb WHERE tbb.trans_id='$trans_id')";
		
		$q1 = $this->db->query("SELECT * FROM bmc_malipo tb1 
							  INNER JOIN bmc_malipo_details tb2 ON tb2.trans_id = tb1.trans_id
							  WHERE ((tb1.pat_id = '$pat_num') || (tb1.reference = '$pat_num') ) && 
							  		 (tb1.trans_id > 134 ) && (tb1.wallet_sync = '0') ".$subQuery."
									 ORDER BY tb1.trans_id ASC LIMIT 1");

		if ($q1->getNumRows() > 0){
			foreach ($q1->getResult() as $row){
				$resArray[] = $row;
			}
		}

		return $resArray;
	}

	public function updatePatLatestMalipo($pat_id, $patArray, $balrecArray){
		
		$patBalance	= 0;
		$balType	= "0";
		$siku		= date("Y-m-d H:i:s");
		$tmStamp	= strtotime( date("Y-m-d H:i:s") );
		$pat_num	= $patArray->pat_num;
		$trans_id	= $balrecArray->trans_id;
		$payArray	= get_instance()->Engine->fetchPatLatestMalipo($pat_id , $trans_id );

		if( (isset($balrecArray->trans_id) && $balrecArray->trans_id > 0) ){
			$patBalance = (int)$balrecArray->bal_amt;
			$balType	= "1";
		}

		if( sizeof( $payArray  ) == 0){
			return true; //Dont update balance if we have no new transaction
		}

		//Do save data
		$this->db->transStart();

		foreach($payArray AS $payArr){
			$trans_id	= $payArr->trans_id;
			$amount		= (int)$payArr->amount;

			$patBalance = ($patBalance+$amount);

			$builder = $this->db->table('bmc_malipo');
			$builder->where('trans_id', $trans_id);
			$builder->update(array("wallet_sync" => "1"));
		}

		//IF PAYMENT IS WALLET LOG TO WALLET MASTER

		//ESLE LOG TO BALANCE LOG

		$balHash	= returnMyHash( $patBalance , $tmStamp );

		$dataMap	= array("pat_id" => $pat_id, "item_amt" => $amount, "bal_amt" => $patBalance, 
							"trans_id" => $trans_id, "post_type" => "1", "bal_timestamp" => $tmStamp, 
							"bal_hash" => $balHash, "bal_type" => $balType , "siku" => $siku);
							
		$builder = $this->db->table('bmc_profile_bal_log');
		$builder->insert($dataMap);
		
		$dataMap	= array("wallet_bal" => $patBalance,"wallet_key" => $balHash ,"last_updated" => $siku);
		
		$builder = $this->db->table('bmc_patient_wallet');
		$builder->where('pat_id', $pat_id);
		$builder->update($dataMap);
		
		$this->db->transComplete();

		if ($this->db->transStatus() === false) {
			return false;
		}
		
		return true;
	}

	public function retPatIdFromPatref($pat_ref){
		$q1 = $this->db->query("SELECT tb1.pat_id FROM bmc_patient_profile tb1 
								LEFT JOIN bmc_bills tb2 ON tb2.pat_id = tb1.pat_id 
								WHERE (tb1.pat_num = '$pat_ref') ||(tb2.bill_num = '$pat_ref') 
								LIMIT 1");
	
		if ($q1->getNumRows() > 0){
			return $q1->getResult()[0]->pat_id;
		}
		
		return 0;
	}

	public function getMalipoSummary($startDate, $stopDate, $wallet){
		$walletReportArr	= array("walletTotals" => array() , "walletGrandTotals" => "0" , 
									"walletControlTotals" => "0" , "walletDepositTotals" => "0" , 
									"walletTxnTotals" => "0" , "detArr" => array() );

		$trans_date		= strtotime(date('Y-m-d'));
		$extLimit 		= 2000;

		if($startDate == 0 || $stopDate == 0){
			$startDate		= strtotime(date('Y-m-01 00:00:00', $trans_date));
			//$stopDate		= strtotime(date('Y-m-t 11:59:59', $trans_date));
			$stopDate		= strtotime(date('Y-m-t 23:59:59', $trans_date));
		}

		$walletReportArr["startDate"]		= $startDate;
		$walletReportArr["stopDate"]		= $stopDate;

		$walletReportArr["payLoadMore"]		= "";
		$walletReportArr["morePayData"]		= "";
		$walletReportArr["finishedPayData"]	= "";
		$walletReportArr["detArr"] 			= array();

		$dataArr = get_instance()->Engine->getReportDetails($startDate , $stopDate , $wallet ,$limit = 0);

		if (sizeof($dataArr) > 0){
			foreach ($dataArr as $row){
				//$resArray[] = $row;
				$amount			= $row->amount;
				$trans_sp		= $row->trans_sp;
				$pat_id			= $row->pat_id;
				$wallet_name	= $row->wallet_name;

				if(sizeof($walletReportArr["detArr"]) < $extLimit){
					$walletReportArr["detArr"][] = $row;
				}
				
				if( isset($walletReportArr["walletPatArray"][$trans_sp]["patCountArr"]) ){
					$patCountArray	= $walletReportArr["walletPatArray"][$trans_sp]["patCountArr"];

					if (!in_array($pat_id, $patCountArray)){
						$patCountArray[] = $pat_id;
					}

					$walletReportArr["walletPatArray"][$trans_sp]["patCountArr"] = $patCountArray;
				}
				else{
					$walletReportArr["walletPatArray"][$trans_sp]["patCountArr"] = array($pat_id);
				}
				
				$newPatCount = sizeof($walletReportArr["walletPatArray"][$trans_sp]["patCountArr"]); 

				if(isset($walletReportArr["walletTotals"][$trans_sp]["total"])){
					$newTotal	= ($amount+$walletReportArr["walletTotals"][$trans_sp]["total"]);
					$newTxCount	= (1+$walletReportArr["walletTotals"][$trans_sp]["txnCount"]);

					if($row->pay_type == "1"){
						$newWalletTotal = ($amount+$walletReportArr["walletTotals"][$trans_sp]["walletTotal"]);
					}
					else{
						$newControlTotal = ($amount+$walletReportArr["walletTotals"][$trans_sp]["controlTotal"]);
					}

					$row->pay_type == "1" ? $newWalletTotal = $amount + $walletReportArr["walletTotals"][$trans_sp]["walletTotal"] : $newControlTotal = $amount + $walletReportArr["walletTotals"][$trans_sp]["controlTotal"];
					$row->pay_type == "1" 
					? $newWalletTotal = $amount + $walletReportArr["walletTotals"][$trans_sp]["walletTotal"] 
					: $newControlTotal = $amount + $walletReportArr["walletTotals"][$trans_sp]["controlTotal"];
				
				}
				else{
					$newTotal			= $amount;
					$newWalletTotal		= 0;
					$newControlTotal	= 0;
					$newTxCount			= 1;

					$row->pay_type == "1" ? $newWalletTotal = $amount : $newControlTotal = $amount;

					$walletReportArr["walletTotals"][$trans_sp]["name"]		= $wallet_name;
					$walletReportArr["walletTotals"][$trans_sp]["id"]		= $trans_sp;
				}

				if(!isset( $walletReportArr["walletGrandTotals"] ) ){
					$walletReportArr["walletGrandTotals"]		= 0;
					$walletReportArr["walletTxnTotals"]			= 0;
					$walletReportArr["walletControlTotals"]		= 0;
					$walletReportArr["walletDepositTotals"]		= 0;
				}

				$walletReportArr["walletTotals"][$trans_sp]["total"]		= $newTotal;
				$walletReportArr["walletTotals"][$trans_sp]["walletTotal"]	= $newWalletTotal;
				$walletReportArr["walletTotals"][$trans_sp]["controlTotal"]	= $newControlTotal;
				$walletReportArr["walletTotals"][$trans_sp]["txnCount"]		= $newTxCount;
				$walletReportArr["walletTotals"][$trans_sp]["patCount"] 	= $newPatCount;

				$walletReportArr["walletGrandTotals"]	= ($amount+$walletReportArr["walletGrandTotals"]);
				$walletReportArr["walletTxnTotals"]		= (1+$walletReportArr["walletTxnTotals"]);

				if($row->pay_type == "1"){
					$walletReportArr["walletDepositTotals"] = ($amount+$walletReportArr["walletDepositTotals"]);
				}
				else{
					$walletReportArr["walletControlTotals"] = ($amount+$walletReportArr["walletControlTotals"]);
				}			
				
			}

			$ct	= sizeof($dataArr);

			if($ct >= $extLimit && $ct%$extLimit == 0){
				$walletReportArr["payLoadMore"]	= "1";
			}
			else{
				$walletReportArr["payLoadMore"]	= "1";
			}
		}

		return $walletReportArr;
	}

	public function getReportDetails($startDate , $stopDate , $wallet , $limit ){
		$resArray		= array();
		$extendQuery	= "";
		$extendQuery2	= "";

		if( $wallet > 0 && $wallet < 99){
			$extendQuery	= " && tb2.trans_sp ='$wallet'";
		}

		if($limit > 0){
			$extendQuery2	= " LIMIT $limit";
		}

		$q1 = $this->db->query("SELECT tb1.* , tb2.trans_sp , tb2.trans_date, tb1.msisdn, tb3.wallet_name
								FROM bmc_malipo tb1 
								INNER JOIN bmc_malipo_details tb2 ON tb2.trans_id = tb1.trans_id
								INNER JOIN bmc_malipo_wallets tb3 ON tb3.wallet_id = tb2.trans_sp
								WHERE (tb2.trans_date >= '$startDate') && (tb2.trans_date <= '$stopDate') 
								      ".$extendQuery." ORDER BY tb1.trans_id DESC ".$extendQuery2."");

		if ($q1->getNumRows() > 0){
			foreach ($q1->getResult() as $row){
				$resArray[] = $row;
			}
		}

		return $resArray;
	}

	public function getAllUserTy($pat_num , $trans_id ){

	}

	public function searchRefundBils($custid){
		$resArray	= array();

		if(strlen($custid) < 4){
			return $resArray;
		}
		
		$q1 		= $this->db->query("SELECT tb1.* , tb2.* FROM bmc_patient_profile tb1 
										INNER JOIN bmc_bills tb2 ON tb2.pat_id = tb1.pat_id 
										WHERE ((tb1.pat_num LIKE '$custid%') ||(tb1.pat_id = '$custid') || 
												(tb2.bill_num = '$custid')) && (tb2.bill_total_paid != '0') &&
												(tb2.is_refund = '0' )  
										ORDER BY tb2.bill_id DESC LIMIT 10");

		if ($q1->getNumRows() > 0){
			foreach ($q1->getResult() as $row){
				$resArray[]	= $row;
			}
		}
		
		return $resArray;
	}
	
	public function getBillsNotSynched(){
		$q1	= $this->db->query("SELECT * FROM bmc_bill_cleared tb1
								INNER JOIN bmc_bills tb2 ON tb2.bill_id = tb1.bill_id
								WHERE tb1.sync_status = '2'
								ORDER BY tb1.bill_id DESC LIMIT 10");
		
		if ($q1->getNumRows() > 0){
			//Update to synch
			
			foreach ( $q1->getResult() AS $row){
				$bill_id = $row->bill_id;				
				$this->db->query("UPDATE bmc_bill_cleared SET sync_status = '0'  WHERE bill_id = '$bill_id' ");
			}			
		}		
	}	

	public function getBillToSynch(){
		$billArray 		= new \stdClass();
		$payArray  		= array();
		$currTmstamp	= time();

		$q1	= $this->db->query("SELECT * FROM bmc_bill_cleared tb1
								INNER JOIN bmc_bills tb2 ON tb2.bill_id = tb1.bill_id
								WHERE tb1.sync_status = '0' && synch_queue < '$currTmstamp' ORDER BY tb1.bill_id DESC LIMIT 1");

		if ($q1->getNumRows() > 0){
			$billArray		= $q1->getResult()[0];
			$bill_id		= $billArray->bill_id;
			$bill_num		= $billArray->bill_num;
			$control_num	= $billArray->control_num;

			//Check if paid by Wallet Balance
			$q1x	= $this->db->query("SELECT * FROM bmc_wallet_master WHERE entry_type  = '0' && ext_reference = '$bill_id' LIMIT 1");

			if ($q1x->getNumRows() > 0){
				$row		= $q1x->getResult()[0];
				$payArray[]	= array("channelID" => "WALLET", "amount" => $row->amount, "externalReceipt" => $row->some_key );
			}
			else{
				//Get payments to bill
				$q2	= $this->db->query("SELECT * FROM bmc_malipo tb1 
										INNER JOIN bmc_malipo_details tb2 ON tb2.trans_id = tb1.trans_id
										INNER JOIN bmc_malipo_wallets tb3 ON tb3.wallet_id = tb2.trans_sp
										WHERE (tb1.reference = '$bill_num') || (tb1.reference = '$control_num') LIMIT 1");

				if ($q2->getNumRows() > 0){
					$row		= $q2->getResult()[0];
					$payArray[]	= array("channelID" => $row->wallet_alias, "amount" => $row->amount, "externalReceipt" => $row->receipt );
				}
			}

			$billArray->payArray	= $payArray;
		}

		return $billArray;
	}

	public function getDaysBillSynch(){
		$billArray = new \stdClass();;

		$q1	= $this->db->query("SELECT * FROM bmc_bill_cleared tb1
								INNER JOIN bmc_bills tb2 ON tb2.bill_id = tb1.bill_id
								INNER JOIN bmc_patient_profile tb3 ON tb3.pat_id = tb2.pat_id
								WHERE tb1.sync_status = '1' ORDER BY tb1.bill_id DESC LIMIT 1000");

		if ($q1->getNumRows() > 0){
			foreach ( $q1->getResult() AS $row){
				$billArray		= array("BillNumber" => $row->control_num , 
										"PatientName" => $row->sur_name." ".$row->other_names, 
										"PatientPhone" => $row->phone, 
										"AmountPaid" => $row->bill_total_paid , 
										"BillTotal" => $row->bill_total_cost, 
										"PayStatus" => $row->is_closed, 
										"PaymentReceipt" => $row->bill_receipt );
			}
		}

		return $billArray;
	}	

	public function doApiPaymentBillUpdate($bill_id , $status, $statusMsg, $respDate , $globLogId){
		$dataMap	= array("sync_status" => $status, 
							"status_msg" => $statusMsg,
							"global_log_id" => $globLogId);

		if(strlen($respDate) > 0){
			$dataMap["sync_date"] =  $respDate;
		}

		$builder = $this->db->table('bmc_bill_cleared');		
		$builder->where('bill_id', $bill_id);
		$builder->update($dataMap);
	}

	public function saveAuditActivityLogs($dataMap){
		$dataMap["log_siku"]	= date("Y-m-d H:i:s");

		$this->db->transStart();
		$builder = $this->db->table('bmc_audit_activity_logs');		
		$builder->insert($dataMap);

		$this->db->transComplete();

		if ($this->db->transStatus() === false) {
			return false;
		}
		
		return true;
	}
}

