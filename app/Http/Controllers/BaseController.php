<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Excel;

class BaseController extends Controller
{

    public function exportFile($title, $data, $is_object = false){
        if($is_object){
            foreach ($data as $single){
                $dataArray[] = $this->objectToArray($single);
            }
        }else{
            $dataArray = $data;
        }

        Excel::create($title, function ($excel) use ($dataArray) {
            $excel->setCreator(\Auth::user()->email)
                ->setCompany('蛋壳公寓');
            $excel->sheet('Sheetname', function($sheet) use ($dataArray) {

                $sheet->fromArray($dataArray);

            });

        })->download('xlsx');
    }

    /**
     * 将对象转换为数组
     * @param $e
     * @return array|void
     */
    function objectToArray($e) {
        $e = (array)$e;
        foreach ($e as $k => $v) {
            if (gettype($v) == 'resource') return;
            if (gettype($v) == 'object' || gettype($v) == 'array')
                $e[$k] = (array)objectToArray($v);
        }
        return $e;
    }

}
