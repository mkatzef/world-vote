<!DOCTYPE html>
<html>
  <head>
  	<meta charset="utf-8">
  	<title>Display a map on a webpage</title>
  	<meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no">
  	<link href="https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.css" rel="stylesheet">
  	<script src="https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.js"></script>
  	<style>
  		body { margin: 0; padding: 0; }
  		#map { position: absolute; top: 0; bottom: 0; left: 20%; width: 80%; }
  	</style>
  </head>

  <body>
  	<div id="control_overlay"
  		style="position:fixed; width:20%; height:100%; background-color:#000000;
  		opacity:0.5; z-index:1">
  	</div>

    @foreach ($tags as $tag)
      <p>{{ $tag->slug }}</p>
    @endforeach

  	<div style="width:20%">
  		Hi
  		<button onclick="toggle(0)">Demo 0</button>
  		<button onclick="toggle(1)">Demo 1</button>
  		<div class="slidecontainer">
  		  <input type="range" min="0" max="10" value="0" class="slider"
  			id="ranger" oninput="slide_func0()">
  		</div>
  		<button onclick="start_select_location()">Select Loc</button>
  	</div>

  	<div id="map"></div>

  	<script>
  		mapboxgl.accessToken = 'pk.eyJ1IjoibWthdHplZmYiLCJhIjoiY2w1aTBqajB6MDNrOTNkcDRqOG8zZDRociJ9.5NEzcPb68a9KN04kSnI68Q';
  	  const map = new mapboxgl.Map({
  	    container: 'map', // container ID
  	    style: 'mapbox://styles/mapbox/streets-v11', // style URL
  	    center: [-74.5, 40], // starting position [lng, lat]
  	    zoom: 1, // starting zoom
  	    projection: 'globe', // Alternative: 'mercator'
  			maxZoom: 6
  	  });

  	  map.on('style.load', () => {
  	    map.setFog({}); // Set the default atmosphere style
  	  });

  		map.on('load', () => {
  			map.addSource('vote-data', {
  					'type': 'vector',
  					'url': 'mapbox://mkatzeff.0cccfbxm'
  				});

  			for (let layer_num = 0; layer_num < 2; layer_num++) {
  				let data_name = 'demo' + layer_num;
  				let layer_id = 'vote-data-layer' + layer_num;
  				map.addLayer({
  					'id': layer_id,
  					'type': 'fill',
  					'source': 'vote-data',
  					'source-layer': 'cells',
  					'paint': {
  						'fill-color': [
  														"case",
  														["==", ["get", data_name], -1], 'rgba(0,0,0,0)', // transparent if demo0 == -1
  														["rgba", 255, 0, 0, ["get", data_name]]  // else red with demo0 as opacity
  													],
  						'fill-outline-color': 'rgba(0,0,0,0)'
  					},
  					'layout': {
  						'visibility': 'none'
  					}
  				});
  			}

  			map.addSource('clicked_loc', {
  					'type': 'geojson',
  					'data': {
  						'type': 'Feature',
  						'geometry': {
  							'type': 'Polygon',
  							'coordinates': [[]]
  						}
  					}
  				}
  			);

  			map.addLayer({
  				'id': 'clicked_loc_layer',
  				'type': 'fill',
  				'source': 'clicked_loc',
  				'layout': {},
  				'paint': {
  					'fill-color': '#ff0000',
  					'fill-opacity': 0.8
  				},
  				'layout': {
  					'visibility': 'none'
  				}
  			});

  			map.addSource('hover_loc', {
  					'type': 'geojson',
  					'data': {
  						'type': 'Feature',
  						'geometry': {
  							'type': 'Polygon',
  							'coordinates': [[]]
  						}
  					}
  				}
  			);

  			map.addLayer({
  				'id': 'hover_loc_layer',
  				'type': 'line',
  				'source': 'hover_loc',
  				'layout': {},
  				'paint': {
  					'line-color': '#000000',
  					'line-opacity': 0.5
  				},
  				'layout': {
  					'visibility': 'none'
  				}
  			});

  			control_overlay.remove();
  		});


  		// Interactive parts
  		var actives = [false, false];
  		function toggle(num) {
  			let layer_id = 'vote-data-layer' + num;

  			if (actives[num]) {
  				map.setLayoutProperty(layer_id, 'visibility', 'none');
  			} else {
  				map.setLayoutProperty(layer_id, 'visibility', 'visible');
  			}
  			actives[num] = !actives[num];
  		}

  		function slide_func0() {
  			let threshold = ranger.value / 10;
  			map.setPaintProperty(
  				'vote-data-layer0',
  				'fill-color',
  				[
  					"case",
  					[">", ["get", 'demo1'], threshold], 'rgba(0,0,0,0)',
  					["==", ["get", 'demo0'], -1], 'rgba(0,0,0,0)',
  					["rgba", 255, 0, 0, ["get", 'demo0']]
  				]
  			)
  		}

  		const maxZoom = 4;
  		const maxStepDeg = 15;
  		const zSteps = [0, 1, 2, 3, 4].map((i) => { return maxStepDeg / Math.pow(2, i) });
  		const oLng = -180;
  		const oLat = 90;
  		function get_xy(lngLat, zoom) {
  			const zStep = zSteps[zoom];
  			const col = Math.floor((lngLat.lng - oLng) / zStep);
  			const row = Math.floor((oLat - lngLat.lat) / zStep);
  			return [col, row];
  		}

  		/*
  		Returns array of coords for the cell containing the given lngLat at the
  		given zoom level.
  		Note: only valid for zooms in [0, maxZoom]
  		*/
  		function get_cell_coords(lngLat, zoom) {
  			const zStep = zSteps[zoom];
  			const xy = get_xy(lngLat, zoom);
  			const col = xy[0];
  			const row = xy[1];
  			const anc_lat = oLat + zStep * row;
  			const anc_lng = oLng + zStep * col;
  			return [
  				[anc_lng, anc_lat],
  				[anc_lng + zStep, anc_lat],
  				[anc_lng + zStep, anc_lat + zStep],
  				[anc_lng, anc_lat + zStep],
  				[anc_lng, anc_lat],
  			];
  		}

  		function display_clicked_cell(lngLat) {
  			map.getSource('clicked_loc').setData({
  				'type': 'Feature',
  				'geometry': {
  					'type': 'Polygon',
  					'coordinates': [get_cell_coords(lngLat, maxZoom)]
  				}
  			});

  			// In case touch was used, still display outlines
  			display_hover_cell(lngLat);
  		}

  		function display_hover_cell(lngLat) {
  			var features = Array(maxZoom);
  			for (let i = 0; i < maxZoom + 1; i++) {
  				features[i] = {
  					'type': 'Feature',
  					'geometry': {
  						'type': 'Polygon',
  						'coordinates': [get_cell_coords(lngLat, i)]
  					}
  				}
  			}

  			map.getSource('hover_loc').setData({
  				"type": "FeatureCollection",
  		    "features": features
  			});
  		}

  		function handler_clicked_cell(e) {
  			selected_xy = get_xy(e.lngLat, maxZoom);
  			display_clicked_cell(e.lngLat);
  		}

  		function handler_hover_cell(e) {
  			display_hover_cell(e.lngLat);
  		}

  		function set_up_select_ui() {
  			map.setLayoutProperty('clicked_loc_layer', 'visibility', 'visible');
  			map.setLayoutProperty('hover_loc_layer', 'visibility', 'visible');
  			map.on('mousemove', handler_hover_cell);
  			map.on('click', handler_clicked_cell);
  		}

  		function tear_down_select_ui() {
  			map.off('mousemove', handler_hover_cell);
  			map.off('click', handler_clicked_cell);
  			map.setLayoutProperty('clicked_loc_layer', 'visibility', 'none');
  			map.setLayoutProperty('hover_loc_layer', 'visibility', 'none');
  		}

  		var busy_selecting_loc = false;
  		var selected_xy = null;
  		function start_select_location() {
  			if (busy_selecting_loc) {
  				console.log('Ending select');
  				tear_down_select_ui();
  				busy_selecting_loc = false;
  				console.log("Final xy: " + selected_xy);
  			} else {
  				console.log('Starting select');
  				set_up_select_ui();
  				busy_selecting_loc = true;
  			}
  		}
  	</script>
  </body>
</html>
