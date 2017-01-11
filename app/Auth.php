<?php

namespace App;

use App\classes\Mail;
use App\classes\Rest;

class Auth extends Rest {

	public function register() {

		$request = $this->request();

		//Set Validation Rules
		$rules = array(
			'name' => 'required|unique,users',
			'email' => 'required|valid_email|unique,users',
		);

		//Validate Request Data
		$this->validate($request, $rules);

		//Insert data into db after validation
		$newUser = $this->db->table('users')->insert($request); // Docs => https://github.com/izniburak/PDOx/blob/master/DOCS.md

		if ($newUser) {

			$user = $this->db->table('users')->where('id', $newUser)->get();

			//Send Welcome email

			Mail::send('email_confirm', compact('user'))
				->to($request['email'], $request['name'])
				->subject('Welcome to my app')
				->deliver();
		}

		//return  response

		return ($newUser) ? $this->success('User has been successfully registered') : $this->error('Something Went Wrong');

	}

}