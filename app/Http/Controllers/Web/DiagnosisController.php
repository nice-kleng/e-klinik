<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Icd10Service;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiagnosisController extends Controller
{
    protected Icd10Service $icd10Service;

    public function __construct(Icd10Service $icd10Service)
    {
        $this->icd10Service = $icd10Service;
    }

    public function index(Request $request): View
    {
        $keyword = $request->get('search');
        $diagnoses = collect();
        $categories = $this->icd10Service->getCategoryList();

        if ($keyword) {
            $diagnoses = $this->icd10Service->search($keyword);
        }

        return view('diagnoses.index', compact('diagnoses', 'keyword', 'categories'));
    }
}
