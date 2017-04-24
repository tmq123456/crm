<?php
class ProductAction extends Action {

	public function _initialize(){
		$action = array(
			'permission'=>array('getProductByBusiness'),
			'allow'=>array('listdialog','changecontent','adddialog','editdialog', 'mdelete','allproductdialog','validate','check','delimg','sortimg','mutildialog','getcurrentstatus','freight','freightadd','freightedit','freightadd2','edit','service','addservice','editservice','analytics','fservice','viewservice','fserviceadd','checkdelete','manager','addmanager','upper','onper','del','updatemanager')
		);
		B('Authenticate', $action);

		//echo '产品展示需要完善';->数据来源1.本地数据库(产品类目) 2.api接口(本功能没有问题)

//		问题: 1.第三方可以用几个账号登录crm 1.多个   2.1个(限制第三方新增账号)
//
//		2.(多个)权限问题 (公司公告    站内信需要屏蔽或删除)站内信可以修改使用
//			2.1自行添加账号  2.2平台添加账号(大量数据)
//			管理者有所有权限 员工可分配权限
//
//			自行添加账号需要验证(功能,逻辑)
//
//		方案
//		1.(1个)直接查询出所属者
//		2.(多个)新增一个关于第三方公司的字段
	}

	public function validate() {
		if($this->isAjax()){
			if(!$this->_request('clientid','trim') || !$this->_request($this->_request('clientid','trim'),'trim')) $this->ajaxReturn("","",3);
			$field = M('Fields')->where('model = "product" and field = "%s"',$this->_request('clientid','trim'))->find();
			$m_product = $field['is_main'] ? D('Product') : D('ProductData');
			$where[$this->_request('clientid','trim')] = array('eq',$this->_request($this->_request('clientid','trim'),'trim'));
			if($this->_request('id','intval',0)){
				$where[$m_product->getpk()] = array('neq',$this->_request('id','intval',0));
			}
			if($this->_request('clientid','trim')) {
				if ($m_product->where($where)->find()) {
					$this->ajaxReturn("","",1);
				} else {
					$this->ajaxReturn("","",0);
				}
			}else{
				$this->ajaxReturn("","",0);
			}
		}
	}

	public function check(){
		import("@.ORG.SplitWord");
		$sp = new SplitWord();
		$m_product = M('Product');
		$useless_words = array(L('COMPANY'),L('LIMITED'),L('DI'),L('LIMITED_COMPANY'));
		if ($this->isAjax()) {
			$split_result = $sp->SplitRMM($_POST['name']);
			if(!is_utf8($split_result)) $split_result = iconv("GB2312//IGNORE", "UTF-8", $split_result) ;
			$result_array = explode(' ',trim($split_result));
			if(count($result_array) < 2){
				$this->ajaxReturn(0,'',0);
				die;
			}
			foreach($result_array as $k=>$v){
				if(in_array($v,$useless_words)) unset($result_array[$k]);
			}
			$name_list = $m_product->getField('name', true);
			$seach_array = array();
			foreach($name_list as $k=>$v){
				$search = 0;
				foreach($result_array as $k2=>$v2){
					if(strpos($v, $v2) > -1){
						$v = str_replace("$v2","<span style='color:red;'>$v2</span>", $v, $count);
						$search += $count;
					}
				}
				if($search > 0) $customer_search_array[$k] = array('value'=>$v,'search'=>$search);
			}
			$seach_sort_result = array_sort($seach_array,'search','desc');
			if(empty($seach_sort_result)){
				$this->ajaxReturn(0,L('ABLE_ADD'),0);
			}else{
				$this->ajaxReturn($seach_sort_result,L('BUSINESS_OPPORTUNITY_CUSTOMER_IS_CREATED'),1);
			}
		}
	}


	/*  @ $params  筛选条件
	 *
	 *
	 *
	 * */
	public function index(){
		if(session('position_id') == 19 || session('position_id') == 20 || session('position_id') == 16){
			$this->assign('position',session('position_id'));
			$this->assign('url',C('URl'));
			$this->display('service');
		}else{
			$this->assign('position',session('position_id'));
			$this->assign('url',C('URl'));
			$this->display();
		}
	}

	public function service(){
		$this->assign('position',session('position_id'));
		$this->assign('url',C('URl'));
		$this->display();
	}

	public function edit(){
		//实例化 DOMDocument 类，并指定版本号
		// $doc = new DOMDocument();
		// $doc->loadHTML("<html><body>Test<br></body></html>");
		// echo $doc->saveHTML();

		$this->display();
	}

	public function editservice(){
        //$company = M('user')->where("user_id = '%s'",$_GET['company'])->getField('company');
        if(session('company') !==  M('user')->where("user_id = '%s'",$_GET['company'])->getField('company')){
            echo "<script>alert('没有权限!'); history.go(-1);</script>";
        }
        $pro['userId'] = $_GET['company'];
		$pro = http_build_query($pro);
		$opts = array(
			'http' => array(
				'method' => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => $pro
			)
		);
		$context = stream_context_create($opts);
		$html = file_get_contents(C('URL').'/manager/community/getShopFee', false, $context);
		$html = json_decode($html,true);
        //dump($html);
		$this->assign('free',$html['data']['fee']);
		$this->display();
	}

	public function viewservice(){
		$this->display();
	}

	public function add() {
		$this->display();
	}

	public function addservice(){
		$pro['userId'] = session('user_id');
		$pro = http_build_query($pro);
		$opts = array(
			'http' => array(
				'method' => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => $pro
			)
		);
		$context = stream_context_create($opts);
		$html = file_get_contents(C('URL').'/manager/community/getShopFee', false, $context);
		$html = json_decode($html,true);
        //dump($html);
		$this->assign('free',$html['data']['fee']);
		$this->display();
	}

	public function view(){
		$this->display();
	}

	public function delete(){
		$m_product = M('product');
		$m_product_data = M('product_data');
		$m_product_images = M('productImages');
		$r_module = array('Log'=>'RLogProduct', 'File'=>'RFileProduct','rproductProduct','rContractProduct');
		if($this->isPost()){
			$product_ids = is_array($_POST['product_id']) ? implode(',', $_POST['product_id']) : '';
			if ('' == $product_ids) {
				alert('error', L('YOU_HAVE_NOT_CHOOSE_ANY_CONTENT') ,$_SERVER['HTTP_REFERER']);
			} else {
				$productName = '';
				foreach($_POST['product_id'] as $k=>$v){
					$product = $m_product->where('product_id = %d', $v)->find();
					if($product){
						$stock_count = M('stock')->where('product_id = %d', $product['product_id'])->sum('amounts');
						if($stock_count > 0){
							$productName .= $product['name'].'&nbsp;';
						}
					}
				}
				if(!empty($productName)){
					alert('error',L('UNDER_THE_FOLLOWING_PRODUCTS_HAVE_IN_STOCK_YOU_CAN_NOT_DELETE',array($productName)), $_SERVER['HTTP_REFERER']);
				}
				if (!session('?admin')) {
					foreach($_POST['product_id'] as $key => $value){
						if(!$m_product->where('creator_role_id = %d and product_id = %d', session('role_id'), $value)->find()){
							alert('error', L('YOU_DO_NOT_HAVE_PERMISSION_TO_OPERATE_ALL'), $_SERVER['HTTP_REFERER']);
						}
					}
				}
				$product_delete = $m_product->where('product_id in (%s)', $product_ids)->delete();
				$product_data_delete = $m_product_data->where('product_id in (%s)', $product_ids)->delete();
				if($product_delete && $product_data_delete){
					foreach ($_POST['product_id'] as $value) {
						actionLog($value);
						foreach ($r_module as $key2=>$value2) {
							$module_ids = M($value2)->where('product_id = %d', $value)->getField($key2 . '_id', true);
							M($value2)->where('product_id = %d', $value) -> delete();
							if(!is_int($key2)){
								M($key2)->where($key2 . '_id in (%s)', implode(',', $module_ids))->delete();
							}
						}
						//删除图片
						$images_files = $m_product_images->where('product_id = %d', $value)->select();
						foreach($images_files as $files){
							@unlink($files['path']);
						}
						$m_product_images->where('product_id = %d', $value)->delete();
					}
					alert('success', L('DELETE_THE_SUCCESS') ,U('product/index'));
				} else {
					alert('error', L('DELETE_FAILED_PLEASE_CONTACT_YOUR_ADMINISTRATOR'),$_SERVER['HTTP_REFERER']);
				}

			}
		} elseif($_GET['id']) {
			$product_id = intval($_GET['id']);
			$product = $m_product->where('product_id = %d', $product_id)->find();
			if (is_array($product)) {
				$stock_count = M('stock')->where('product_id = %d', $product['product_id'])->sum('amounts');
				if($stock_count > 0){
					alert('error', L('THE_PRODUCT_IS_AVAILABLE_FROM_STOCK_AND_CAN_NOT_BE_DELETED'), $_SERVER['HTTP_REFERER']);
				}
				if(session('?admin') || $product['creator_role_id'] == session('role_id')){
					if($m_product->where('product_id = %d', $product_id)->delete()){
						foreach ($r_module as $key2=>$value2) {
							if(!is_int($key2)){
								$module_ids = M($value2)->where('product_id = %d', $product_id)->getField($key2 . '_id', true);
								M($value2)->where('product_id = %d', $product_id) -> delete();
								M($key2)->where($key2 . '_id in (%s)', implode(',', $module_ids))->delete();
							}
						}
						//删除图片
						$images_files = $m_product_images->where('product_id = %d', $product_id)->select();
						foreach($images_files as $files){
							@unlink($files['path']);
						}
						$m_product_images->where('product_id = %d', $product_id)->delete();

						alert('success', L('DELETE_THE_SUCCESS'), U('product/index'));
					}else{
						alert('error', L('DELETE_FAILED_PLEASE_CONTACT_YOUR_ADMINISTRATOR'),$_SERVER['HTTP_REFERER']);
					}
				} else {
					alert('error', L('YOU_HAVE_NO_PERMISSION'), $_SERVER['HTTP_REFERER']);
				}

			} else {
				alert('error', L('YOU_WANT_TO_DELETE_THE_RECORD_DOES_NOT_EXIST'),$_SERVER['HTTP_REFERER']);
			}
		} else {
			alert('error', L('PLEASE_SELECT_PRODUCT_TO_DELETE'),$_SERVER['HTTP_REFERER']);
		}
	}

	public function mDelete(){
		if($_GET['id']){
			$m_r = M($_GET['r']);
			if($m_r->where("id = %d", trim($_GET['id']))->delete()){
				alert('success',L('DELETE_THE_SUCCESS'),$_SERVER['HTTP_REFERER']);
			} else {
				alert('error',L('DELETE_FAILED'),$_SERVER['HTTP_REFERER']);
			}
		} else {
			alert('error',L('PARAMETER_ERROR'),$_SERVER['HTTP_REFERER']);
		}
	}

	public function editDialog(){
		if($this->isPost()){
			$r = trim($_POST['r']);
			$d_r = D($r);
			$d_r->create();
			if($d_r->save()){
				alert('success', L('MODIFY_THE_SUCCESS'),$_SERVER['HTTP_REFERER']);
			}else{
				alert('error', L('MODIFY_THE_FAILURE'),$_SERVER['HTTP_REFERER']);
			}
		}elseif ($_GET['r'] && $_GET['id']) {
			$rbs = M($_GET['r'])->where('id = %d', $_GET['id'])->find();
			$rbs['info'] = M('product')->where('product_id = %d', $rbs['product_id'])->find();
			$this->r = $_GET['r'];
			$this->rbs = $rbs;
			$this->display();
		}else{
			alert('error', L('PARAMETER_ERROR'),$_SERVER['HTTP_REFERER']);
		}
	}

	public function listDialog(){
		if($this->isPost()){
			$r = $_POST['r'];
			$model_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
			$m_r = M($r);
			$m_id = $_POST['module'] . '_id';  //对应模块的id字段

			$data[$m_id] = $model_id;
			foreach ($_POST['product_id'] as $value) {
				$data['product_id'] = $value;
				if ($m_r -> add($data) <= 0) {
					alert('error', L('SELECT_A_PRODUCT_FAILURE'),$_SERVER['HTTP_REFERER']);
				}
			}
			alert('success', L('SELECT_A_PRODUCT_SUCCESS') ,$_SERVER['HTTP_REFERER']);
		}elseif ($_GET['r'] && $_GET['module'] && $_GET['id']) {
			$id_array = M($_GET['r']) -> where('%s = %d', $_GET['module'] . '_id', $_GET['id'])-> getField('product_id', true);
			$id_array[] = 0;
			$this -> r = $_GET['r'];
			$this -> module = $_GET['module'];
			$this -> model_id = $_GET['id'];
			$d_product = D('ProductView');
			$a = $d_product->where('product_id not in (%s)', implode(',',$id_array))->select();
			$this->list = $a;
			$this->display();
		}else{
			alert('error', L('PARAMETER_ERROR') ,$_SERVER['HTTP_REFERER']);
		}
	}

	public function addDialog(){
		if($this->isPost()){
			$r = $_POST['r'];
			$model_id = isset($_POST['model_id']) ? intval($_POST['model_id']) : 0;
			$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
			$m_r = D($r);
			$m_id = $_POST['module'] . '_id';  //对应模块的id字段
			$m_r->create();
			$m_r->$m_id = $model_id;
			if ($m_r -> add()) {
				alert('success', L('ADD_SUCCESSFUL'),$_SERVER['HTTP_REFERER']);
			} else {
				alert('error', L('ADD_FAILURE'),$_SERVER['HTTP_REFERER']);
			}

		}elseif ($_GET['r'] && $_GET['module'] && $_GET['id']) {
			$id_array = M($_GET['r']) -> where('%s = %d', $_GET['module'] . '_id', $_GET['id']) -> getField('product_id', true);
			$id_array[] = 0;
			$this -> r = $_GET['r'];
			$this -> module = $_GET['module'];
			$this -> model_id = $_GET['id'];
			$d_product = D('ProductView');
			$a = $d_product->where('product_id not in (%s)', implode(',',$id_array))->select();
			$this->list = $a;
			$this->display();
		}else{
			alert('error', L('PARAMETER_ERROR'),$_SERVER['HTTP_REFERER']);
		}
	}

	public function allProductDialog(){
		$d_product = D('ProductView');
		//查询所有产品信息
		$list = $d_product->select();
//        echo $d_product->getLastSql();
//        dump($list);
		foreach($list as $k=>$v){
			$stock_count = M('stock')->where('product_id = %d', $v['product_id'])->sum('amounts');
			//echo M('stock')->getLastSql();
			$list[$k]['stock_count'] = empty($stock_count) ? $list[$k]['stock_count'] = 0 : $list[$k]['stock_count'] = $stock_count;
		}
		$this->list = $list;
		$count = $d_product->count();
		$this->total = $count%10 > 0 ? ceil($count/10) : $count/10;
		$this->count_num = $count;
		$this->display();
	}

	public function changeContent(){
		if($this->isAjax()){
			$product = D('ProductView'); // 实例化User对象
			import('@.ORG.Page');// 导入分页类
			$category = M('product_category');
			$where = array();
			$params = array();

			$p = !$_REQUEST['p']||$_REQUEST['p']<=0 ? 1 : intval($_REQUEST['p']);
			if ($_REQUEST["field"]) {
				$field = trim($_REQUEST['field']);

				$search = empty($_REQUEST['search']) ? '' : trim($_REQUEST['search']);
				$condition = empty($_REQUEST['condition']) ? 'is' : trim($_REQUEST['condition']);
				if	('development_time' == $field || 'listing_time' == $field) $search = is_numeric($search)?$search:strtotime($search);;
				if (!empty($field)) {
					switch ($condition) {
						case "is" : $where[$field] = array('eq',$search);break;
						case "isnot" :  $where[$field] = array('neq',$search);break;
						case "contains" :  $where[$field] = array('like','%'.$search.'%');break;
						case "not_contain" :  $where[$field] = array('notlike','%'.$search.'%');break;
						case "start_with" :  $where[$field] = array('like',$search.'%');break;
						case "end_with" :  $where[$field] = array('like','%'.$search);break;
						case "is_empty" :  $where[$field] = array('eq','');break;
						case "is_not_empty" :  $where[$field] = array('neq','');break;
						case "gt" :  $where[$field] = array('gt',$search);break;
						case "egt" :  $where[$field] = array('egt',$search);break;
						case "lt" :  $where[$field] = array('lt',$search);break;
						case "elt" :  $where[$field] = array('elt',$search);break;
						case "eq" : $where[$field] = array('eq',$search);break;
						case "neq" : $where[$field] = array('neq',$search);break;
						case "between" : $where[$field] = array('between',array($search-1,$search+86400));break;
						case "nbetween" : $where[$field] = array('not between',array($search,$search+86399));break;
						case "tgt" :  $where[$field] = array('gt',$search+86400);break;
						default : $where[$field] = array('eq',$search);
					}
				}
				$params = array('field='.trim($_REQUEST['field']), 'condition='.$condition, 'search='.$_REQUEST["search"]);
			}

			if(intval($_REQUEST['cid'])){
				$sub_category = getSubCategory(intval($_REQUEST['cid']), $category->select());
				foreach($sub_category as $v){
					$id_array[] = $v['category_id'];
				}
				$id_array[] = intval($_REQUEST['cid']);
				$where['category_id'] = array('in', $id_array);
			}

			$count = $product->where($where)->count();// 查询满足要求的总记录数
			$list = $product->order('product_id')->where($where)->Page($p.',10')->select();
//            echo $product->getLastSql();
//            dump($list);exit;
			foreach($list as $k=>$v){
				$stock_count = M('stock')->where('product_id = %d', $v['product_id'])->sum('amounts');
				$list[$k]['stock_count'] = empty($stock_count) ? $list[$k]['stock_count'] = 0 : $list[$k]['stock_count'] = $stock_count;
			}

			$data['list'] = $list;
			$data['p'] = $p;
			$data['count'] = $count;
			$data['total'] = $count%10 > 0 ? ceil($count/10) : $count/10;
			$this->ajaxReturn($data,"",1);
		}
	}

	//分类查询
	public function category(){
		$product_category = M('product_category');
		$category_list = $product_category->select();
		//echo $product_category->getLastSql();
		$category_list = getSubCategory(0, $category_list, '');

		foreach($category_list as $key=>$value){
			$product = M('product');
			$count = $product->where('category_id = %d', $value['category_id'])->count();
			$category_list[$key]['count'] = $count;
			$category_list[$key]['list'] = $product->where('category_id = %d', $value['category_id'])->select();
		}
		//dump($category_list);
		$this->alert=parseAlert();
		$this->assign('category_list', $category_list);
		$this->display();
	}

	//添加分类
	public function category_add(){
		if (isset($_POST['name']) && $_POST['name'] != '') {
			$category = D('ProductCategory');
			if ($category->create()) {
				if ($category->add()) {
					alert('success', L('ADD_SUCCESSFUL'),$_SERVER['HTTP_REFERER']);
				} else {
					alert('error', L('PARAMETER_ERROR'),$_SERVER['HTTP_REFERER']);
				}
			} else {
				alert('error', L('PARAMETER_ERROR'),$_SERVER['HTTP_REFERER']);
			}
		}else{
			$category = M('product_category');
			$category_list = $category->select();
			$this->assign('category_list', getSubCategory(0, $category_list, ''));
			$this->display();
		}
	}

	public function category_delete(){
		$product_category = M('Product_category');
		$product = M('product');
		if($_POST['category_list']){
			foreach($_POST['category_list'] as $value){
				if($product->where('category_id = %d',$value)->select()){
					$name = $product_category->where('category_id = %d',$value)->getField('name');
					alert('error', L('UNDER_THE_CATEGORY_OF_PRODUCTS',array($name)),$_SERVER['HTTP_REFERER']);
				}
				if($product_category->where('parent_id = %d',$value)->select()){
					$name = $product_category->where('category_id = %d',$value)->getField('name');
					alert('error', L('UNDER_THE_CATEGORY_OF_CHILD_CATEGORIES',array($name)),$_SERVER['HTTP_REFERER']);
				}
			}
			if($product_category->where('category_id in (%s)', join($_POST['category_list'],','))->delete()){
				alert('success', L('CATEGORY_WAS_REMOVED_SUCCESSFULLY') ,$_SERVER['HTTP_REFERER']);
			}else{
				alert('error', L('CATEGORY_WAS_REMOVED_FAILED') ,$_SERVER['HTTP_REFERER']);
			}
		}elseif($_GET['id']){
			if($product->where('category_id = %d',$_GET['id'])->select()){
				$name = $product_category->where('category_id = %d',$value)->getField('name');
				alert('error', L('UNDER_THE_CATEGORY_OF_PRODUCTS',array($name)),$_SERVER['HTTP_REFERER']);
			}
			if($product_category->where('parent_id = %d',$value)->select()){
				$name = $product_category->where('category_id = %d',$value)->getField('name');
				alert('error', L('UNDER_THE_CATEGORY_OF_CHILD_CATEGORIES',array($name)),$_SERVER['HTTP_REFERER']);
			}
			if($product_category->where('category_id = %d',$_GET['id'])->delete()){
				alert('success', L('CATEGORY_WAS_REMOVED_SUCCESSFULLY') ,$_SERVER['HTTP_REFERER']);
			}else{
				alert('error', L('CATEGORY_WAS_REMOVED_FAILED') ,$_SERVER['HTTP_REFERER']);
			}
		}else{
			alert('error',L('PARAMETER_ERROR'),$_SERVER['HTTP_REFERER']);
		}
	}

	//编辑产品分类信息
	public function category_edit(){
		if($_GET['id']){
			$product_category = M('product_category');
			$category_list = $product_category->select();
			$this->assign('category_list', getSubCategory(0, $category_list, ''));
			$product_category = M('product_category');
			$categoryList = $product_category->select();	//读取分类列表 加载下拉框
			foreach($categoryList as $key=>$value){
				if($value['category_id'] == $_GET['id']){
					unset($categoryList[$key]);
				}
			}

			$this->category_list = $categoryList;
			$this->temp =$product_category->where('category_id =%s', $_GET['id'])->find();
			$this->display();
		}elseif($_POST['category_id']){
			$product_category = M('product_category');
			$product_category -> create();
			if($product_category->save()){
				alert('success',L('MODIFY_THE_CATEGORY_INFORMATION_SUCCESSFULLY'),$_SERVER['HTTP_REFERER']);
			}else{
				alert('error',L('THERE_IS_NO_DATA_CHANGE_MODIFY_THE_CATEGORY_INFORMATION_FAILURE'),$_SERVER['HTTP_REFERER']);
			}
		}else{
			alert('error',L('PARAMETER_ERROR'),$_SERVER['HTTP_REFERER']);
		}
	}

	//产品销量统计(备注缺少字段报错)
	public function count(){
		//商机	产品	销量	成本	交易价	盈利
		$sales = D('SalesView');
		$sales_list = $sales->order('create_time')->select();
		//echo $sales->getLastSql();
		foreach($sales_list as $key=>$value){
			$count = $value['product_amount'];
			$sales_price = $value['sales_price'];
			$cost_price = $value['cost_price'];
			$profit = $count*($sales_price-$cost_price);
			$sales_list[$key]['profit'] = $profit;
		}

		$this->salesList = $sales_list;
		$this->display();
	}


	public function getProductByBusiness(){
		$business_id = $_GET['id'];
		if($business_id){
			$r_business_product = M('rBusinessProduct');
			$m_product = M('product');
			$business_product = $r_business_product->where('business_id = %d', $business_id)->select();
			foreach($business_product as $k=>$v){
				$business_product[$k]['product_name'] = $m_product->where('product_id = %d', $v['product_id'])->getField('name');
				$business_product[$k]['standard'] = $m_product->where('product_id = %d', $v['product_id'])->getField('standard');
			}
			$this->ajaxReturn(array('product'=>$business_product,'total_count'=>sizeOf($business_product)),'已获取与商机有关产品！',1);
		}
	}

	//删除图片
	public function delImg(){
		$images_id = $_GET['images_id'];
		if($images_id){
			$m_product_images = M('productImages');
			$images_path = $m_product_images->where('images_id = %d', $images_id)->getField('path');
			$result = $m_product_images->where('images_id = %d', $images_id)->delete();
			if($result){
				@unlink($images_path);
				$this->ajaxReturn('','',1);
			}
		}else{
			$this->ajaxReturn('',L('PARAMETER_ERROR'),0);
		}
	}

	//图片排序
	public function sortImg(){
		$images_files = $_POST['images_arr'];
		$imagesArr = explode(',', $images_files);
		if($imagesArr){
			$m_product_images = M('productImages');
			//拖动后的listorder
			$original_listorder = $m_product_images->where('images_id in (%s)',$images_files)->getField('listorder',true);
			sort($original_listorder);//按顺序排列

			//交换顺序
			foreach($imagesArr as $k=>$v){
				$m_product_images->where('images_id = %d',$v)->setField('listorder',$original_listorder[$k]);
			}
			$this->ajaxReturn('success', '排序成功！', 1);
		}
	}

	public function excelExport($productList=false){
		C('OUTPUT_ENCODE', false);
		import("ORG.PHPExcel.PHPExcel");
		$objPHPExcel = new PHPExcel();
		$objProps = $objPHPExcel->getProperties();
		$objProps->setCreator("crm");
		$objProps->setLastModifiedBy("crm");
		$objProps->setTitle("crm Product");
		$objProps->setSubject("crm Product Data");
		$objProps->setDescription("crm Product Data");
		$objProps->setKeywords("crm Product");
		$objProps->setCategory("crm");
		$objPHPExcel->setActiveSheetIndex(0);
		$objActSheet = $objPHPExcel->getActiveSheet();

		$objActSheet->setTitle('Sheet1');
		$ascii = 65;
		$cv = '';
		$field_list = M('Fields')->where('model = \'product\'')->order('order_id')->select();
		foreach($field_list as $field){
			$objActSheet->setCellValue($cv.chr($ascii).'1', $field['name']);
			$ascii++;
			if($ascii == 91){
				$ascii = 65;
				$cv .= chr(strlen($cv)+65);
			}
		}
		if(is_array($productList)){
			$list = $productList;
		}else{
			$list = D('ProductView')->select();
		}
		$i = 1;
		foreach ($list as $k => $v) {
			$data = m('ProductData')->where("product_id = $v[product_id]")->find();
			if(!empty($data)){
				$v = $v+$data;
			}
			$i++;
			$ascii = 65;
			$cv = '';
			foreach($field_list as $field){
				if($field['form_type'] == 'datetime'){
					if($v[$field['field']] == 0 || strlen($v[$field['field']]) != 10){
						$objActSheet->setCellValue($cv.chr($ascii).$i, '');
					}else{
						$objActSheet->setCellValue($cv.chr($ascii).$i, date('Y-m-d',$v[$field['field']]));
					}
				}elseif($field['form_type'] == 'number' || $field['form_type'] == 'floatnumber' || $field['form_type'] == 'phone' || $field['form_type'] == 'mobile' || ($field['form_type'] == 'text' && is_numeric($v[$field['field']]))){
					//防止使用科学计数法，在数据前加空格
					$objActSheet->setCellValue($cv.chr($ascii).$i, ' '.$v[$field['field']]);
				}elseif($field['field'] == 'category_id'){
					$m_category = M('ProductCategory');
					$category = $m_category->where('category_id = %d',$v['category_id'])->find();
					$objActSheet->setCellValue($cv.chr($ascii).$i, $category['name']);
				}else{
					$objActSheet->setCellValue($cv.chr($ascii).$i, $v[$field['field']]);
				}
				$ascii++;
				if($ascii == 91){
					$ascii = 65;
					$cv .= chr(strlen($cv)+65);
				}
			}

		}
		$current_page = intval($_GET['current_page']);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		ob_end_clean();
		header("Content-Type: application/vnd.ms-excel;");
		header("Content-Disposition:attachment;filename=crm_product_".date('Y-m-d',mktime())."_".$current_page.".xls");
		header("Pragma:no-cache");
		header("Expires:0");
		$objWriter->save('php://output');
		session('export_status', 0);
	}
	public function getCurrentStatus(){
		$this->ajaxReturn(intval(session('export_status')), 'success', 1);

	}

	public function excelImport(){
		$m_product = D('product');
		$m_product_data = D('ProductData');
		if($_POST['submit']){
			if (isset($_FILES['excel']['size']) && $_FILES['excel']['size'] != null) {
				import('@.ORG.UploadFile');
				$upload = new UploadFile();
				$upload->maxSize = 20000000;
				$upload->allowExts  = array('xls');
				$dirname = UPLOAD_PATH . date('Ym', time()).'/'.date('d', time()).'/';
				if (!is_dir($dirname) && !mkdir($dirname, 0777, true)) {
					alert('error', L('ATTACHMENTS_TO_UPLOAD_DIRECTORY_CANNOT_WRITE'), U('product/index'));
				}
				$upload->savePath = $dirname;
				if(!$upload->upload()) {
					alert('error', $upload->getErrorMsg(), U('product/index'));
				}else{
					$info =  $upload->getUploadFileInfo();
				}
			}
			if(is_array($info[0]) && !empty($info[0])){
				$savePath = $dirname . $info[0]['savename'];
			}else{
				alert('error', L('UPLOAD_FAILED'), U('product/index'));
			};
			import("ORG.PHPExcel.PHPExcel");
			$PHPExcel = new PHPExcel();
			$PHPReader = new PHPExcel_Reader_Excel2007();
			if(!$PHPReader->canRead($savePath)){
				$PHPReader = new PHPExcel_Reader_Excel5();
			}
			$PHPExcel = $PHPReader->load($savePath);
			$currentSheet = $PHPExcel->getSheet(0);
			$allRow = $currentSheet->getHighestRow();

			if ($allRow < 2) {
				alert('error', L('UPLOAD_A_FILE_WITHOUT_A_VALID_DATA'), U('product/index'));
			} else {
				$field_list = M('Fields')->where('model = \'product\'')->order('order_id')->select();
				for($currentRow = 2;$currentRow <= $allRow;$currentRow++){
					$data = array();
					$data['owner_role_id'] = intval($_POST['owner_role_id']);
					$data['creator_role_id'] = session('role_id');
					$data['create_time'] = time();
					$data['update_time'] = time();
					$ascii = 65;  //A
					$cv = '';
					foreach($field_list as $field){
						$info = (String)$currentSheet->getCell($cv.chr($ascii).$currentRow)->getValue();
						if ($field['is_main'] == 1){
							if($field['field'] == 'category_id'){
								$m_product_category = M('ProductCategory');
								$product_category = $m_product_category->where('name like "%s"',$info)->find();
								$info = $product_category['category_id'] ? $product_category['category_id'] : 0;
							}
							$data[$field['field']] = ($field['form_type'] == 'datetime' && $info != null) ? intval(PHPExcel_Shared_Date::ExcelToPHP($info))-8*60*60 : $info;
						}else{
							$data_date[$field['field']] = ($field['form_type'] == 'datetime' && $info != null) ? intval(PHPExcel_Shared_Date::ExcelToPHP($info))-8*60*60 : $info;
						}

						$ascii++;
						//从 A-Z   65->A
						if($ascii == 91){
							$ascii = 65;
							$cv .= chr(strlen($cv)+65);
						}
					}
					if ($m_product->create($data) && $m_product_data->create($data_date)) {
						$product_id = $m_product->add();
						$m_product_data->product_id = $product_id;
						$m_product_data->add();
					}else{

						if($this->_post('error_handing','intval',0) == 0){
							alert('error', L('ERROR INTRODUCED INTO THE LINE',array($currentRow,$m_product->getError().$m_product_data->getError())), U('product/index'));
						}else{
							$error_message .= L('LINE ERROR',array($currentRow,$m_product->getError().$m_product_data->getError()));
							$m_product->clearError();
							$m_product_data->clearError();
						}
					}
				}
				alert('success', $error_message . L('IMPORT_SUCCESS'), U('product/index'));
			}
		}else{
			$this->display();
		}
	}
	//xls模板下载
	public function excelImportDownload(){
		C('OUTPUT_ENCODE', false);
		import("ORG.PHPExcel.PHPExcel");
		$objPHPExcel = new PHPExcel();
		$objProps = $objPHPExcel->getProperties();
		$objProps->setCreator("crm");
		$objProps->setLastModifiedBy("crm");
		$objProps->setTitle("crm Product");
		$objProps->setSubject("crm Product Data");
		$objProps->setDescription("crm Product Data");
		$objProps->setKeywords("crm Product Data");
		$objProps->setCategory("crm");
		$objPHPExcel->setActiveSheetIndex(0);
		$objActSheet = $objPHPExcel->getActiveSheet();

		$objActSheet->setTitle('Sheet1');
		$ascii = 65;
		$cv = '';
		$field_list = M('Fields')->where('model = \'product\' ')->order('order_id')->select();
		foreach($field_list as $field){
			$objActSheet->setCellValue($cv.chr($ascii).'2', $field['name']);
			$ascii++;
			if($ascii == 91){
				$ascii = 65;
				$cv .= chr(strlen($cv)+65);
			}
		}
		$objActSheet->mergeCells('A1:'.$cv.chr($ascii).'1');
		$objActSheet->getRowDimension('1')->setRowHeight(80);
		$objActSheet->getStyle('A1')->getFont()->getColor()->setARGB('FFFF0000');
		$objActSheet->getStyle('A1')->getAlignment()->setWrapText(true);
		$content = L('ADRESS');
		$objActSheet->setCellValue('A1', $content);

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		header("Content-Type: application/vnd.ms-excel;");
		header("Content-Disposition:attachment;filename=crm_product.xls");
		header("Pragma:no-cache");
		header("Expires:0");
		$objWriter->save('php://output');
	}

	//产品树 列表
	public function mutildialog(){
		$product = D('ProductView'); // 实例化对象
		$category = D('ProductCategory'); // 实例化对象
		$where = array();
		$list = $product->order('product_id desc')->where($where)->limit(10)->select();
		//echo $product->getLastSql();
		//dump($list);
		$count = $product->where($where)->count();
		//dump($count);
		//分类
		$category_list = $category->select();
		//dump($category_list);

		$this->treecode = getSubCategoryTreeCode(0,1);

		$this->categoryList = getSubCategory(0, $category_list, ''); //类别选项
		$this->total = $count%10 > 0 ? ceil($count/10) : $count/10;
		$this->count_num = $count;
		$this->assign('list',$list);// 赋值数据集
		$this->display(); // 输出模板
	}

	//查看运费模板
	public function freight(){
		// $data['sid'] = $_SESSION['user_id'];
		// $data = http_build_query($data);
		// $opts = array (
		// 	'http' => array (
		// 		'method' => 'POST',
		// 		'header'  => 'Content-type: application/x-www-form-urlencoded',
		// 		'content' => $data
		// 	)
		// );
		// $context = stream_context_create($opts);
		// $html = file_get_contents(C('URL').'/appserver/template/queryTradeShipTemplate', false, $context);
		// $html = json_decode($html,true);
		//dump($html['data']);
		// foreach ($html['data'] as $key => $val) {
		// 	dump($val['infoList']);
		// 	$this->assign('list',$val['infoList']);
		// }
		// $this->assign('list',$html['data']);
		//$userId = $_SESSION['user_id'];
		// $list = M("trade_ship_template","trade","mysql://root:Luichi2015@@192.168.1.210:3306/eachonline_dev")->query("select a.sid as asid,b.sid as bsid,a.template_id,a.ship_default_fee,a.ship_default_num,a.ship_add_num,a.ship_add_fee,a.ship_area,b.template_name,b.fee_way,b.user_id from trade_ship_template_info AS a LEFT JOIN trade_ship_template AS b ON b.sid = a.template_id WHERE  user_id = '$userId'");
		//dump(($list));

		if(session('position_id') == 17){
			$this->assign('sid',$_SESSION['user_id']);
		}elseif(session('position_id') == 18){
			$this->assign('sid',$_SESSION['parent_id']);
		}
		$this->assign('url',C('URl'));
		$this->display();
	}
	//新建运费模板
	public function freightadd(){

		cookie('source',intval($_GET['source']));
		//dump($_COOKIE);
		$this->assign('source',cookie(source));
		
		if(session('position_id') == 17){
			$this->assign('userId',$_SESSION['user_id']);
		}elseif(session('position_id') == 18){
			$this->assign('userId',$_SESSION['parent_id']);
		}

		$this->assign('url',C('URl'));
		$this->display();
	}

	public function freightadd2(){

		if(session('position_id') == 17){
			$this->assign('userId',$_SESSION['user_id']);
		}elseif(session('position_id') == 18){
			$this->assign('userId',$_SESSION['parent_id']);
		}
		$this->assign('url',C('URl'));
		$this->display();
	}

	public function freightedit(){
		$data['sid'] = $_REQUEST['id'];
		$id = $_REQUEST['id'];
		$data = http_build_query($data);
		$opts = array (
			'http' => array (
				'method' => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => $data
			)
		);

		$context = stream_context_create($opts);
		$html = file_get_contents(C('URL').'/manager/template/getTradeShipTemplate', false, $context);
		$html = json_decode($html,true);
		$city['sid'] = $html['data']['sid'];
		$city['template_name'] = $html['data']['templateName'];
		$city['user_id'] = $html['data']['userId'];
		$city['fee_way'] = $html['data']['feeWay'];
		$city2 = $html['data']['infoList'];
		// dump($html);
		// $sql = 'select * from trade_ship_template_info as a'.
		// 		' left join trade_ship_template as b on b.sid = a.temptemplate_id';
		// $list = M("trade_ship_template","trade","mysql://root:Luichi2015@@192.168.1.210:3306/eachonline_dev")->query("select * from trade_ship_template_info as a left join trade_ship_template as b on b.sid = a.template_id where b.sid = '$id'");
		// //分开查询
		// $city = M("trade_ship_template","trade","mysql://root:Luichi2015@@192.168.1.210:3306/eachonline_dev")->query("select * from trade_ship_template where sid = '$id'");
		// $city2 = M("trade_ship_template_info","trade","mysql://root:Luichi2015@@192.168.1.210:3306/eachonline_dev")->query("select * from trade_ship_template_info where template_id = '$id'");
		// dump($city);
		// $list['name'] = '运费模板1';
		// $list['type'] = '1';
		// $list['city'] = '2,3,4';
		$this->assign('city',$city);
		$this->assign('city2',$city2);
		$this->assign('list',$list);
		if(session('position_id') == 17){
			$this->assign('userId',$_SESSION['user_id']);
		}elseif(session('position_id') == 18){
			$this->assign('userId',$_SESSION['parent_id']);
		}
		
		$this->assign('url',C('URL'));
		$this->display();
	}

	// public function service(){
	// 	$this->display();
	// }

	// public function addservice(){
	// 	$data['sid'] = session('user_id');
	// 	$data['sid'] = '1';
	// 	$data = http_build_query($data);
	// 	$opts = array (
	// 		'http' => array (
	// 			'method' => 'POST',
	// 			'header'  => 'Content-type: application/x-www-form-urlencoded',
	// 			'content' => $data
	// 		)
	// 	);

	// 	$context = stream_context_create($opts);
	// 	$html = file_get_contents(C('URL').'/manager/template/queryTradeShipTemplate', false, $context);
	// 	$html = json_decode($html,true);
	// 	//dump($html);
	// 	$this->assign('free',$html['data']);
	// 	$this->display();
	// }


	public function analytics(){
        if(session('position_id')==14){
            $tradeagent = M('user as u')->join("INNER JOIN crm_role as r on r.user_id = u.user_id")->where("r.position_id = 15 and u.parent_id = '%s'",session('user_id'))->field('u.user_id,u.name,u.role_id')->select();
            //echo M('user as u')->getLastSql();
            $this->assign('tradeagent',$tradeagent);
            $shopsagent = M('user as u')->join("INNER JOIN crm_role as r on r.user_id = u.user_id")->where("r.position_id = 21 and u.parent_id = '%s'",session('user_id'))->field('u.user_id,u.name,u.role_id')->select();
            $this->assign('shopsagent',$shopsagent);
        }else if(session('position_id') == 15){
            $this->assign('gAgent', M('user')->where("user_id = '%s'",session('parent_id'))->getField('name'));
            $this->assign('company', M('user')->where("parent_id = '%s'",session('user_id'))->field('name,user_id,company')->select());
            //echo M('user')->getLastSql();
        }else if(session('position_id') == 16){
            $this->assign('gAgent', M('user')->where("user_id = '%s'",M('user')->where("user_id = '%s'",session('parent_id'))->getField('parent_id'))->getField('name'));
            $this->assign('shopagent',M('user')->where('user_id = "%s"',session('parent_id'))->getField('name'));
            $this->assign('company',M('user')->where("parent_id = '%s'",session('parent_id'))->field('company,user_id,name,parent_id,role_id')->select());
            //echo M('user')->getLastSql();

        }else if(session('position_id') == 19 || session('position_id') == 17){
            $this->assign('gAgent', M('user')->where("user_id = '%s'",M('user')->where("user_id = '%s'",M('user')->where('user_id = "%s"',session('parent_id'))->getField('parent_id'))->getField('parent_id'))->getField('name'));
            $this->assign('shopagent',M('user')->where('user_id = "%s"',session('parent_id'))->getField('name'));

        }else if(session('position_id') == 18 || session('position_id') == 20){
            $this->assign('gAgent', M('user')->where("user_id = '%s'",M('user')->where("user_id = '%s'",M('user')->where('user_id = "%s"',session('parent_id'))->getField('parent_id'))->getField('parent_id'))->getField('name'));
            $this->assign('shopagent',M('user')->where('user_id = "%s"', M('user')->where('user_id = "%s"',session('parent_id'))->getField('parent_id'))->getField('name'));
        }

        if(I('get.company') && I('get.company') !== 'all'){
            $position = M('role')->where('user_id = "%s"',I('get.company'))->find();
            $this->assign('company',I('get.company'));
            $this->assign('areaagent',I('get.areaagent'));
            // $type = 'company';
            // $typeid = I('get.company');
        }else if(I('get.tradeagent') && I('get.tradeagent')!== 'all'){
            $position = M('role')->where('user_id = "%s"',I('get.tradeagent'))->find();
            // echo M('role')->getLastSql();
            $this->assign('tradeagent',I('get.tradeagent'));
        }else if(I('get.shopsagent') && I('get.shopsagent')!== 'all'){
            $position = M('role')->where('user_id = "%s"',I('get.shopsagent'))->find();
            // $type = 'shopsagent';
            // $typeid = I('get.shopsagent');
            //echo M('role')->getLastSql();
            $this->assign('areaagent',I('get.tradeagent'));
        }else{
            $position = array('role_id'=>$_SESSION['role_id'],'position_id'=>$_SESSION['position_id'],'user_id'=>$_SESSION['user_id']);
        }

        if($_GET['role']) {
            $role_id = ($_GET['role']);
        }else{
            $role_id = 'all';
        }
        if($_GET['department'] && $_GET['department'] != 'all'){
            $department_id = ($_GET['department']);
        }else{
            //$department_id 部门信息id
            $department_id = D('RoleView')->where('role.role_id = %d', session('role_id'))->getField('department_id');
            //echo D('RoleView')->getLastSql();
        }

        if($_GET['start_time']) $start_time = strtotime(date('Y-m-d',strtotime($_GET['start_time'])));
        $end_time = $_GET['end_time'] ?  strtotime(date('Y-m-d 23:59:59',strtotime($_GET['end_time']))) : strtotime(date('Y-m-d 23:59:59',time()));

        if($role_id == "all") {
            if(session('position_id')==14 || session('position_id') == 15 ||session('position_id')== 16 || session('position_id') == 21){
                //$roleList = getuser3($type,$typeid,'four');
                $roleList = getTJ($position,false);
            }else{
                $roleList = getTJ('',false);
            }
            $role_id_array = array();
            foreach($roleList as $v2){
                $role_id_array[] = $v2['role_id'];
            }
            $where_role_id = array('in', implode(',', $role_id_array));
            $where_source['creator_role_id'] = $where_role_id;
            $where_industry['owner_role_id'] = $where_role_id;
            $where_renenue['creator_role_id'] = $where_role_id;
            $where_employees['creator_role_id'] = $where_role_id;
        }else{
        	$roleList['0']['role_id'] = $role_id;
            $where_source['creator_role_id'] = $role_id;
            $where_industry['owner_role_id'] = $role_id;
            $where_renenue['creator_role_id'] = $role_id;
            $where_employees['creator_role_id'] = $role_id;
        }
        if($start_time){
            $where_create_time = array(array('elt',$end_time),array('egt',$start_time), 'and');
            $where_source['create_time'] = $where_create_time;
            $where_industry['create_time'] = $where_create_time;
            $where_renenue['create_time'] = $where_create_time;
            $where_employees['create_time'] = $where_create_time;

        }else{
            $where_source['create_time'] = array('elt',$end_time);
            $where_industry['create_time'] = array('elt',$end_time);
            $where_renenue['create_time'] = array('elt',$end_time);
            $where_employees['create_time'] = array('elt',$end_time);
        }
        //dump($roleList);
		foreach($roleList as $v){
			$user = getUserByRoleId2($v['role_id']);
			if($user['position_id'] == '18' || $user['position_id'] == '20'){
				$pro['userIds'] = $user['user_parent_id'];
			}else{
				$pro['userIds'] = $user['user_id'];
			}
			$pro['startTime'] = $_GET['start_time'];
			$pro['endTime'] = $_GET['end_time'];
			$reportList[] = array("user"=>$user,"ordernum"=>productAna($pro));
//			G('end');
//			echo G('begin','end').'s';
		}
		$this->reportList = $reportList;
		$this->display();

	}


	public function fservice(){
		$this->assign('url',C('URL'));
		if(session('position_id') == 16 || session('position_id') == 19){
			$this->assign('userId',session('user_id'));
			$pro['userId'] = session('user_id');
		}else if(session('position_id') == 20){
			$this->assign('userId',session('parent_id'));
			$pro['userId'] = session('parent_id');
		}
		$pro = http_build_query($pro);
		$opts = array(
			'http' => array(
				'method' => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => $pro
			)
		);
		$context = stream_context_create($opts);
		$html = file_get_contents(C('URL').'/manager/community/getShopFee', false, $context);
		//echo (C('URL').'/manager/community/getShopFee');
		$html = json_decode($html,true);
		$this->assign('code',$html['code']);
		$this->assign('free',$html['data']['fee']);
		$this->display();
	}

	public function fserviceadd(){
		if(session('position_id') == 16 || session('position_id') == 19){
			$pro['userId'] = session('user_id');
			$this->assign('userId',session('user_id'));
		}else if(session('position_id') == 20){
			$pro['userId'] = session('parent_id');
			$this->assign('userId',session('parent_id'));
		}
		$pro = http_build_query($pro);
		$opts = array(
			'http' => array(
				'method' => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => $pro
			)
		);
		$context = stream_context_create($opts);
		$html = file_get_contents(C('URL').'/manager/community/getShopFee', false, $context);
		//echo (C('URL').'/manager/community/getShopFee');
		$html = json_decode($html,true);
		$this->assign('code',$html['code']);
		$this->assign('free',$html['data']['fee']);
		$this->assign('url',C('URL'));
		$this->display();
	}

	public function checkdelete(){
        $userids = $_POST['userid'];
        foreach ($userids as $val){
            $company = M('user')->where("user_id = '%s'",$val)->getField('company');
            if(session('company') !== $company){
                $this->ajaxReturn("","",1);
            }else{
                $this->ajaxReturn("","",0);
            }
        }
        //url+'/manager/trade/deleteService',
    }



    /**
     *管家券管理
     */
    public function manager(){
        if(session('position_id') == 19 || session('position_id') == 20 || session('position_id') == 16){
            $data=array();
            $data['values']=$_SESSION['user_id'];
            $data['keyWord']=$_GET['search'];
            //$data['serviceStatus']=1;
            $data['pageSize']=10;
            $data['start']=$_GET['p']==0?0:($_GET['p']-1)*$data['pageSize'];
            //print_r($data);exit;
            $list=send_post('http://192.168.1.207:8003/manager/ticket/queryTicketList',$data);
            //print_r($list);exit;
            $list=json_decode(json_encode($list),TRUE);
            $manager=$list['data']['list'];
            //print_r($manager);exit;
            import("@.ORG.Page");
            $Page = new Page($list['data']['count'],$data['pageSize']);				//实例化分页类 传入总记录数和每页显示的记录数
            $show = $Page->show();
            $this->assign('page',$show);
            $this->assign('position',session('position_id'));
            $this->assign('url',C('URl'));
            $this->assign('list',$manager);
            $this->display();
        }
    }


    /**
     * 添加管家券
     */
    public function addmanager(){
        if($this->isPost()){
            $data['userId']=$_POST['userId'];
            $data['serviceName']=$_POST['serviceName'];
            $data['servicePrice']=$_POST['now_price'];
            $data['ticketOriginalPrice']=$_POST['price'];
            $data['serviceUnit']=$_POST['serviceUnit'];
            $data['ticketIntroduce']=$_POST['goodsModel'];
            $data['serviceDetail']=$_POST['serviceDetail'];
            $data['ticketType']=$_POST['goodsType'];
            $data['isExpire']=$_POST['stop'];
            if($_POST['stop']==1) {
                $data['endTime'] = $_POST['start_time'];
            }
            $data['sumStockNum']=$_POST['discount'];
            $data['servicePictureArray']=$_POST['servicePictureArray'];
            $data['tradeClassHeadArrary']='[{"value":"5500000000","text":"电子券"}]';
            $data['ticketTypeName']='电子券';
            //print_r($data);exit;
            $status=send_post('http://192.168.1.207:8003/manager/ticket/saveCommunityTicket',$data);
            if ($status) {
                $this->ajaxReturn(1);
            }else {
                $this->ajaxReturn(0);
            }
        }
        $this->display();
    }



    /**
     * 编辑管家券
     */
    public function updatemanager(){
        $datas['sid']=$_GET['sid'];
        $datas['module']='community_ticket';
        $list=send_post('http://192.168.1.207:8003/manager/serviceGoods/getGoodsBase',$datas);
        $list=json_decode(json_encode($list),TRUE);
        $manager=$list['data']['resultMap'];
        $img=$list['data']['picList'];
        //print_r($manager);exit;
        if($this->isPost()){
            $data['sid']=$_POST['sid'];
            $data['userId']=$_POST['userId'];
            $data['serviceName']=$_POST['serviceName'];
            $data['servicePrice']=$_POST['now_price'];
            $data['ticketOriginalPrice']=$_POST['price'];
            $data['serviceUnit']=$_POST['serviceUnit'];
            $data['ticketIntroduce']=$_POST['goodsModel'];
            $data['serviceDetail']=$_POST['serviceDetail'];
            $data['ticketType']=$_POST['goodsType'];
            $data['isExpire']=$_POST['stop'];
            if($_POST['stop']==1) {
                $data['endTime'] = $_POST['start_time'];
            }
            $data['sumStockNum']=$_POST['discount'];
            $data['tradeClassHeadArrary']='[{"value":"5500000000","text":"电子券"}]';
            $data['servicePictureArray']=$_POST['servicePictureArray'];
            $data['ticketTypeName']='电子券';
            //print_r($_POST);exit;
            $status=send_post('http://192.168.1.207:8003/manager/ticket/updateCommunityTicket',$data);
            if ($status) {
                $this->ajaxReturn(1);
            }else {
                $this->ajaxReturn(0);
            }
        }
        $this->assign('sid',$_GET['sid']);
        $this->assign('list',$manager);
        $this->assign('img',$img);
        $this->display();
    }

    /**
     * 下架
     */
    public function upper(){
        if($_POST['id']){
            $data['serviceIds'] = $_POST['id'];
            $data['serviceStatus'] = 0;
            $status=send_post('http://192.168.1.207:8003/manager/trade/editorSoldOut',$data);
            if ($status) {
                return $this->ajaxReturn();
            }
        }else{
            R('Public/errjson',array('fail'));
        }
    }

    /**
     * 上架
     */
    public function onper(){
        if($_POST['id']){
            $data['serviceIds'] = $_POST['id'];
            $data['serviceStatus'] = 1;
            $status=send_post('http://192.168.1.207:8003/manager/trade/editorSoldOut',$data);
            if ($status) {
                return $this->ajaxReturn();
            }
        }else{
            R('Public/errjson',array('fail'));
        }
    }


    /**
     * 删除商品
     */
    public function del(){
        if($_POST['id']){
            $data['serviceIds'] = $_POST['id'];
            $status=send_post('http://192.168.1.207:8003/manager/trade/deleteService',$data);
            if ($status) {
                return $this->ajaxReturn();
            }
        }else{
            R('Public/errjson',array('fail'));
        }
    }
}