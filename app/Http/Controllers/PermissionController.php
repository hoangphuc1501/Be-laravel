<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
