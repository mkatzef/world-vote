<!DOCTYPE html>
@php
  $title_height_px = 40;
  $pane_width_perc = 25;
@endphp

<html>
  <head>
  	<meta charset="utf-8">
  	<title>Display a map on a webpage</title>
  	<meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no">
  	<link href="https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.css" rel="stylesheet">
  	<script src="https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.js"></script>
  	<style>
  		body { margin: 0; padding: 0; }
  		#map { position: absolute; top: {{ $title_height_px }}px; bottom: 0; left: {{ $pane_width_perc }}%; width: {{ 100 - $pane_width_perc }}%; }
      .pane { position: fixed; top: {{ $title_height_px }}px; width: {{ $pane_width_perc }}%; height:100% } //
  	</style>

    <link href="nouislider.css" rel="stylesheet">
    <script src="nouislider.js"></script>
  </head>

  <body>
    <iframe style="display:none" name="form_sink"></iframe>

    <!--
      TITLE
    -->
    <div id="title_bar" style="position:fixed; height:{{ $title_height_px }}px; width:100%">
      <div style="float: left;">
        Title
      </div>
      <div style="float: right;">
        <a href="/">About</a>
        <a href="/">Help</a>
        <a href="/">FAQ</a>
        <button onclick="button_new_vote()">New Vote</button>
        <button onclick="button_update_vote()">Update Vote</button>
      </div>
    </div>

  	<div id="pane_overlay" class="pane" style="background-color:#000000; opacity:0.2">
    </div>


    <!--
      DATA
    -->
  	<div id="data_control_pane" class="pane" style="visibility:hidden">
      <h1>Data</h1>
      <div id="stats-chart" style="display:flex; width:80%; height:80px">
      </div>

      <button>Wait for vote?</button>
      <button onclick="set_pane_mode('filter_pane')">Filters</button>

      <div style="display:flex; flex-direction:column">
        @foreach ($prompts as $prompt)
          <button onclick="stage_prompt({{ $prompt->id }})" style="border-radius:10px; margin:5px; padding-top:5px; padding-bottom:5px">
            {{ $prompt->caption }}
            @if($prompt->is_mapped)
              Mappable!
            @endif
          </button>
        @endforeach
      </div>

      <p>Demo data:</p>
  		<button onclick="toggle(0)">Demo 0</button>
  		<button onclick="toggle(1)">Demo 1</button>
    </div>


    <!--
      FILTERS
    -->
    <div id="filter_pane" class="pane" style="visibility:hidden">
      <h1>Filters</h1>
      <button onclick="set_pane_mode('data_control_pane')">Back</button><br>

      <p>Demo filter:</p>
      <div class="slidecontainer">
        <input type="range" min="0" max="10" value="0" class="slider"
        id="ranger" oninput="slide_func0()">
      </div>

      @foreach ($tags as $tag)
        {{ $tag->name }}<br>
        <div id="doubleslider-{{ $tag->slug }}" style="width:80%"></div>
        <br>
      @endforeach
    </div>


    <!--
      NEW
    -->
    <div id="new_vote_pane" class="pane" style="background-color:#ffffff; visibility:hidden">
      <h1>New Vote</h1>
      <button onclick="start_select_location()">Select Loc</button>
      <button onclick="attach_loc_to_form('new')">Confirm Loc</button>
      <form id="new_vote_form" action="/new_vote" method="POST"> <!--target="form_sink">-->
        @csrf
        <input type="number" id="new-row" name="grid_row" style="display:none">
        <input type="number" id="new-col" name="grid_col" style="display:none">
        <label>Select any tags for your vote:</label><br>
        @foreach ($tags as $tag)
          <input type="radio" id="{{ $tag->slug }}" name="{{ $tag->slug }}">{{ $tag->name }}</input><br>
        @endforeach
        <button>Submit</button>
      </form>
      <button onclick="set_pane_mode('data_control_pane')">Cancel</button>
    </div>


    <!--
      UPDATE
    -->
    <div id="update_vote_pane" class="pane" style="background-color:#ffffff; visibility:hidden">
      <h1>Update Vote</h1>
      <button onclick="start_select_location()">Select Loc</button>
      <button onclick="attach_loc_to_form('update')">Confirm Loc</button>
      <form id="update_vote_form" action="/update_vote" method="POST"> <!--target="form_sink">-->
        @csrf
        <label for="utoken">Unique token:</label><br>
        <input type="text" id="utoken" name="utoken"><br>
        <input type="number" id="update-row" name="grid_row" style="display:none">
        <input type="number" id="update-col" name="grid_col" style="display:none">
        <label>Select any tags for your vote:</label><br>
        @foreach ($tags as $tag)
          <input type="radio" id="{{ $tag->slug }}" name="{{ $tag->slug }}">{{ $tag->name }}</input><br>
        @endforeach
        <button>Submit</button>
      </form>
      <button onclick="set_pane_mode('data_control_pane')">Cancel</button>
    </div>


    <!--
      MAP
    -->
  	<div id="map"></div>


    <!--
      SCRIPT
    -->
  	<script>
      function stylizeDoubleSlider(sliderId) {
        var slider = document.getElementById("doubleslider-" + sliderId);
        noUiSlider.create(slider, {
            start: [0, 1],
            connect: true,
            step: 0.1,
            range: {
                'min': 0,
                'max': 1
            }
        });

        slider.noUiSlider.on('update', (e) => { console.log('FILTER TODO'); });
      }
      @foreach ($tags as $tag)
        stylizeDoubleSlider("{{ $tag->slug }}");
      @endforeach

  		mapboxgl.accessToken = 'pk.eyJ1IjoibWthdHplZmYiLCJhIjoiY2w1aTBqajB6MDNrOTNkcDRqOG8zZDRociJ9.5NEzcPb68a9KN04kSnI68Q';

      const pane_divs = [
        'pane_overlay',
        'data_control_pane',
        'filter_pane',
        'new_vote_pane',
        'update_vote_pane',
      ];
      function set_pane_mode(pane_mode) {
        // disable all divs that aren't pane_mode
        pane_divs.forEach((pane_id) => {
          if (pane_mode == pane_id) {
            document.getElementById(pane_id).style.visibility = 'visible';
          } else {
            document.getElementById(pane_id).style.visibility = 'hidden';
          }
        });
      }

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

        set_pane_mode('data_control_pane');
  		});

  		// Interactive map components
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

      var selected_xy = null;
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

      function start_select_location() {
  				set_up_select_ui();
  		}

      function attach_loc_to_form(form_prefix) {
        if (selected_xy == null) {
          alert("Please select a location on the map");
        } else {
          document.getElementById(form_prefix + '-col').value = selected_xy[0];
          document.getElementById(form_prefix + '-row').value = selected_xy[1];
          tear_down_select_ui();
        }
      }

      function end_select_location() {
        tear_down_select_ui();
      }

      function button_new_vote() {
        set_pane_mode('new_vote_pane');
      }

      function button_update_vote() {
        set_pane_mode('update_vote_pane');
      }

      new_vote_form.addEventListener('submit',
        function (e) {
          if (document.getElementById('new-row').value == null ||
              document.getElementById('new-col').value == null) {
            e.preventDefault();
            alert("Please confirm the location for your vote");
          } else {
            set_pane_mode('data_control_pane');
          }
        }
      );

      update_vote_form.addEventListener('submit',
        function (e) {
          set_pane_mode('data_control_pane');
        }
      );

      function displayStats(data) {
        const n_elems = data.length;
        var canvas = document.getElementById('stats-chart');
        const width = 100 / n_elems;

        canvas.innerHtml = '';  // Clear existing
        
        var new_bars = []
        for (let i = 0; i < n_elems; i++) {
          var bar_container = document.createElement("div");
          bar_container.style = "display:flex; width:" + width + "%; height:100%; background-color:#FFFFFF";

          var bar = document.createElement("div");
          bar.style = "background-color:#AAAAAA; width:100%; height:" + data[i] + "%; align-self:flex-end";

          bar_container.appendChild(bar);
          canvas.appendChild(bar_container);
        }

      }
      displayStats([10, 20, 10, 0, 20]);

  	</script>
  </body>
</html>
