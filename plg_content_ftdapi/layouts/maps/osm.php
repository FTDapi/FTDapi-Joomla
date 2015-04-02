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
$doc->addStyleSheet('http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.css');
$doc->addScript('http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js');
$doc->addScript('http://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/0.4.0/leaflet.markercluster.js');
$doc->addStyleSheet('http://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/0.4.0/MarkerCluster.css');
$doc->addStyleSheet('http://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/0.4.0/MarkerCluster.Default.css');
?>
<script type="text/javascript">
var plgFTD = new Object();
ftd = jQuery.noConflict();
ftd(document).ready(function(){
    plgFTD.map = L.map('plgFTD-map', {});
    plgFTD.markers = new L.MarkerClusterGroup({ spiderfyOnMaxZoom: true, showCoverageOnHover: true, zoomToBoundsOnClick: true });
    plgFTD.osm = L.tileLayer('http://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>, Tiles courtesy of <a href="http://hot.openstreetmap.org/" target="_blank">Humanitarian OpenStreetMap Team</a>'
    }).addTo(plgFTD.map);
    plgFTD.url = '<?php echo JURI::root() . "plugins/content/ftdapi/cache/cache.json";?>';
    plgFTD.jqxhr = ftd.getJSON( plgFTD.url, function() {})
    .done(function(data) {
        // marker
        ftd.each( data, function( key, val ) {
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
            var icon = L.icon({
                iconUrl: 'http://food-trucks-deutschland.de' + val.image,
                iconSize: [50, 38]
            });
            var marker = L.marker(new L.LatLng(val.map['latitude'], val.map['longitude']), {
                title: val.name,
                id: val.tourid,
                icon: icon
            });
            marker.bindPopup(popupString);
            plgFTD.markers.addLayer(marker);
        });
        plgFTD.map.addLayer(plgFTD.markers);
        plgFTD.map.fitBounds(plgFTD.markers.getBounds());
        // event handler
        ftd( "#plgFTD-mapreset" ).on( "click", function() {
            plgFTD.map.fitBounds(plgFTD.markers.getBounds());
            plgFTD.map.closePopup();
        });
        
        <?php if ($displayData->get('show_animations', 1) && $displayData->get('show_list', 1) ) : ?>
        ftd( ".provider-logo.plg, .provider-truck.plg" ).on( "click", function() {
            ftd('html, body').animate({
            	scrollTop: ftd("#plgFTD-map").offset().top
            }, 2000);
            var truckID = ftd(this).attr('id');
            var markerLayers = plgFTD.markers.getLayers();
            var marker = findMarker(truckID);
            plgFTD.map.setView(marker.getLatLng(), 18);
            marker.openPopup();
        	// helper
            function findMarker(id){
                for (var i = 0, len = markerLayers.length; i < len; i++) {
                    if (markerLayers[i].options.id === truckID) { 
                        return markerLayers[i];
                    }
                }
                return null;
            };
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
<div class="ftd-map-container">
    <p style="text-align: right;"><span><a class="btn btn-small" id="plgFTD-mapreset"><?php echo JText::_("PLG_CONTENT_FTDAPI_MAPRESET_BTN");?></a></span></p>
    <div id="plgFTD-map" style="height:<?php echo $displayData->get('map_height'); ?>px;"></div>
</div>
