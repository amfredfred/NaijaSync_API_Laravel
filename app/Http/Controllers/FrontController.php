<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FrontController extends Controller {
    public function login() {

        return view( 'Auth.login' );
    }

    function register() {

        return  view( 'Auth.register' );
    }

}
