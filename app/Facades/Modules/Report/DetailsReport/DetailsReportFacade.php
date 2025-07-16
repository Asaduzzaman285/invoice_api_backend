<?php

namespace App\Facades\Modules\Report\DetailsReport;
use Illuminate\Support\Facades\Facade;

class DetailsReportFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'detailsreporthelper';
    }
}
