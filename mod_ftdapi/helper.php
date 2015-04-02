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

abstract class ModFtdapiHelper
{
	
	public static function _getFtdData(&$params)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$module = JModuleHelper::getModule( 'mod_ftdapi' );
		$cacheFile = JFile::makeSafe($module->id.'.json');
		$cacheFilePath = JPath::clean(JPATH_ROOT . '/modules/mod_ftdapi/cache/' . $cacheFile);

		if(JFile::exists($cacheFilePath))
		{
			$fileinfos = stat($cacheFilePath);

			if ($fileinfos[9] > (time() - (60 * $params->get('cache'))))
			{
				if(!file_get_contents($cacheFilePath))
				{
					$app = JFactory::getApplication();
					$app->enqueueMessage(JText::_('MOD_FTDAPI_ERROR'), 'notice');

					return false;
				}

				$result = json_decode(file_get_contents($cacheFilePath), true);

				if ($params->get('truck_id') != '0')
				{
					$result = ModFtdapiHelper::_search($result, 'truckid', $params->get('truck_id'));
				}

				return $result;
				
			} else {

				$result =  ModFtdapiHelper::_getFtdDataFromApi($params);

				if ($result)
				{
					if ($params->get('truck_id') != '0')
					{
						$result = ModFtdapiHelper::_search($result, 'truckid', $params->get('truck_id'));
					}

					ModFtdapiHelper::_renewCache($result);

					return $result;
				}
			}
		}
		else 
		{
			$result =  ModFtdapiHelper::_getFtdDataFromApi($params);

			if ($result)
			{
				if ($params->get('truck_id') != '0')
				{
					$result = ModFtdapiHelper::_search($result, 'truckid', $params->get('truck_id'));
				}

				ModFtdapiHelper::_renewCache($result);

				return $result;
			}
		}

	}

	protected function _getFtdDataFromApi($params)
	{
        $app = isset($app) ? $app : JFactory::getApplication();
		$options = isset($options) ? $options : new JRegistry;
		$http = isset($http) ? $http : new JHttp($options);
		$input = isset($input) ? $input : JFactory::getApplication()->input;

        $api_url = 'http://www.food-trucks-deutschland.de/api/v12.php?tp=operatortour&tk=' . $params->get('token') . '&dt=' . $params->get('time_interval');

        try 
		{
			$response = $http->post($api_url, null);

			if ($response->code === 200)
			{
				$result = json_decode($response->body);
			
				if ($result->error)
				{
					$app->enqueueMessage(JText::_('MOD_FTDAPI_API_ERROR'), 'error');

					return false;
				}
				else
				{
					$result = ModFtdapiHelper::_arrayCastRecursive($result);
					
					return $result;
				}

			}
					
		} catch (Exception $e) 
		{
			$app->enqueueMessage(JText::_('MOD_FTDAPI_ERROR'), 'error');

			return false;
		}  
	}

	protected function _renewCache($data) 
	{
		$module = JModuleHelper::getModule( 'mod_ftdapi' );
		$cacheFile = JFile::makeSafe($module->id.'.json');
		$cacheFilePath = JPath::clean(JPATH_ROOT . '/modules/mod_ftdapi/cache/' . $cacheFile);

		file_put_contents($cacheFilePath, json_encode($data));
	}

	protected function _arrayCastRecursive($array)
	{
		if (is_array($array))
		{
			foreach ($array as $key => $value) 
			{
				if (is_array($value))
				{
					$array[$key] = ModFtdapiHelper::_arrayCastRecursive($value);
				}
				if ($value instanceof stdClass)
				{
					$array[$key] = ModFtdapiHelper::_arrayCastRecursive((array)$value);
				}
			}
		}

		if ($array instanceof stdClass)
		{
			return ModFtdapiHelper::_arrayCastRecursive((array)$array);
		}

		return $array;
	}

	protected function _search($array, $key, $value)
	{
		$results = array();
		if (is_array($array))
		{
			if (isset($array[$key]) && $array[$key] == $value)
			{
				$results[] = $array;
			}

			foreach ($array as $subarray)
			{
				$results = array_merge($results, ModFtdapiHelper::_search($subarray, $key, $value));
			}
		}

		return $results;
	}  
}