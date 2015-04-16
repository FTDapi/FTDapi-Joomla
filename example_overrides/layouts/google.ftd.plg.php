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
$doc->addScript('media/plg_content_ftdapi/js/oms.min.js');
?>
<script type="text/javascript">
var plgFTD = new Object();
ftd = jQuery.noConflict();
ftd(document).ready(function(){
	plgFTD.markers = [];
	plgFTD.markerClusterer = null;
	plgFTD.map = new google.maps.Map(document.getElementById('plgFTD-map'),{});
	plgFTD.bounds = new google.maps.LatLngBounds();
	plgFTD.oms = new OverlappingMarkerSpiderfier(plgFTD.map,{markersWontMove: true,markersWontHide: true,keepSpiderfied: true,circleSpiralSwitchover: 20,legWeight: 0});
	plgFTD.iw = new google.maps.InfoWindow();
	plgFTD.url = '<?php echo JURI::root() . "plugins/content/ftdapi/cache/cache.txt";?>';
	plgFTD.jqxhr = ftd.getJSON( plgFTD.url, function() {})
	.done(function(data) {
		if (plgFTD.markerClusterer){
			plgFTD.markerClusterer.clearMarkers();
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
			var popupString = '<p><?php echo JText::_("PLG_CONTENT_FTDAPI_ON");?> <strong>' + day + '</strong> <?php echo JText::_("PLG_CONTENT_FTDAPI_FROM");?> ' + '<strong>' + formattedStartTime + '</strong> <?php echo JText::_("PLG_CONTENT_FTDAPI_UNTIL");?> ' + '<strong>' + formattedEndTime +  '</strong><br/>' + val.address.full + '<br/>' + val.locationname + ' (' + val.sponsorname + ')' +'</p>';
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
			plgFTD.bounds.extend(marker.position);
			plgFTD.oms.addMarker(marker);
			plgFTD.markers.push(marker);
		});
		plgFTD.markerClusterer = new MarkerClusterer(plgFTD.map, plgFTD.markers, {
			maxZoom: 18,
			gridSize: 40,
			minClusterSize: 10
		});
		plgFTD.map.fitBounds(plgFTD.bounds);
		plgFTD.markerClusterer.setMap(plgFTD.map);
		// event handler
		plgFTD.oms.addListener('spiderfy', function() {
			plgFTD.iw.close();
		});
		plgFTD.oms.addListener('unspiderfy', function() {
			plgFTD.iw.close();
		});
		plgFTD.oms.addListener('click', function(marker, event) {
			plgFTD.iw.setContent(marker.desc);
			plgFTD.iw.open(plgFTD.map, marker);
		});
		ftd( "#plgFTD-mapreset" ).on( "click", function() {
			plgFTD.iw.close();
			plgFTD.map.fitBounds(plgFTD.bounds);
			plgFTD.markerClusterer.setMap(plgFTD.map);
		});

		<?php if ($displayData->get('show_animations', 1) && $displayData->get('show_list', 1) ) : ?>
		ftd( ".provider-logo.plg, .provider-truck.plg" ).on( "click", function() {
			ftd('html, body').animate({
				scrollTop: ftd("#plgFTD-map").offset().top
			}, 2000);
			var truckID = ftd(this).attr('id');
			var marker = findMarker(truckID);
			plgFTD.map.setCenter(marker.position);
			plgFTD.map.setZoom(18);
			plgFTD.iw.setContent(marker.desc);
			plgFTD.iw.open(plgFTD.map, marker);
			google.maps.event.trigger(marker, 'click');
			// helper
			function findMarker(id){
				for (var i = 0, len = ftd(plgFTD.markers).length; i < len; i++) {
					if (ftd(plgFTD.markers)[i].id === id) { 
						return ftd(plgFTD.markers)[i];
					}
				}
				return null;
			}
		});
		ftd( "#ftdTop-plg" ).on( "click", function() {
			ftd('html, body').animate({
				scrollTop: ftd(".ftd-container.plg").offset().top
			}, 2000);
		});
		<?php endif; ?>

	})
	.fail(function() {})
	.always(function() {});
});
</script>
<h5>google.ftd.plg Override</h5>
<div class="ftd-map-container">
    <p style="text-align: right;"><span><a class="btn btn-small" id="plgFTD-mapreset"><?php echo JText::_("PLG_CONTENT_FTDAPI_MAPRESET_BTN");?></a></span></p>
    <div id="plgFTD-map" style="height:<?php echo $displayData->get('map_height'); ?>px;"></div>
</div>
