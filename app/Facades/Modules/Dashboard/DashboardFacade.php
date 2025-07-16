<?php

namespace App\Facades\Modules\Dashboard;


use Illuminate\Support\Facades\Facade;

class DashboardFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'dashboardhelper';
    }
}
