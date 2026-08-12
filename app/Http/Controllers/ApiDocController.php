<?php

namespace App\Http\Controllers;

class ApiDocController extends Controller
{
    /**
     * Display Public REST API Documentation (v1)
     */
    public function index()
    {
        return view('api-docs');
    }
}
