<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */

	public function __construct()
	{
		parent::__construct();
		$this->load->model('products');
		$this->load->model('errors');
		$this->load->model('validation');

		$api_key = $this->input->get('ws_key');

		if ( API_KEY != $api_key ) 
		{
			if ( empty($api_key) ) 
			{
				$this->error('api_mis');
				exit;
			}
			else
			{
				$this->error('api');
				exit;	
			}
			
		}
		
	}

	public function index()
	{
		echo "Lovzme API";
	}

	public function products()
	{
		$id_product = $this->uri->segment(3);
		
		if(isset($id_product))
		{
			$this->products->getProducts($id_product);
		}	
		else
		{
			$this->products->getProducts();
		}
	}

	public function lovzmeprice()
	{
		$id_product = $this->uri->segment(3);
		
		if(isset($id_product))
		{
			$this->products->getLovzmeProductsPrice($id_product);
		}
		else
		{
			$this->products->getProducts();
		}
	}

	public function login()
	{
		$api_key = $this->input->get('ws_key');

		if( API_KEY == $api_key )
		{
			if($this->input->method(true) == 'POST')
			{
				$username = $_POST['username'];
				$password = $_POST['password'];

				$this->user_validation($username,$password);
				// echo json_encode($data);
			}
			
			elseif ($this->input->method(true) == 'GET') 
			{
				$data["error"] = array(	"Code"	=> 401,
										"Category" => "UNAUTHORIZED",
										"Message" => "You are unauthorized to access the requested resource.",
								);
				echo json_encode($data);
			}

			elseif ($this->input->method(true) == 'PUT') 
			{
				$data["error"] = array(	"Code"	=> 401,
										"Category" => "UNAUTHORIZED",
										"Message" => "You are unauthorized to access the requested resource.",
								);
				echo json_encode($data);
			}

			else
			{
				$data["error"] = array(	"Code"	=> 404,
										"Category"	=> "NOT FOUND",
										"Message" => "We could not find the resource you requested." );
				echo json_encode($data);
			}
		}

		else
		{
			$data["error"] = array(	"Code"	=> 404,
									"Category"	=> "NOT FOUND",
									"Message" => "We could not find the resource you requested." );
			echo json_encode($data);
		}

	}

	public function error($error_name)
	{
		$errors = $this->errors->error_name($error_name);
		return $errors;
	}

	public function getmethod()
	{
		echo "Hello";
		$aa = $this->uri->segment(3);
		// echo $aa;
	}

	public function user_validation($username,$password)
	{
		$this->validation->validuser($username,$password);
		// echo json_encode($username);
	}

	public function test()
	{
		$url = file_get_contents("http://localhost/php/ci/lovzmeapi/index.php/api/lovzmeprice/14??ws_key=7U7DZ8549RD546TJUMNSIAA2LAJ4DDRL");
		$data = json_decode($url,true);
		echo "Hello Test";
		print_r($data);
	}
}
