<?php

namespace PICKLES\modules;

class admin extends \Module
{
	protected $template = 'admin';

	public function __default()
	{
		// Checks if we're logged in or not
		if (Security::hasLevel(SECURITY_LEVEL_USER))
		{
			if (Security::hasLevel(SECURITY_LEVEL_ADMIN))
			{
				$uri = '/admin/dashboard';
			}
			else
			{
				$uri = '/user/dashboard';
			}
		}
		else
		{
			$uri = '/user/login';
		}

		\Browser::redirect($uri);
	}
}

?>
