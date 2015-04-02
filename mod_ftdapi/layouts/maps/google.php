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

JHtml::_('jquery.framework');

$app    = JFactory::getApplication();
$doc    = $app->getDocument();
$doc->addScript('https://maps.googleapis.com/maps/api/js?v=3');
$doc->addScript('http://google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclustererplus/src/markerclusterer_packed.js');
$doc->addScript('media/mod_ftdapi/js/oms.min.js');

$module = JModuleHelper::getModule( 'mod_ftdapi' );
$cacheFile = JFile::makeSafe($module->id.'.json');
$cacheFilePath = JPath::clean(JPATH_ROOT . '/modules/mod_ftdapi/cache/' . $cacheFile);
?>
<script type="text/javascript">
var modFTD = new Object();
ftd = jQuery.noConflict();
ftd(document).ready(function(){
	modFTD.markers = [];
	modFTD.markerClusterer = null;
	modFTD.map = new google.maps.Map(document.getElementById('modFTD-map'),{});
	modFTD.bounds = new google.maps.LatLngBounds();
	modFTD.oms = new OverlappingMarkerSpiderfier(modFTD.map,{markersWontMove: true,markersWontHide: true,keepSpiderfied: true,circleSpiralSwitchover: 20,legWeight: 0});
	modFTD.iw = new google.maps.InfoWindow();
	modFTD.url = '<?php echo JURI::root() . "/modules/mod_ftdapi/cache/" . $cacheFile;?>';
	modFTD.jqxhr = ftd.getJSON( modFTD.url, function() {})
	.done(function(data) {
		if (modFTD.markerClusterer){
			modFTD.markerClusterer.clearMarkers();
		}
		// marker
		ftd.each( data, function( key, val ) {
			var tourid = val.tourid;
			var startTime = new Date(parseInt(val.startTime*1000));
			var day = startTime.getDate() + "." + (startTime.getMonth() + 1) + "." + startTime.getFullYear();
			var starthours = startTime.getHours();
			var startminutes = "0" + startTime.getMinutes();
			var formattedStartTime = starthours + ':' + startminutes.substr(startminutes.length-2);
			var endTime = new Date(parseInt(val.endTime*1000));
			var endhours = endTime.getHours();
			var endminutes = "0" + endTime.getMinutes();
			var formattedEndTime = endhours + ':' + endminutes.substr(endminutes.length-2);
			var popupString = '<p><?php echo JText::_("MOD_FTDAPI_ON");?> <strong>' + day + '</strong> <?php echo JText::_("MOD_FTDAPI_FROM");?> ' + '<strong>' + formattedStartTime + '</strong> <?php echo JText::_("MOD_FTDAPI_UNTIL");?> ' + '<strong>' + formattedEndTime +  '</strong><br/>' + val.address.full + '<br/>' + val.locationname + ' (' + val.sponsorname + ')' +'</p>';
			var iconUrl = 'http://food-trucks-deutschland.de' + val.image;
			var image = {
				url: iconUrl,
				size: new google.maps.Size(50, 38),
				scaledSize: new google.maps.Size(50, 38),
				origin: new google.maps.Point(0,0)
			};
			var latLng = new google.maps.LatLng(val.map['latitude'], val.map['longitude'])
			var marker = new google.maps.Marker({position: latLng,draggable: false, icon: image, optimized: false});
			marker.id = tourid;
			marker.desc = popupString;
			modFTD.bounds.extend(marker.position);
			modFTD.oms.addMarker(marker);
			modFTD.markers.push(marker);
		});
		modFTD.markerClusterer = new MarkerClusterer(modFTD.map, modFTD.markers, {
			maxZoom: 18,
			gridSize: 40,
			minClusterSize: 10
		});
		modFTD.map.fitBounds(modFTD.bounds);
		modFTD.markerClusterer.setMap(modFTD.map);
		// event handler
		modFTD.oms.addListener('spiderfy', function() {
			modFTD.iw.close();
		});
		modFTD.oms.addListener('unspiderfy', function() {
			modFTD.iw.close();
		});
		modFTD.oms.addListener('click', function(marker, event) {
			modFTD.iw.setContent(marker.desc);
			modFTD.iw.open(modFTD.map, marker);
		});
		ftd( "#modFTD-mapreset" ).on( "click", function() {
			modFTD.iw.close();
			modFTD.map.fitBounds(modFTD.bounds);
			modFTD.markerClusterer.setMap(modFTD.map);
		});

		<?php if ($displayData->get('show_animations', 1) && $displayData->get('show_list', 1) ) : ?>
		ftd( ".provider-logo.mod, .provider-truck.mod" ).on( "click", function() {
			ftd('html, body').animate({
				scrollTop: ftd("#modFTD-map").offset().top
			}, 2000);
			var truckID = ftd(this).attr('id');
			var marker = findMarker(truckID);
			modFTD.map.setCenter(marker.position);
			modFTD.map.setZoom(18);
			modFTD.iw.setContent(marker.desc);
			modFTD.iw.open(modFTD.map, marker);
			google.maps.event.trigger(marker, 'click');
			// helper
			function findMarker(id){
				for (var i = 0, len = ftd(modFTD.markers).length; i < len; i++) {
					if (ftd(modFTD.markers)[i].id === id) { 
						return ftd(modFTD.markers)[i];
					}
				}
				return null;
			}
		});
		ftd( "#ftdTop-mod" ).on( "click", function() {
			ftd('html, body').animate({
				scrollTop: ftd(".ftd-container.mod").offset().top
			}, 2000);
		});
		<?php endif; ?>

	})
	.fail(function() {})
	.always(function() {});
});
</script>
<div class="ftd-map-container">
    <p style="text-align: right;"><span><a class="btn btn-small" id="modFTD-mapreset"><?php echo JText::_("MOD_FTDAPI_MAPRESET_BTN");?></a></span></p>
    <div id="modFTD-map" style="height:<?php echo $displayData->get('map_height'); ?>px;"></div>
</div>
