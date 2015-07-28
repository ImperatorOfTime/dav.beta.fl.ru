<?php

/**
 * Class TServiceFilterForm
 *
 * ����� ������� ������� �����
 */
class TServiceFilterForm {

	public $category;

	public $category_group;

	public $keywords = '';

	public $prices = array();

	public $country;

	public $city;
        
        public $order;
        
        public $price_max;
        
        

        public function attributes($attributes = null)
	{
		if (is_null($attributes))
		{
			return get_object_vars($this);
		}
		foreach($attributes as $key => $value)
		{
			if (property_exists($this, $key))
			{
				$this->{$key} = $value;
			}
		}

		// � ������� prices ������ ���� ����� "��� ����" ������ ����� ������ �������� �� �������
		// � ���� �������, �� "��� ����" ���� �������
		unset($this->prices[tservices_catalog::ANY_PRICE_RANGE]); // ������
		if (count($this->prices) == 0) // ���� ������ �� ��������
		{
			$this->prices[tservices_catalog::ANY_PRICE_RANGE] = 1; // �� ������� �������
		}
	}

	/**
	 * ���������� true ���� �� ���� �������� ������� �� ��� ����������
	 * @return bool
	 */
	public function isEmpty()
	{
		$values = get_object_vars($this);
		unset($values['prices'][tservices_catalog::ANY_PRICE_RANGE]);
                unset($values['order']);
		return 0 == count(array_filter($values));
	}
}