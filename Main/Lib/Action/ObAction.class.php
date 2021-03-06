<?php

class ObAction extends PublicAction {
	public function __construct(){
		$this->assign('active_ob','active');
		$this->assign('title_h1','客服订单管理');

		$this->addBreadcrumbs(array(
				'name'=>'订单管理'
		));
		$this->menuAccess();

	}
   	public function index(){
      $this->setReturnUrl();
   		$this->assign('title_h2','列表');
		$this->addBreadcrumbs(array(
				'name' => '订单列表'
		));
		$this->assign('active_ob_'.intval($_GET['status']),'active');

		$this->assign('active_order_index','active');

		if(!empty($_GET['mobile']) && is_numeric($_GET['mobile']) ){
			$map['mobile'] = ($_GET['mobile']);
		}
		if(!empty($_GET['card'])  && is_numeric($_GET['card']) ){
			$map['card'] = ($_GET['card']);

		}
		if(!empty($_GET['id']) && is_numeric($_GET['id'])){
			$map['id'] = $_GET['id'];

		}
		if(!empty($_GET['order_id']) && is_numeric($_GET['order_id'])){
			$map['order_id'] = $_GET['order_id'];

		}
		if(!empty($_GET['status']) && is_numeric($_GET['status'])){
			$map['status']= intval($_GET['status']);

		}

		if(!empty($_GET['name'])){
			$map['name']= ($_GET['name']);

		}

		if(!empty($_GET['tmall_name'])){
			$map['tmall_name']= ($_GET['tmall_name']);

		}



		//获取数据
		$Model = D('Order');

		if(!empty($_GET['status'])){
			$map['status'] = intval($_GET['status']);
		}



		import('ORG.Util.Page');// 导入分页类
		$count      = $Model->where($map)->count();// 查询满足要求的总记录数
		$Page       = new Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数
		$show       = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		
		switch($map['status']){
			case 5:
				$order = 'mobile';
			break;
			case 1:
			case 4:
			
			case 15:
				
			default:
				$order = 'tmall_create_time';
		}

		$list = $Model->order($order)->where($map)->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出


		$this->display();
   	}
   	public function edit(){
   		$this->assign('title_h2','详情');
		$this->addBreadcrumbs(array(
				'name' => '订单详情'
		));
		

		$Model = D('Order');
		$filter['id'] = $_GET['id'];
		$vo = $Model->where($filter)->find();

		//echo $Model->getLastSql();
		$this->assign('vo',$vo);

		
       $ModelOb = D('Ob');
         $filter = array();
         $filter['order_id'] = $_GET['id'];
         $ob = $ModelOb->where($filter)->find();
         
         $this->assign('ob',$ob);

         
		$this->assign('active_ob_'.$vo['status'],'active');

    	$this->display();
   	}
   	public function update(){
   		//限制输入数量
   		if(!empty($_POST['haveCard']) && !empty($_POST['acceptJoin'])){ //或者拥有卡号,并且同意加入优悦会
   			if( (empty($_POST['card']) || !is_numeric($_POST['card']) || strlen($_POST['card']) != 9 )){
   				$this->error('输入的会员卡号不正确。');
   			}
   		}else{
   			//如果不愿意加入或是没有卡号，则不允许添加卡号
   			if(!empty($_POST['card'])){
   				$this->error('如果不愿意加入或是没有卡号，则不允许添加卡号');
   			}
   		}
   		
   		//控制卡号与信息保存的问题,暂时只处理前台
   		/*
   		if(empty($_POST['haveCard'])){
   			if(!empty($_POST['card'])){
   				$this->error('选择了没有拥有卡号，');
   			}
   		}else{

   		}
	*/

   		//保存信息到Order主表
   		
   		//如果选择了拥有卡号，则变为信息确认
   		if($_POST['haveCard'] == 1){
   			$_POST['status'] = 7;
   		}else{
   			$_POST['status'] = 6 ; //需要注册
   		}

   		//如果无法联系，则变为 无法联系
   		if($_POST['isConnect'] != 1){
   			$_POST['status'] = 8;
   		}

   		//如果选择不愿意注册，则标记为退款

   		if($_POST['acceptJoin'] != 1){
   			$_POST['status'] = 14; 
   		}

   		//转换输入内容为py
   		if(!empty($_POST['name'])){
   			$_POST['name_py'] = $this->convertPinyin($_POST['name']);
   		}

   		if(!empty($_POST['address'])){
   			$_POST['address_py'] = $this->convertPinyin($_POST['address']);
   		}


         //判断电子邮件
         if($_POST['haveCard'] == 0  && !is_email($_POST['email'])){
            $this->error('请填写正确的电子邮件');
         }


   		

   		$Model = D('Order');
         $_POST['type'] = 'update';
   		$Model->create();

   		$result = $Model->save();
   		//echo $Model->getLastSql();
   		//exit;
   		if($result){
   			//$this->success('更新成功');
   		}else{
   			//$this->error('订单表更新失败');
   		}


   		//更新到订单表中
   		$ModelOb = D('Ob');

   		$_POST['order_id'] = $_POST['id'];

   		
   		$_POST['create_time'] = mktime();

   		
   		$map = array();
   		$map['order_id'] = $_POST['id'];
   		$info = $ModelOb->where($map)->find();



   		if($info){
	   		$_POST['id'] = $info['id'];
	   		$_POST['update_time'] = mktime();
            $_POST['type'] = 'ob_update';
   			$ModelOb -> create();
	   		$ModelOb->save();
   		}else{
			unset($_POST['id']);
         $_POST['type'] = 'ob_insert';
   			$ModelOb -> create();
	   		$resultOb = $ModelOb -> add();
	   	}

   		/*
   		//状态以主订单的更新为准
   		if($result){
   			$this->success('更新成功');
   		}else{
   			$this->error('订单表更新失败');
   		}
	*/

   		$this->success('更新成功',cookie('return_url'));

   	}
      public function edit2(){
         $this->assign('title_h2','详情');
         $this->addBreadcrumbs(array(
               'name' => '多帐号共享处理'
         ));
         

         $Model = D('Order');
         $filter['id'] = $_GET['id'];
         $vo = $Model->where($filter)->find();

         //echo $Model->getLastSql();
         $this->assign('vo',$vo);

         $ModelOb = D('Ob');
         $filter = array();
         $filter['order_id'] = $_GET['id'];
         $ob = $ModelOb->where($filter)->find();
         
         $this->assign('ob',$ob);

         $this->assign('active_ob_'.$vo['status'],'active');

         $this->display();
      }
      public function update2(){
         if(empty($_POST['ob_comment'])){
            $this->error('请填写备注');
         }

         
         $data['status'] = 16;
         $data['id'] = intval($_POST['id']); 
         $data['type'] = 'ob_du_update';

         $Model = D('Order');
         $Model->create($data);
         $res = $Model->save($data);
         //if($res){


            $ModelOb = D('Ob');
            $info = $ModelOb->where(array('order_id'=>$data['id']))->find();
            
            if($info){
               $data2['ob_comment'] = $_POST['ob_comment'];
               $data2['id'] = $info['id'];
               $ModelOb->create($data2);
               $ModelOb->save($data2);
               
            }else{
               $data2['ob_comment'] = $_POST['ob_comment'];
               $data2['create_time'] = mktime();
               $data2['order_id'] = $data['id'];
               $ModelOb->create($data2);
               $ModelOb->add($data2);
               $res = $ModelOb->getLastSql();
               
            }
            
            $this->success('更新成功');
         //}else{
           // $this->error('更新失败');
         //}
      }
}