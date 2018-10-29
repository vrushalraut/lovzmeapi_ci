<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// Based on RESTful
class Validation extends CI_Model
{
	public function __construct()
	{
		header('Content-Type: application/json; charset=utf-8');
	}

	public function validuser($username,$password)
	{
		$username = trim($username, " ");
		$this->db->select('*');
		$this->db->where('email',$username);
		$customer = $this->db->get(_DB_PREFIX_.'customer')->row();
		
		//validation logic
		$email = $customer->email;
		$passwd = $customer->passwd;
		$passwd_veri = password_verify($password, $passwd);

		if( $username != $email )
		{
			$data = array(	'status' => 'FAILED' ,
							'validation' => FALSE,
							'error'	=>	'username',
							'remark' => 'Invalid username' );
			echo json_encode($data);
			exit;
		}
		elseif ($passwd_veri == TRUE) 
		{
			$data = array(	'status' => "Success",
							'validation' => TRUE ,
							'remark' => 'Login successful');
			echo json_encode($data);
		}
			else
			{
				$data = array(	'status' => "FAILED",
								'validation' => FALSE ,
								'error'	=>	'password',
								'remark' => 'Incorrect password');
				echo json_encode($data);
				exit;
			}
	}
}
