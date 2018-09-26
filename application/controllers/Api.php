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
			$this->products->getLovzmeProductsPrice();
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

	public function test()
	{
		echo "Hello Test";
		$url = file_get_contents("http://localhost/php/ci/lovzmeapi/index.php/api/lovzmeprice/14");
		$data = json_decode($url,true);
		// print_r($data);
	}
}
