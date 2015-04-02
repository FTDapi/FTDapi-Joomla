<?php
/**
 * @package     Joomla.Site
 * @subpackage  Module.FTDapi
 * @version     1.9.3
 *
 * @copyright   Copyright (C) 2014-2015 tec-promotion GmbH. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 * @link        http://www.tec-promotion.de/
 * @author      Christian Hent <hent.dev@googlemail.com>
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/helper.php';

$result = ModFtdapiHelper::_getFtdData($params);

if (!empty($result))
{
	$data = array('items' => $result, 'mod_params' => $params);
	
	$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

	$path = JPath::clean(JPATH_ROOT .'/modules/mod_ftdapi/layouts');

	$layout_type = $params->get('layout');

	switch ($layout_type)
	{
		case 0:
			$ftdLayout  = new JLayoutFile('region', $path, array('suffixes' => array('ftd.mod'), 'debug' => false));
			break;
		case 1:
			$ftdLayout  = new JLayoutFile('provider', $path, array('suffixes' => array('ftd.mod'), 'debug' => false));
			break;
		case 2:
			$ftdLayout  = new JLayoutFile('truck', $path, array('suffixes' => array('ftd.mod'), 'debug' => false));
			break;
	}

	echo $ftdLayout->render($data);
}
