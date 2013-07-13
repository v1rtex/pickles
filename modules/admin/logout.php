<?php

namespace PICKLES\Modules;

class admin_logout extends \Module
{
	public function __default()
	{
		unset($_SESSION['__pickles']['admin']);
		\Browser::redirect('/admin/login');
	}
}

?>
