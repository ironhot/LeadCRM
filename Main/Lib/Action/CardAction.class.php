<?php

class CardAction extends PublicAction {
	/**
	 * 将订单的卡号信息复制到数据库中 
	 * @return [type] [description]
	 */
	public function splitOrder(){
			set_time_limit(0);
    	header('Content-Type:text/html;charset=utf-8');

		$Model = D('Order');
		$list = $Model->query('select * from ihg_order ');

		$ModelCard = D('Card');
		foreach ($list as $key => $value) {
			for($i=1;$i<=$value['count'];$i++){
				$data = array();
				$data['card'] = $value['card'];
				$data['name'] = $value['name'];
				$data['product_id'] = $value['product_id'];
				$data['nights'] = 5 *  $value['count'];
				$data['order_id'] = $value['id'];


				$ModelCard->create($data);
				$res = $ModelCard->add($data);
				echo $ModelCard->getLastSql();

				//exit;

			}



		}

		

	}

	public function splitCard(){
		$this->error('已经拆分');
		$Model = D('Order');
		$ModelCard = D('Card');
		$list = $Model->select();

		foreach ($list as $key => $value) {

			for($i=0;$i<$value['count'];$i++){
				# code...
				$data = array();
				$data['order_id'] = $value['id'];
				$data['card'] = $value['card'];
				$data['product_id'] = $value['product_id'];
				$data['name'] = $value['name'];
				$data['nights'] = 5; 

				$ModelCard->create($data);
				$ModelCard->add($data);
			}


		}
	}

}