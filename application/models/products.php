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
			$data1['product_info'] = $this->calProductPriceTax($id_product);
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
			unset($data);
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
			unset($data);
		}
	}

	public function calProductPriceTax($id_product)
	{
			if(!empty($id_product))
			{
				// Getting TAX RULE GROUP
				$this->db->select('*');
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

				// Category name
				$sql = "	SELECT
								    DISTINCT `name`
								FROM
								    `"._DB_PREFIX_."category_lang`
								INNER JOIN `"._DB_PREFIX_."category_group` INNER JOIN `"._DB_PREFIX_."category` ON `"._DB_PREFIX_."category`.`id_category` = `"._DB_PREFIX_."category_group`.`id_category`
							WHERE `"._DB_PREFIX_."category_lang`.`id_category` =". $products->id_category_default ."";
				
				$category_name = $this->db->query($sql)->row()->name;

				// Product name
				$sql = "SELECT DISTINCT `name` FROM `"._DB_PREFIX_."product_lang` WHERE `id_product` =".$id_product."";
				$product_name = $this->db->query($sql)->row()->name;

				$sql = " SELECT
							    m.name AS manufacturer,
							    p.id_product,
							    pl.name,
							    GROUP_CONCAT(DISTINCT(al.name) ORDER BY al.name DESC SEPARATOR ',') AS combinations,
							    s.quantity,
							    LENGTH( GROUP_CONCAT(DISTINCT(al.name) ORDER BY al.name DESC SEPARATOR ',')) as comb_length
							FROM
							    ps_product p
							LEFT JOIN ps_product_lang pl ON
							    (
							    	p.id_product = pl.id_product
							    )
							LEFT JOIN ps_manufacturer m ON
							    (
							        p.id_manufacturer = m.id_manufacturer
							    )
							LEFT JOIN ps_category_product cp ON
							    (
							    	p.id_product = cp.id_product
							    )
							LEFT JOIN ps_category_lang cl ON
							    (
							        cp.id_category = cl.id_category
							    )
							LEFT JOIN ps_category c ON
							    (
							    	cp.id_category = c.id_category
							    )
							LEFT JOIN ps_stock_available s ON
							    (
							    	p.id_product = s.id_product
							    )
							LEFT JOIN ps_product_tag pt ON
							    (
							    	p.id_product = pt.id_product
							    )
							LEFT JOIN ps_product_attribute pa ON
							    (
							    	p.id_product = pa.id_product
							    )
							LEFT JOIN ps_product_attribute_combination pac ON
							    (
							        pac.id_product_attribute = pa.id_product_attribute
							    )
							LEFT JOIN ps_attribute_lang al ON
							    (
							        al.id_attribute = pac.id_attribute
							    )
						WHERE
						    pl.id_lang = 1 
						    AND cl.id_lang = 1 
						    AND p.id_shop_default = 1 
						    AND c.id_shop_default = 1 
						    AND	p.id_product = '$id_product'
						GROUP BY
                           	pac.id_product_attribute
                        ORDER BY 
                        	comb_length DESC";

				$combination = $this->db->query($sql)->result();

				$data['id_product']	= $id_product;

				// Combination Logic
				$i = 0;
				$combinations = "";
				$comcolor = "";

				foreach ($combination as $key => $value) 
				{
						$data['combinations'][$i]['id_product'] = $value->id_product;
						
						$combinations = explode(',',$value->combinations);		
						/*
						// Product Size logic						
							if( is_numeric($combinations[0]) == TRUE || preg_match("/[X|S|L|M|XL|XXL|XXXL]+/",$combinations[0]) == TRUE || preg_match("/^[2-5](2|4|6|8|0)(A(A)?|B|C|D(D(D)?)?|E|F|G|H)$/",$combinations[0]) == TRUE || is_numeric($combinations[0]) == TRUE)
								{
									$data['combinations'][$i]['combination']['size'] = $combinations[0];

									if($combinations[0] === "Multicolor")
									{
										$data['combinations'][$i]['combination']['color'] = $combinations[0];
									}
								}
							// else
							// {
							// 	$data['combinations'][$i]['combination']['size'] = $combinations[1];
							// 	$data['combinations'][$i]['combination']['color'] = $combinations[0];
							// }
							else
							{
								if( is_numeric($combinations[1]) == TRUE || preg_match("/[X|S|L|M|XL|XXL|XXXL]+/",$combinations[1]) == TRUE || preg_match("/^[2-5](2|4|6|8|0)(A(A)?|B|C|D(D(D)?)?|E|F|G|H)$/",$combinations[1]) == TRUE || is_numeric($combinations[1]) == TRUE && $combinations[1] != "Multicolor")
									{
										$data['combinations'][$i]['combination']['size'] = $combinations[1];
										$data['combinations'][$i]['combination']['color'] = $combinations[0];
										// $color = $combinations[0];
										// unset($combinations[1]);						
									}
							}

							// Product Color logic
							if( is_numeric($combinations[1]) == TRUE || preg_match("/[X|S|L|M|XL|XXL|XXXL]+/",$combinations[1]) == TRUE || preg_match("/^[2-5](2|4|6|8|0)(A(A)?|B|C|D(D(D)?)?|E|F|G|H)$/",$combinations[1]) == TRUE || is_numeric($combinations[1]) == TRUE)
							{
								$data['combinations'][$i]['combination']['color'] = $combinations[0];
								$color = $combinations[0];

							}				
							else
							{
								if($combinations[1] == null)
								{
									if(empty($combinations[1]) && $color == "Multicolor" )
									{
										$data['combinations'][$i]['combination']['color'] = "Multicolor";
									}
									else
									{
										if ($color == null) 
										{
											$data['combinations'][$i]['combination']['color'] = "Multicolor";
										}
										else
										{
											$data['combinations'][$i]['combination']['color'] = $color;
										}
									}
								}

								else
								{						
									$data['combinations'][$i]['combination']['color'] = $combinations[1];
									$color = $combinations[1];	
									unset($combinations[1]);						
								}
							}
						*/

						//Size Logic PROTOYPE
						if( is_numeric($combinations[0]) || 
							preg_match("/[X|S|L|M|XL|XXL|XXXL]+/",$combinations[0]) || 
							preg_match("/^[2-5](2|4|6|8|0)(A(A)?|B|C|D(D(D)?)?|E|F|G|H)$/",$combinations[0]) || 
							is_numeric($combinations[0]) == TRUE )
							{
								$data['combinations'][$i]['combination']['size'] = $combinations[0];
								$data['combinations'][$i]['combination']['color'] = $combinations[1];
								

									if( $combinations[1] != null )
									{
										$color = $combinations[1];	
									}

									if( $combinations[1] == null )
									{
										$data['combinations'][$i]['combination']['color'] = $color;
									}
							}
							else
							{
								$data['combinations'][$i]['combination']['size'] = $combinations[1];
								$data['combinations'][$i]['combination']['color'] = $combinations[0];
								

									if( $combinations[0] != null )
									{
										$color = $combinations[0];	
									}

									if( $combinations[0] == null )
									{
										$data['combinations'][$i]['combination']['color'] = $color;
									}

							}

						$data['combinations'][$i]['quantity'] = $value->quantity;			
						$i++;
				}

				$data['product_name'] = $product_name;
				$data['category_name'] = $category_name;
				$data['reference']	= $products->reference;
				$data['price'] = $price;
				$data['price_tax_excl'] = $price;
				$data['price_tax_incl'] = $price + ((($tax_rate + $tax_rate)/100) * $price);
				$data['tax_rate'] = $tax_rate;
				
				// Getting Reducton rate
				$this->db->select('reduction');
				$this->db->where('id_product',$id_product);
				$reduction_rate = $this->db->get(_DB_PREFIX_.'specific_price')
												->row();
				
				if( $reduction_rate == null )
				{
					$reduction_rate = 0;
					$reduction_rate1 = $reduction_rate;
				}
				else
				{
					$reduction_rate_db = $reduction_rate->reduction;
					$reduction_rate1 = floatval($reduction_rate_db);
				}

				if (isset($reduction_rate1) && $reduction_rate1 != 0) 
				{
					$data['price_reduction'] = $data['price_tax_incl'] - (($reduction_rate1) * $data['price_tax_incl']);
					$data['reduction_rate'] = $reduction_rate1 * 100;
				}
				else
				{
					$data['reduction_rate'] = 0;
				}
				return $data;
			}
			else
			{
				$data['error'] = "Poduct ID is not valid";
				echo json_encode($data);
			}
	}
	
}