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

$app    = JFactory::getApplication();
$doc    = $app->getDocument();
$doc->addStyleSheet(JURI::root().'media/plg_content_ftdapi/css/ftd.css');
?>
<?php if ($displayData['plg_params']->get('show_list', 1)) : ?>
<div class="ftd-container plg">
    <h3 class="ftd-headline"><img class="headline-logo" src="<?php echo 'http://www.food-trucks-deutschland.de/' . $displayData['items'][0]['image'];?>" alt="<?php echo $item['name'];?>" > <?php echo $displayData['items'][0]['name'] ?> <?php echo $displayData['items'][0]['truck'] ?> - <?php echo JText::_('PLG_CONTENT_FTDAPI_HEADLINE');?></h3>
    <div id="ftd-summary">
        <ul>
            <?php if ($displayData['items'][0]['ftd'] === "true") : ?>
            <li class="approved"><img src="http://food-trucks-deutschland.de/assets/images/foodtrucks/logo-ftd-approved.png" alt="ftd approved logo"/></li>
            <?php endif; ?>
            <?php if ($displayData['items'][0]['nundso']) : ?>
            <li class="nundso">
                <a href="<?php echo $displayData['items'][0]['nundso'];?>" target="_blank" title="Nürnberg und so - Food Test <?php echo $displayData['items'][0]['name'];?>">
                <img src="http://food-trucks-deutschland.de/assets/images/foodtrucks/logo-nuernberg-und-so-food-test.png">
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
    <div class="ftd-items">
        <?php foreach ($displayData['items'] as $item) : ?>
        <div class="ftd-item media">
            <div class="media-body">
                <p>
                <?php if ($displayData['plg_params']->get('time_interval') === 'today') : ?>
            	<?php echo JText::_('PLG_CONTENT_FTDAPI_TODAY');?>
                <?php endif; ?>
                <?php if ($displayData['plg_params']->get('show_animations', 1) && $displayData['plg_params']->get('show_map', 1) ) : ?>
                <a id="<?php echo $item['tourid']; ?>" class="provider-truck plg"><?php echo JHtml::_('date', $item['startTime'], JText::_('PLG_CONTENT_FTDAPI_DATE_FORMAT_HOUR_MINUTE')); ?>&nbsp;<?php echo JText::_('PLG_CONTENT_FTDAPI_UNTIL');?>&nbsp;<?php echo JHtml::_('date', $item['endTime'], JText::_('PLG_CONTENT_FTDAPI_DATE_FORMAT_HOUR_MINUTE')); ?></a>
                <?php else: ?>
                <?php echo JHtml::_('date', $item['startTime'], JText::_('PLG_CONTENT_FTDAPI_DATE_FORMAT_HOUR_MINUTE')); ?>&nbsp;<?php echo JText::_('PLG_CONTENT_FTDAPI_UNTIL');?>&nbsp;<?php echo JHtml::_('date', $item['endTime'], JText::_('PLG_CONTENT_FTDAPI_DATE_FORMAT_HOUR_MINUTE')); ?>
                <?php endif; ?>
                <span class="boldify">
                <?php if ($displayData['plg_params']->get('show_gmaps_links', 1)) : ?>
                <a href="http://maps.google.com/maps?q=<?php echo $item['map']['latitude'];?>,<?php echo $item['map']['longitude'];?>" target="_blank"><?php echo $item['locationname'];?> (<?php echo $item['sponsorname'];?>)</a>
                <?php else: ?>
                <?php echo $item['locationname'];?>&nbsp;(<?php echo $item['sponsorname'];?>) 
                <?php endif; ?>   
                </span>
                </p>
                <p><?php echo JHtml::_('date', $item['startTime'], JText::_('PLG_CONTENT_FTDAPI_DATE_FORMAT_DAY_TEXT')); ?>&nbsp;<?php echo JHtml::_('date', $item['startTime'], JText::_('DATE_FORMAT_LC4'));  ?>&nbsp;-&nbsp;<?php echo $item['address']['full'];?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
<?php if ($displayData['plg_params']->get('show_map', 1)) : ?>
<?php 
    $path = JPath::clean(JPATH_ROOT .'/plugins/content/ftdapi/layouts/maps');
    $mapLayout  = new JLayoutFile($displayData['plg_params']->get('map_provider'), $path, array('suffixes' => array('ftd.plg'), 'debug' => false));
    $data = $displayData['plg_params'];
    echo $mapLayout->render($data);
?>
<?php endif; ?>
<div class="ftd-cowork">
<?php if ($displayData['plg_params']->get('show_animations', 1) && $displayData['plg_params']->get('show_map', 1) && $displayData['plg_params']->get('show_list', 1) ) : ?>
    <a id="ftdTop-plg">Zum Listenanfang</a> |
<?php endif; ?>
<?php echo JText::_('PLG_CONTENT_FTDAPI_SUBLINE');?> <img class="ftd-logo" src="http://www.nuernberg-und-so.de/assets/images/logo-food-trucks-in-deutschland.png" alt="Logo Food Trucks in Deutschland"/>
</div>
