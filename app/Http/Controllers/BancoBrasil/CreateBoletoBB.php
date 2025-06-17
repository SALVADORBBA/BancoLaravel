<?php

namespace App\Http\Controllers\BancoBrasil;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreateBoletoBB extends Controller
{ 
    public function create(Request $request)
    {
        return  $request->all();
    }
}