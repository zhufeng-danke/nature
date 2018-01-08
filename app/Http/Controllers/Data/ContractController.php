<?php

namespace App\Http\Controllers\Data;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;
use Excel;

class ContractController extends BaseController
{

    const EXPORT_LIMIT_ONR_TIME = 10000;

    /**************************************************************** 收房合同 ****************************************************************/
    public function landlord(Request $request)
    {
        $requestData = $request->all();

        $start_date = isset($requestData['start_date']) && $requestData['start_date'] != '' ? $requestData['start_date'] : '';
        $end_date = isset($requestData['end_date']) && $requestData['end_date'] != '' ? $requestData['end_date'] : '';
        $suit_id = isset($requestData['suit_id']) && $requestData['suit_id'] > 0 ? $requestData['suit_id'] : '';
        $count = 0;

        $data = [];
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        $data['suit_id'] = $suit_id;
        $data['count'] = $count;

        if ($request->method() == 'POST') {
            if (!isset($requestData['start_date']) || !isset($requestData['end_date']) || $requestData['start_date'] == '' || $requestData['end_date'] == '' || $requestData['start_date'] > $requestData['end_date']) {
                flash('请输入合法数据.')->error();
                return back();
            }

            //获取所有有效合同
            $valuableContracts = $this->valuableLandlordContracts($start_date, $end_date, $suit_id);
            if (!count($valuableContracts)) {
                flash('查询结果为空')->warning();
                return view('data.landlord', $data);
            }

            $dataArray = [];

            //查询前序合同
            foreach ($valuableContracts as $key => $valuableContract) {
                $pre = $this->preLandlordContractInfo($valuableContract->id);
                $valuableContracts[$key]->pre_contract_info = is_array($pre) ? implode(',', $pre) : '';
                $dataArray[] = $this->objectToArray($valuableContracts[$key]);
            }

            $excel_title = 'contract_with_landlord_' . $start_date . '~' . $end_date;
            $this->exportFile($excel_title, $dataArray);
        }

        return view('data.landlord', $data);
    }


    //预处理收房前序合同
    public function preLandlordContractInfo($contract_id)
    {
        $pre_contract = [];
        $pre_contract_info = [];
        $next_id = $contract_id;
        do {
            $contract = $this->landlordPreContracts($next_id);
            if ($contract) {
                $next_id = $contract->id;
                $pre_contract[] = $contract;
                $pre_contract_info[] = $contract->id . '-' . ($contract->next_id ? $contract->next_id : '无后序') . '-' . $contract->number.'-'.$contract->stage;
            }
        } while ($contract);

        if (count($pre_contract)) {
            return $pre_contract_info;
        }

        return '';
    }

    //前序合同
    public function landlordPreContracts($contract_id)
    {
        $sql = "
SELECT
cwl.id,
cwl.next_id,
cwl.number,
cwl.stage
FROM contract_with_landlords cwl
WHERE cwl.stage = '未执行' AND cwl.next_id = ?";
        $pre_contract = DB::select($sql, [$contract_id]);
        if (count($pre_contract)) {
            return $pre_contract[0];
        }

        return false;
    }

    //有效收房合同
    public function valuableLandlordContracts($start, $end, $suit_id = '')
    {
        $sql = "
SELECT
cwl.id,
cwl.next_id,
cwl.suite_id,
'' as pre_contract_info,
cwl.number,
cwl.start_date,
cwl.end_date,
cwl.status,
cwl.stage,
cwl.terminate_date,
cwl.terminate_type,
IF(cwl.terminate_date IS NOT NULL, cwl.terminate_date, cwl.end_date) as 'actual_end_date'
FROM contract_with_landlords cwl
WHERE
IF(
cwl.terminate_date IS NULL, 
(cwl.start_date <= '" . $start . "' AND cwl.end_date >= '" . $start . "') OR (cwl.start_date >= '" . $start . "' AND cwl.start_date <= '" . $end . "'),
(cwl.start_date <= '" . $start . "' AND cwl.terminate_date >= '" . $start . "') OR (cwl.start_date >= '" . $start . "' AND cwl.start_date <= '" . $end . "')
)
AND 
IF(
cwl.terminate_date IS NULL,
1,
cwl.terminate_date >= cwl.start_date
)
AND cwl.stage IN ('执行中', '执行结束')     
        ";

        if ($suit_id != '' && $suit_id > 0) {
            $sql .= "AND cwl.suite_id = " . trim($suit_id);
        }

        return DB::select($sql);
    }

    /**************************************************************** 出房合同 ****************************************************************/

    public function customer(Request $request)
    {
        ini_set('memory_limit', '1024M');
        $requestData = $request->all();

        $start_date = isset($requestData['start_date']) && $requestData['start_date'] != '' ? $requestData['start_date'] : '';
        $end_date = isset($requestData['end_date']) && $requestData['end_date'] != '' ? $requestData['end_date'] : '';
        $suit_id = isset($requestData['suit_id']) && $requestData['suit_id'] > 0 ? $requestData['suit_id'] : '';
        $count = 0;

        $data = [];
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        $data['suit_id'] = $suit_id;
        $data['count'] = $count;

        if ($request->method() == 'POST') {
            if (!isset($requestData['start_date']) || !isset($requestData['end_date']) || $requestData['start_date'] == '' || $requestData['end_date'] == '' || $requestData['start_date'] > $requestData['end_date']) {
                flash('请输入合法数据.')->error();
                return back();
            }

            $contracts = $this->valuableCustomerContracts($start_date, $end_date, $suit_id);
            $count = count($contracts);
            if (!$count) {
                flash('查询结果为空')->warning();
                return view('data.customer', $data);
            }

            foreach ($contracts as $key => $contract) {
                //前序合同
                $pre = $this->preCustomerContractInfo($contract->previous_id);
                $contracts[$key]->pre_contract_info = count($pre) ? implode(',', $pre) : '';
            }
            $title = 'contract_with_customer_' . $start_date . '~' . $end_date;
            $this->exportFile($title, $contracts, true);
        }

        return view('data.customer', $data);
    }

    // 预处理出房合同
    public function preCustomerContractInfo($previous_id)
    {
        if (!is_numeric($previous_id) || intval($previous_id) < 0) {
            return [];
        }

        $pre = [];
        $next_id = $previous_id;
        do {
            $contract = $this->customerPreConstract($next_id);
            if ($contract) {
                $next_id = $contract->previous_id;
                $pre[] = $contract->id . '-' . ($contract->previous_id ? $contract->previous_id : '无前序') . '-' . $contract->number . '-' . $contract->stage;
            }
        } while ($contract);

        return $pre;
    }

    //查找前序合同
    public function customerPreConstract($previous_id)
    {
        $sql = "
SELECT
cwc.id,
cwc.previous_id,
cwc.number,
cwc.stage
FROM contract_with_customers cwc
WHERE cwc.id = ?
AND cwc.status != '期满'
        ";

        $contract = DB::select($sql, [$previous_id]);
        if (count($contract)) {
            return $contract[0];
        }

        return false;
    }

    //有效出房合同
    public function valuableCustomerContracts($start, $end, $suit_id = '')
    {
        $sql = "
SELECT
cwc.id,
cwc.previous_id,
r.suite_id,
'' as pre_contract_info,
cwc.number,
cwc.start_date,
cwc.end_date,
cwc.status,
cwc.stage,
cwc.terminate_date,
cwc.terminate_type,
IF(cwc.terminate_date IS NOT NULL, cwc.terminate_date, cwc.end_date) as 'actual_end_date'

FROM contract_with_customers cwc
INNER JOIN rooms r ON r.id = cwc.room_id

WHERE
IF(
cwc.terminate_date IS NULL,
(cwc.start_date <= '" . $start . "' AND cwc.end_date >= '" . $start . "') OR (cwc.start_date >= '" . $start . "' AND cwc.start_date <= '" . $end . "'),
(cwc.start_date <= '" . $start . "' AND cwc.terminate_date >= '" . $start . "') OR (cwc.start_date >= '" . $start . "' AND cwc.start_date <= '" . $end . "')
) 
AND 
IF(
cwc.terminate_date IS NULL,
1,
cwc.start_date <= cwc.terminate_date
)
AND cwc.stage IN ('执行中','执行结束')     
        ";

        if ($suit_id != '' && $suit_id > 0) {
            $sql .= "AND r.suite_id = " . trim($suit_id);
        }

        return DB::select($sql);
    }

}
