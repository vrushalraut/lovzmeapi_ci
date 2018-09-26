<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Errors extends CI_Model
{
	public function __construct()
	{
		header('Content-Type: application/json; charset=utf-8');
	}

	public function error_name($error_name)
	{
		if ($error_name == 'api') 
		{
			$data = array(
							'error' => array(
												'code'=>27,
												'type'=>'Invalid API KEY'
											),
							'message'=>"Please provide correct API Key!"
						);
		}
		elseif ($error_name == 'resource') 
		{
			$data = array(
							'error' => array(
												'code'=>27,
												'type'=>'Invalid Resource!'
											),
							'message'=>"Resource type not exist"
						); 
		}
		elseif ($error_name == 'api_mis') 
		{
			$data = array(
							'error' => array(
												'code'=>27,
												'type'=>'Missing API KEY'
											),
							'message'=>"Please provide API Key!"
						);
		}
		else
		{
			$data = array(
							'error' => 'Error',
							'message'=>"Invalid resource call!"
						); 	
		}
		echo json_encode($data,true);
	}
}