<?php
/*
# Joomla.Plugin - itcs date field
# ------------------------------------------------------------------------
# Author    it-conserv.de
# Copyright (C) 2022 it-conserv.de All Rights Reserved.
# License - GNU/GPLv3 <http://www.gnu.org/licenses/gpl-3.0.de.html>
# Websites: it-conserv.de
# ------------------------------------------------------------------------
*/ 

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

/**
 * Form Field class for itcs Extensions.
 * Provides a donation code check.
 */
class JFormFieldItcsDate extends FormField
{
	protected $type = 'itcsdate';

	protected function getInput()
	{

		$day = $this->value;

		//output
		$required	= $this->required ? ' required' : '';
		$class		= !empty($this->class) ? 'class="form-control ' . $this->class . '"' : 'class="form-control"';

            return 	'<input type="date" ' . $class . '  name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
					. htmlspecialchars($day, ENT_COMPAT, 'UTF-8') . $required . '"/>';
	}
}
