<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use app\index\model\Teacher;

class Api extends Controller
{
	public function index()
	{
		return;
		$pageSize = 5;

		if (!$teachers = \my\StaticCache::get('teachers')) {
			$teachers = Teacher::select();
			\my\StaticCache::set('teachers', $teachers, 30);
		}

		$this->assign('teachers', $teachers);

		return $this->fetch();
		
	}
}