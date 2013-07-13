<?php

namespace PICKLES\modules;

class admin extends \Module
{
	public function __default()
	{
		// Checks if we're logged in or not
		if (!isset($_SESSION['__pickles']['admin']))
		{
			\Browser::redirect('/admin/login');
		}
		else
		{
			// TODO Pull stuff for the dashboard
			var_dump($_SESSION);
		}
	}
}

?>
