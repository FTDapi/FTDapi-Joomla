<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.FTDapi
 * @version     1.9.3
 *
 * @copyright   Copyright (C) 2014-2015 tec-promotion GmbH. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 * @link        http://www.tec-promotion.de/
 * @author      Christian Hent <hent.dev@googlemail.com>
 */

defined('_JEXEC') or die;

class PlgContentFtdapi extends JPlugin
{
	protected $autoloadLanguage = true;

	protected $ftdLayout;

	protected $placeholder = 'ftdapi';

	public function __construct(&$subject, $config, JRegistry $options = null, JHttp $http = null, JInput $input = null)
	{
		parent::__construct($subject, $config);

		$this->app = isset($app) ? $app : JFactory::getApplication();
		$this->options = isset($options) ? $options : new JRegistry;
		$this->http = isset($http) ? $http : new JHttp($this->options);
		$this->input = isset($input) ? $input : JFactory::getApplication()->input;

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$this->cacheFile = JFile::makeSafe('cache.json');
		$this->cacheFilePath = JPath::clean(JPATH_ROOT . '/plugins/content/ftdapi/cache/' . $this->cacheFile);

		$this->regexpr = '/{'.$this->placeholder.'.*?}/i';
	}

	public function onContentPrepare($context, &$row, &$params, $page = 0)
	{
		if (in_array($context, array('com_content.category','com_content.featured','com_content.archive')))
		{
			if ( JString::strpos( $row->text, '{'.$this->placeholder ) === false )
			{
				return true;
			}

			preg_match_all( $this->regexpr, $row->text, $matches );

			if ( !count( $matches[0] ) ) 
			{
				return true;
			}

			$row->text = preg_replace( $this->regexpr, '', $row->text);
		}

		if (!in_array($context, array('com_content.article')))
		{
			
			return true;
		}

		if (is_object($row))
		{
			if ($this->params->get('included_articles'))
			{
				$included_articles= explode("\r\n", $this->params->get('included_articles'));

				if (in_array((int)$row->id, $included_articles))
				{
					$ftdArray = $this->_getFtdData();

					if($ftdArray)
					{
						$row->FTD = $ftdArray;

						$path = JPath::clean(JPATH_ROOT .'/plugins/content/ftdapi/layouts');
						
						$layout = $this->params->get('layout');

						switch ($layout)
						{
							case 0:
								$this->ftdLayout  = new JLayoutFile('region', $path, array('suffixes' => array('ftd.plg'), 'debug' => false));
								break;
							case 1:
								$this->ftdLayout  = new JLayoutFile('provider', $path, array('suffixes' => array('ftd.plg'), 'debug' => false));
								break;
							case 2:
								$this->ftdLayout  = new JLayoutFile('truck', $path, array('suffixes' => array('ftd.plg'), 'debug' => false));
								break;
						}

						if ($this->params->get('output') == 3) 
						{
							if ( JString::strpos( $row->text, '{'.$this->placeholder ) === false )
							{
								return true;
							}

							preg_match_all( $this->regexpr, $row->text, $matches );

							if ( !count( $matches[0] ) ) 
							{
								return true;
							}

							$rowAsArray = JArrayHelper::fromObject($row, true,null);

							if (in_array($rowAsArray['FTD'], $rowAsArray))
							{
								if($this->ftdLayout)
								{
									$data = array('items' => $rowAsArray['FTD'], 'plg_params' => $this->params);

									$row->text = preg_replace( $this->regexpr, $this->ftdLayout->render($data), $row->text );
								}
							}
						}
						else 
						{
							 $row->text = preg_replace( $this->regexpr, '', $row->text );
						}
					}
				}
			}
		}
	}

	public function onContentAfterTitle($context, &$row, &$params, $page = 0)
	{
		if (!in_array($context, array('com_content.article')))
		{
			
			return false;
		}

		if (is_object($row))
		{	
			$rowAsArray = JArrayHelper::fromObject($row, true,null);
			
			if (in_array($rowAsArray['FTD'], $rowAsArray))
			{
        		if ($this->params->get('output') == 0)
				{
					if($this->ftdLayout)
					{
						$data = array('items' => $rowAsArray['FTD'], 'plg_params' => $this->params);

						return $this->ftdLayout->render($data);
					}
				}
			}
		}
	}

	public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
	{
		if (!in_array($context, array('com_content.article')))
		{
			
			return false;
		}

		if (is_object($row))
		{	
			$rowAsArray = JArrayHelper::fromObject($row, true,null);
			
			if (in_array($rowAsArray['FTD'], $rowAsArray))
			{
        		if ($this->params->get('output') == 1)
				{
					if($this->ftdLayout)
					{
						$data = array('items' => $rowAsArray['FTD'], 'plg_params' => $this->params);

						return $this->ftdLayout->render($data);
					}
				}
			}
		}
	}

	public function onContentAfterDisplay($context, &$row, &$params, $page = 0)
	{
		if (!in_array($context, array('com_content.article')))
		{
			
			return false;
		}

		if (is_object($row))
		{	
			$rowAsArray = JArrayHelper::fromObject($row, true,null);
			
			if (in_array($rowAsArray['FTD'], $rowAsArray))
			{
        		if ($this->params->get('output') == 2)
				{
					if($this->ftdLayout)
					{
						$data = array('items' => $rowAsArray['FTD'], 'plg_params' => $this->params);

						return $this->ftdLayout->render($data);
					}
				}
			}
		}
	}

	protected function _getFtdData()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		if(JFile::exists($this->cacheFilePath))
		{
			$fileinfos = stat($this->cacheFilePath);

			if ($fileinfos[9] > (time() - (60 * $this->params->get('cache'))))
			{
				if(!file_get_contents($this->cacheFilePath))
				{
					$app = JFactory::getApplication();
					$app->enqueueMessage(JText::_('Es ist ein Fehler aufgetreten'), 'notice');

					return false;
				}

				$result = json_decode(file_get_contents($this->cacheFilePath), true);

				if ($this->params->get('truck_id') != '0')
				{
					$result = $this->_search($result, 'truckid', $this->params->get('truck_id'));
				}

				return $result;	
			}
			else
			{
				$result =  $this->_getFtdDataFromApi();

				if ($result)
				{
					if ($this->params->get('truck_id') != '0')
					{
						$result = $this->_search($result, 'truckid', $this->params->get('truck_id'));
					}

					$this->_renewCache($result);

					return $result;
				}
			}
		}
		else 
		{
			$result =  $this->_getFtdDataFromApi();

			if ($result)
			{
				if ($this->params->get('truck_id') != '0')
				{
					$result = $this->_search($result, 'truckid', $this->params->get('truck_id'));
				}

				$this->_renewCache($result);

				return $result;
			}
		}
	}

	protected function _getFtdDataFromApi()
	{
        $plg = JPluginHelper::getPlugin('content','ftdapi');
        $plgParams = new JRegistry();
        
        $plgParams->loadString($plg->params);

        $api_url = 'http://www.food-trucks-deutschland.de/api/v12.php?tp=operatortour&tk=' . $plgParams->get('token') . '&dt=' . $plgParams->get('time_interval');

        try 
		{
			$response = $this->http->post($api_url, null);

			if ($response->code === 200)
			{
				$result = json_decode($response->body);
			
				if ($result->error)
				{
					$this->app->enqueueMessage(JText::_('PLG_CONTENT_FTDAPI_API_ERROR'), 'error');

					return false;
				}
				else
				{
					$result = $this->_arrayCastRecursive($result);
					
					return $result;
				}

			}
					
		} catch (Exception $e) 
		{
			$this->setError($e);
		}  
	}

	protected function _renewCache($data) 
	{
		
		file_put_contents($this->cacheFilePath, json_encode($data));
	}

	protected function _arrayCastRecursive($array)
	{
		if (is_array($array))
		{
			foreach ($array as $key => $value) 
			{
				if (is_array($value))
				{
					$array[$key] = $this->_arrayCastRecursive($value);
				}
				if ($value instanceof stdClass)
				{
					$array[$key] = $this->_arrayCastRecursive((array)$value);
				}
			}
		}

		if ($array instanceof stdClass)
		{
			return $this->_arrayCastRecursive((array)$array);
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
				$results = array_merge($results, $this->_search($subarray, $key, $value));
			}
		}

		return $results;
	} 
}
