<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends CI_Model
{
	public function __construct()
	{
		header('Content-Type: application/json; charset=utf-8');
	}

	public function getProducts($id_product=null)
	{
		// $data = array();
		if(!empty($id_product))
		{
			$this->db->where('id_product',$id_product);
			$query = $this->db->get(_DB_PREFIX_.'product');
			$data = $query->result();
			$data1['price_extra'] = $this->calProductPriceTax($id_product);
			array_push($data, $data1);
			// echo json_encode($query->result());
			echo json_encode($data);
		}
		else
		{
			$this->db->select('id_product');
			$query = $this->db->get(_DB_PREFIX_.'product');
			echo json_encode($query->result());
		}
	}

	public function getLovzmeProductsPrice($id_product)
	{	
		if(!empty($id_product))
		{
			// $aa = array('1','2','3');
			echo json_encode($this->calProductPriceTax($id_product));
		}
		else
		{
			$this->db->select('id_product');
			$result = $this->db->get(_DB_PREFIX_.'product')
								->result();

			$json = array();
			foreach ($result as $key => $row) 
			{
				$json[] = $this->calProductPriceTax($row->id_product);
			}
			echo json_encode($json);
		}
	}

	public function calProductPriceTax($id_product)
	{
			if(!empty($id_product))
			{
				// Getting TAX RULE GROUP
				$this->db->select('id_tax_rules_group,price');
				$this->db->where('id_product',$id_product);
				$products = $this->db->get(_DB_PREFIX_.'product')
									->row();

				$id_tax_rules_group = $products->id_tax_rules_group;
				$price = $products->price;
				
				// Getting TAX ID
				$this->db->select('id_tax');
				$this->db->where('id_tax_rules_group',$id_tax_rules_group);
				$id_tax = $this->db->get(_DB_PREFIX_.'tax_rule')	
										->row()
										->id_tax;
				
				// Gettring TAX RATE
				$this->db->select('rate');
				$this->db->where('id_tax',$id_tax);
				$tax_rate = $this->db->get(_DB_PREFIX_.'tax')
										->row()
										->rate;
				
				
				$data['id_product']	= $id_product;
				$data['price'] = $price;
				$data['price_tax_excl'] = $price;
				$data['price_tax_incl'] = $price + ((($tax_rate + $tax_rate)/100) * $price);
				$data['tax_rate'] = $tax_rate;
				
				// Getting Reducton rate
				$this->db->select('reduction');
				$this->db->where('id_product',$id_product);
				$reduction_rate = $this->db->get(_DB_PREFIX_.'specific_price')
												->row();

				$reduction_rate = floatval($reduction_rate->reduction);
				if (isset($reduction_rate) && $reduction_rate != 0) 
				{
					$data['price_reduction'] = $data['price_tax_incl'] - (($reduction_rate) * $data['price_tax_incl']);
					$data['reduction_rate'] = $reduction_rate * 100;
				}
				else
				{
					$data['reduction_rate'] = 0;
				}

				//echo json_encode($data);
				// print_r($data);
				return $data;

			}
			else
			{
				$data['error'] = "Poduct ID is not valid";
				echo json_encode($data);
			}
	}
	
}