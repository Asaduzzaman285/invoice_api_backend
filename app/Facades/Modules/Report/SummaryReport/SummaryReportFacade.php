<?php

namespace App\Facades\Modules\Report\SummaryReport;
use Illuminate\Support\Facades\Facade;

class SummaryReportFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'detailsreporthelper';
    }
}
