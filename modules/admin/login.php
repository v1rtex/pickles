<?php

namespace PICKLES\Modules;

class admin_login extends admin
{
	public function __default()
	{
		// Checks if we're already logged in
		if (isset($_SESSION['__pickles']['admin']))
		{
			\Browser::redirect('/admin');
		}
	}
}

class admin_login_authenticate extends admin_login
{
	public function __default()
	{
		parent::__default();

		if (isset($_POST['username'], $_POST['password']) && !\String::isEmpty($_POST['username'], $_POST['password']))
		{
			// Looks up the username
			$admin = new \PICKLES\Models\Administrator(array('conditions' => array('username' => $_POST['username'])));

			if ($admin->count() == 1)
			{
				// Compares the hash
				$hash = explode('$', $admin->record['password']);

				if (count($hash) == 4)
				{
					$salt = substr($hash[3], 0, 22);
				}

				if (\Security::blowfish($_POST['password'], $salt) == $admin->record['password'])
				{
					$_SESSION['__pickles']['admin'] = $admin->record['id'];
					\Browser::redirect('/admin');
				}
			}

			$_SESSION['__notification'] = 'Invalid login credentials.';
		}
		else
		{
			$_SESSION['__notification'] = 'You must supply both a username and password to login.';
		}

		\Browser::redirect('/admin/login');
	}
}

?>
