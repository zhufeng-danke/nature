<?php

namespace App\Http\Controllers\Data;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;
use Excel;

class ContractController extends BaseController
{

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

            //获取所有有效合同
            $valuableContracts = $this->valuableLandlordContracts($start_date, $end_date, $suit_id);
            if (!count($valuableContracts)) {
                flash('查询结果为空')->warning();
                return view('data.landlord', $data);
            }

            $dataArray = [];
            
            //查询前序合同
            foreach ($valuableContracts as $key => $valuableContract) {
                $pre = $this->preContractId($valuableContract->id);
                $valuableContracts[$key]->pre_contract_info = is_array($pre) ? implode(',', $pre) : '';
                $dataArray[] = $this->objectToArray($valuableContracts[$key]);
            }

            $excel_title = 'contract_with_landlord_' . $start_date . '~' . $end_date;
            $this->exportFile($excel_title,$dataArray);
        }

        return view('data.landlord', $data);
    }


    //预处理前序合同
    public function preContractId($contract_id)
    {
        $pre_contract = [];
        $pre_contract_info = [];
        $next_id = $contract_id;
        do {
            $contract = $this->landlordPreContracts($next_id);
            if ($contract) {
                $next_id = $contract->id;
                $pre_contract[] = $contract;
                $pre_contract_info[] = $contract->id . '-' . $contract->number;
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
AND cwl.stage IN ('执行中', '执行结束')
AND cwl.start_date IS NOT NULL
AND cwl.end_date IS NOT NULL
AND cwl.suite_id IS NOT NULL        
        ";

        if ($suit_id != '' && $suit_id > 0) {
            $sql .= "AND cwl.suite_id = " . trim($suit_id);
        }

        return DB::select($sql);
    }

    /**************************************************************** 出房合同 ****************************************************************/

    public function customer()
    {
        flash('Message')->success();

        return view('data.customer');
    }

}
