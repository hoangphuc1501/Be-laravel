<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class PermissionController extends Controller
{
    //
    function add(){
        echo "add permission";
    }

    function store(Request $request){
        echo "store permission";
    }
}
