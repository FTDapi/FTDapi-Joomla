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
$doc->addStyleSheet(JURI::root().'/media/plg_content_ftdapi/css/ftd.css');
?>
<h5>region.ftd.plg Override</h5>
<?php if ($displayData['plg_params']->get('show_list', 1)) : ?>
<div class="ftd-container plg">
    <h3 class="ftd-headline"><?php echo JText::_('PLG_CONTENT_FTDAPI_HEADLINE');?></h3>
    <div class="ftd-items">
    <?php foreach ($displayData['items'] as $item) : ?>
        <div class="ftd-item media">
            <img id="<?php echo $item['tourid']; ?>" class="pull-left provider-logo plg" src="<?php echo 'http://www.food-trucks-deutschland.de/' . $item['image'];?>" alt="<?php echo $item['name'];?>"/>
            <?php if ($item['nundso']) : ?>
                <a href="<?php echo $item['nundso'];?>" target="_blank" title="Nürnberg und so - Food Test <?php echo $item['name'];?>"><img class="pull-right" src="http://food-trucks-deutschland.de/assets/images/foodtrucks/logo-nuernberg-und-so-food-test.png" width="90" height="50"></a>
            <?php endif; ?>
            <div class="media-body">
                <p>
                <?php if ($displayData['plg_params']->get('time_interval') === 'today') : ?>
                <?php echo JText::_('PLG_CONTENT_FTDAPI_TODAY');?>
                <?php endif; ?>
                <span class="boldify">
                <?php echo JHtml::_('date', $item['startTime'], JText::_('PLG_CONTENT_FTDAPI_DATE_FORMAT_HOUR_MINUTE')); ?>&nbsp;<?php echo JText::_('PLG_CONTENT_FTDAPI_UNTIL');?>&nbsp;<?php echo JHtml::_('date', $item['endTime'], JText::_('PLG_CONTENT_FTDAPI_DATE_FORMAT_HOUR_MINUTE')); ?>
                <?php if ($displayData['plg_params']->get('show_gmaps_links', 1)) : ?>
                <a href="http://maps.google.com/maps?q=<?php echo $item['map']['latitude'];?>,<?php echo $item['map']['longitude'];?>" target="_blank"><?php echo $item['locationname'];?>&nbsp;(<?php echo $item['sponsorname'];?>)</a>
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
    $path   = JPath::clean(JPATH_ROOT .'/plugins/content/ftdapi/layouts/maps');
    $layout = new JLayoutFile($displayData['plg_params']->get('map_provider'), $path, array('suffixes' => array('ftd.plg'), 'debug' => false));
    $data   = $displayData['plg_params'];
    echo $layout->render($data);
?>
<?php endif; ?>
<div class="ftd-cowork">
<?php if ($displayData['plg_params']->get('show_animations', 1) && $displayData['plg_params']->get('show_map', 1) && $displayData['plg_params']->get('show_list', 1) ) : ?>
    <a id="ftdTop-plg">Zum Listenanfang</a> |
<?php endif; ?>
<?php echo JText::_('PLG_CONTENT_FTDAPI_SUBLINE');?>&nbsp;<img class="ftd-logo" src="http://www.nuernberg-und-so.de/assets/images/logo-food-trucks-in-deutschland.png" alt="Logo Food Trucks in Deutschland"/>
</div>
