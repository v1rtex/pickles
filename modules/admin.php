<?php

namespace PICKLES\modules;

class admin extends \Module
{
	public function __default()
	{
		\Browser::redirect('/admin/login');
	}
}

?>
