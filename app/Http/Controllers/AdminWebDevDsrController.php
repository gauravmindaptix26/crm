<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\WebDevPmDsr;

class AdminWebDevDsrController extends Controller
{

    public function index()
    {
        $reports = WebDevPmDsr::with('pm')->orderBy('report_date', 'desc')->paginate(20);
        return view('admin.index', compact('reports'));
    }


    public function view($id)
    {
        $report = WebDevPmDsr::findOrFail($id);
        return view('admin.view', compact('report'));
    }
}