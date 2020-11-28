<?php
ob_start();

require_once('Config.php');
require_once('Auth.php');

$dbh = new PDO('sqlite:orders.db');

$config = new PHPAuth\Config($dbh);
$auth = new PHPAuth\Auth($dbh, $config);

if(!$auth->isLogged()) {
	die('Access denied');
}

$action = $_REQUEST['action'];

switch($action) {
	case 'getOrders':
		
		$page = $_REQUEST['page'];
		$limit = $_REQUEST['rows'];
		$sidx = $_REQUEST['sidx'] ? $_REQUEST['sidx'] : 'id';
		$sord = $_REQUEST['sord'];
		$filters = $_REQUEST['filters'] ? json_decode($_REQUEST['filters']) : '';
		
		$query = $dbh->prepare("SELECT * FROM status");
        $query->execute();
		$status_all = array();
        while($status = $query->fetch(\PDO::FETCH_ASSOC)) {
			$status_all[$status['id']] = $status;
		}
		
		$query = $dbh->prepare("SELECT COUNT(*) AS cnt FROM orders");
        $query->execute();
		$orders = $query->fetch(\PDO::FETCH_ASSOC);
		$count = $orders['cnt'];
		
		$total_pages = 0;
		
		if($count > 0) {
			$total_pages = ceil($count / $limit);
		}
		
		if($page > $total_pages) {
			$page = $total_pages;
		}
		$start = $limit * $page - $limit;
		
		$response['page'] = $page;
		$response['total'] = $total_pages;
		$response['records'] = $count;
		
		if($sidx == 'status') {
			$sidx = 'status_name';
		}
		
		$where = '';
		if(is_array($filters->rules) && count($filters->rules) > 0) {
			$where .= "WHERE 1";
			foreach($filters->rules as $rule) {
				$where .= " AND o." . $rule->field . " LIKE '%" . $rule->data . "%'";
			}
		}
		
		$query = $dbh->prepare("SELECT o.*, s.name AS status_name, s.color AS status_color 
			FROM orders o
			LEFT JOIN status s
				ON o.status = s.id
			" . $where . "
			ORDER BY {$sidx} {$sord} LIMIT {$start}, {$limit}");
        $query->execute();
		
        while($order = $query->fetch(\PDO::FETCH_ASSOC)) {
			$order['date_create'] = date('d.m.y H:i', strtotime($order['date_create']));
			$order['status'] = '<span style="color:' . $status_all[$order['status']]['color'] . '">' . $status_all[$order['status']]['name'] . '</span>';
			$params = unserialize($order['params']);
			
			$order['params'] = '';
			foreach($params as $key => $val) {
				$order['params'] .= '<b>' . $key . ':</b> ' . urldecode($val) . '; ';
			}
			$order['params'] = trim($order['params']);
			
			$response['rows'][] = array(
				'id' => $order['id'],
				'cell' => $order,
			);
		}
		
		$response = json_encode($response);
		
		break;
	case 'getStatus':
		
		$id = intval($_REQUEST['id']);
		
		if($id > 0) {
			$query = $dbh->prepare("SELECT status FROM orders WHERE id = ?");
			$query->execute(array($id));
			$st = $query->fetch(\PDO::FETCH_ASSOC);
			$status_id = $st['status'];
			
			$query = $dbh->prepare("SELECT * FROM status ORDER BY id");
			$query->execute();
			
			$response .= '<select name="status">';
			while($status = $query->fetch(\PDO::FETCH_ASSOC)) {
				if($status['id'] == $status_id) {
					$selected = ' selected="selected"';
				}
				else {
					$selected = '';
				}
				$response .= '<option value="' . $status['id'] . '" style="color:' . $status['color'] . '"' . $selected . '>' . $status['name'] . '</option>';
			}
			$response .= '</select>';
		}
		else {
			$query = $dbh->prepare("SELECT * FROM status ORDER BY id");
			$query->execute();
			
			$response .= '<select name="status"><option value=""></option>';
			while($status = $query->fetch(\PDO::FETCH_ASSOC)) {
				$response .= '<option value="' . $status['id'] . '" style="color:' . $status['color'] . '">' . $status['name'] . '</option>';
			}
			$response .= '</select>';
		}
		
		break;
	case 'edit':
	
		$oper = trim($_REQUEST['oper']);
		$id = intval($_REQUEST['id']);
		
		if($id > 0) {
			if($oper == 'edit') {
				$status = $_REQUEST['status'];
				$comment = $_REQUEST['comment'];
				$address = $_REQUEST['address'];
				$upsell = $_REQUEST['upsell'];
				$email = $_REQUEST['email'];
				$phone = $_REQUEST['phone'];
				$name = $_REQUEST['name'];
				
				$query = $dbh->prepare("UPDATE orders SET status = ?, comment = ?, address = ?, upsell = ?, email = ?, phone = ?, name = ? WHERE id = ?");
				$query->execute(array($status, $comment, $address, $upsell, $email, $phone, $name, $id));
				
			}
			elseif($oper == 'del') {
				$query = $dbh->prepare("DELETE FROM orders WHERE id = ?");
				$query->execute(array($id));
			}
		}
		
		break;
}

ob_end_clean();

echo $response;
