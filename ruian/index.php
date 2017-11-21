<?php
$zoom=8;
$lat=49.7;
$lon=15.45;
if(isset($_REQUEST['zoom'])) $zoom=$_REQUEST['zoom'];
if(isset($_REQUEST['lat'])) $lat=$_REQUEST['lat'];
if(isset($_REQUEST['lon'])) $lon=$_REQUEST['lon'];
if(isset($_REQUEST['layer'])) $layer=$_REQUEST['layer'];
if (!is_numeric($zoom)) $zoom=8;
if (!is_numeric($lat)) $lat=49.7;
if (!is_numeric($lon)) $lon=15.45;
//echo is_numeric(strpos(strtolower($layer),'p'));
//die;
?>
<link rel="stylesheet" type="text/css" href="/leaflet/leaflet.css" />
<script type="text/javascript" src="/leaflet/leaflet.js"></script>
<div id="map" class="map" style="height: 100%">
<script>
function openBuilding(x) {
    window.open('/building.php?latlng='+x, 'Building', 'width=780, height=1200, resizable=yes, scrollbars=yes, location=no')};
function initmap() {
        var osmAttr = '<span>&copy;</span><a href="http://mapapi.poloha.net/copyright"> přispěvatelé OpenStreetMap</a>',
	    cuzkAttr = ' ČÚZK',
	    osmUrl='http://tile.poloha.net/{z}/{x}/{y}.png',
	    parcelyUrl = 'http://tile.poloha.net/parcely/{z}/{x}/{y}.png';
	    uliceUrl = 'http://tile.poloha.net/ulice/{z}/{x}/{y}.png';
	    budovyUrl = 'http://tile.poloha.net/budovy/{z}/{x}/{y}.png';
	    todobudovyUrl = 'http://tile.poloha.net/budovy-todo/{z}/{x}/{y}.png';
	    landuseUrl = 'http://tile.poloha.net/landuse/{z}/{x}/{y}.png';
	    adresyUrl = 'http://tile.poloha.net/adresy/{z}/{x}/{y}.png';

        var osm   = L.tileLayer(osmUrl, {minZoom: 0, maxZoom: 20, attribution: osmAttr}),
	    parcely = L.tileLayer(parcelyUrl, {minZoom: 12, maxZoom: 20, attribution: cuzkAttr}),
	    ulice = L.tileLayer(uliceUrl, {minZoom: 12, maxZoom: 20, attribution: cuzkAttr}),
	    budovy = L.tileLayer(budovyUrl, {minZoom: 12, maxZoom: 20, attribution: cuzkAttr}),
	    budovytodo = L.tileLayer(todobudovyUrl, {minZoom: 12, maxZoom: 20, attribution: cuzkAttr}),
	    landuse = L.tileLayer(landuseUrl, {minZoom: 12, maxZoom: 20, attribution: cuzkAttr}),
	    adresy = L.tileLayer(adresyUrl, {minZoom: 12, maxZoom: 20, attribution: cuzkAttr});

	var lpis = L.tileLayer.wms('http://eagri.cz/public/app/wms/plpis.fcgi', {
	    layers: 'LPIS_FB4,LPIS_FB4_KOD',
	    format: 'image/png',
	    transparent: true,
	    crs: L.CRS.EPSG4326,
	    attribution: "eagri.cz"
	});

	var km = L.tileLayer.wms('http://wms.cuzk.cz/wms.asp', {
	    layers: 'parcelni_cisla_i,DEF_BUDOVY,KN_I',
	    format: 'image/png',
	    transparent: true,
	    crs: L.CRS.EPSG4326,
	    minZoom: 17,
	    maxZoom: 20,
	    attribution: ' ČÚZK'
	});

	var ortofoto = L.tileLayer.wms('http://geoportal.cuzk.cz/WMS_ORTOFOTO_PUB/service.svc/get', {
	    layers: 'GR_ORTFOTORGB',
	    format: 'image/jpeg',
	    transparent: false,
	    crs: L.CRS.EPSG4326,
	    minZoom: 7,
	    maxZoom: 20,
	    attribution: cuzkAttr
	});

	var map = L.map('map', {
	    center: [<?php echo $lat.','.$lon;?>],
	    zoom: <?php echo $zoom;?>,
	    minZoom: 0,
	    maxZoom: 20,
	    layers: [osm
		    <?php 
		    if (is_numeric(strpos(strtolower($layer),'o'))) echo ",ortofoto";
		    if (is_numeric(strpos(strtolower($layer),'k'))) echo ",km";
		    if (is_numeric(strpos(strtolower($layer),'l'))) echo ",landuse";
		    if (is_numeric(strpos(strtolower($layer),'i'))) echo ",lpis";
		    if (is_numeric(strpos(strtolower($layer),'p'))) echo ",parcely";
		    if (is_numeric(strpos(strtolower($layer),'u'))) echo ",ulice";
		    if (is_numeric(strpos(strtolower($layer),'b'))) echo ",budovy";
		    if (is_numeric(strpos(strtolower($layer),'t'))) echo ",budovytodo";
		    if (is_numeric(strpos(strtolower($layer),'a'))) echo ",adresy";
		    ?>
	    ]
	});

	    map.attributionControl.setPrefix('');

	var overlays = {
	    "Ortofoto": ortofoto,
	    "KM": km,
	    "RÚIAN lands": landuse,
	    "LPIS": lpis,
	    "Parcely": parcely,
	    "Ulice": ulice,
	    "Budovy": budovy,
	    "Budovy-todo": budovytodo,
	    "Adresy": adresy
	};

	L.control.layers({},overlays).addTo(map);
	L.control.scale({imperial: false}).addTo(map);

map.on('click', function(e) {
    openBuilding(e.latlng);
});


}
initmap();
</script>
</div>
