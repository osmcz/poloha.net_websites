<link rel="stylesheet" type="text/css" href="/leaflet/leaflet.css" />
<script type="text/javascript" src="/leaflet/leaflet.js"></script>
<div id="map" class="map" style="height: 300">
<script>
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

	var puvodnigeom = {
	    "type": "Feature", "geometry": <?php echo(pg_result($resmap,0,"geom"))?>
	};
	var puvodnistyle = {
	    "color": "#ff7800",
	    "weight": 4,
	    "opacity": 0.8,
	    "fillopacity": 0.5
	};

        var osm   = L.tileLayer(osmUrl, {minZoom: 0, maxZoom: 20, attribution: osmAttr}),
	    parcely = L.tileLayer(parcelyUrl, {minZoom: 12, maxZoom: 20, attribution: cuzkAttr}),
	    ulice = L.tileLayer(uliceUrl, {minZoom: 12, maxZoom: 20, attribution: cuzkAttr}),
	    budovy = L.tileLayer(budovyUrl, {minZoom: 12, maxZoom: 20, attribution: cuzkAttr}),
	    budovytodo = L.tileLayer(todobudovyUrl, {minZoom: 12, maxZoom: 20, attribution: cuzkAttr}),
	    landuse = L.tileLayer(landuseUrl, {minZoom: 12, maxZoom: 20, attribution: cuzkAttr}),
	    adresy = L.tileLayer(adresyUrl, {minZoom: 12, maxZoom: 20, attribution: cuzkAttr}),
	    origin = L.geoJSON(puvodnigeom, {style: puvodnistyle});

	var ortofoto = L.tileLayer.wms('http://geoportal.cuzk.cz/WMS_ORTOFOTO_PUB/service.svc/get', {
	    layers: 'GR_ORTFOTORGB',
	    format: 'image/jpeg',
	    transparent: false,
	    crs: L.CRS.EPSG4326,
	    minZoom: 7,
	    maxZoom: 20,
	    attribution: cuzkAttr
	});

	var overlays = {
	    "Ortofoto": ortofoto,
	    "RÚIAN lands": landuse,
	    "Parcely": parcely,
	    "Ulice": ulice,
	    "Budovy": budovy,
	    "Budovy-todo": budovytodo,
	    "Adresy": adresy,
	    "Origin": origin
	};

	var map = L.map('map', {
	    center: [<?php echo(pg_result($resmap,0,"y").",".pg_result($resmap,0,"x"))?>],
	    zoom: 
<?php $v_h=pg_result($resmap,0,"prozoom");
if ($v_h < 10)
    { echo "20"; }
elseif ($v_h < 30)
    { echo "19"; }
elseif ($v_h < 50)
    { echo "18"; }
elseif ($v_h < 100)
    { echo "17"; }
else
    { echo "16"; }
;
?>
, 
	    minZoom: 0,
	    maxZoom: 20,
	    layers: [osm,budovy,origin]
	});
        map.attributionControl.setPrefix('');

	L.control.layers({},overlays).addTo(map);
	L.control.scale({imperial: false}).addTo(map);
}
initmap();
</script>
</div>
