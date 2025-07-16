<?php

namespace App\Http\Controllers\Dashboard;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Contracts\Dashboard\DashboardInterface;

class DashboardController extends Controller
{
    use ApiResponser;

    protected $dashboard;

    public function __construct(DashboardInterface $dashboard)
    {
        $this->dashboard = $dashboard;
    }

    public function getDashboardData(Request $request)
    {
        $data = $this->dashboard->dashboardData($request);
        return $this->set_response($data,  200, 'success', ['Dashboard summary']);
    }

}
