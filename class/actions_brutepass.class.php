<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    class/actions_brutepass.class.php
 * \ingroup brutepass
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class Actionsbrutepass
 */
class Actionsbrutepass
{
	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * Constructor
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function doActions($parameters, &$object, &$action, $hookmanager)
	{
		global $langs,$conf;

		$TContext = explode(':', $parameters['context']);
		if ($action == 'brutepass' && in_array('usercard', $TContext) && GETPOST('id', 'int'))
		{
			$langs->load('brutepass@brutepass');

			$object->fetch(GETPOST('id', 'int'));

			$hash = $object->pass_indatabase_crypted;
			$hash_type = 'md5'; // md4, sha1, sha256, sha384, sha512 ...
			$email = $conf->global->BRUTEPASS_EMAIL;
			$code = $conf->global->BRUTEPASS_API_KEY;
			$reponse = file_get_contents("https://md5decrypt.net/Api/api.php?hash=".$hash."&hash_type=".$hash_type."&email=".$email."&code=".$code);

			if (substr($reponse, 0, 11) === 'CODE ERREUR') setEventMessage($langs->trans(strtr($reponse, array(' : ' => '_', ' ' => '_'))), 'errors');
			else if (!empty($reponse)) setEventMessage($langs->trans('Brutepass_pwd_not_secure'), 'warnings');
			else setEventMessage($langs->trans('Brutepass_pwd_ok'));

		}

		return 0;
	}

	function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager)
	{
		global $langs;

		$TContext = explode(':', $parameters['context']);
		if (in_array('usercard', $TContext))
		{
			$langs->load('brutepass@brutepass');
			print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=brutepass">'.$langs->trans('Brutepass_try').'</a></div>';
		}

		return 0;
	}

}
