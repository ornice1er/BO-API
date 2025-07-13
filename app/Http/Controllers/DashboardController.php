<?php
namespace App\Http\Controllers;

use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DashboardController extends Controller
{
  

    protected $ls;

    public function __construct(LogService $ls)
    {
     //   $this->companyRepository = $companyRepository;
        $this->ls = $ls;

        //$this->middleware('auth:api')->except(['getNotified', 'show']);

    }

    function index() {
        
    }

}
