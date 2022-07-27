<!DOCTYPE html>
@php
  $title_height_px = 50;
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
      .pane { position: fixed; top: {{ $title_height_px }}px; width: {{ $pane_width_perc }}%; height:100%; visibility:hidden; text-align:center } //
  	</style>

    <link href="nouislider.css" rel="stylesheet">
    <script src="nouislider.js"></script>
  </head>

  <body>
    <iframe style="display:none" name="form_sink"></iframe>

    <!--
      TITLE
    -->
    <div id="title_bar" style="position:fixed; height:{{ $title_height_px }}px; width:100%; background-color:#000000">
      <div style="float: left; width:{{ $pane_width_perc }}%; text-align:center">
        <a href="/">
          <img src="/logo.png" style="max-width:100%; max-height:{{ $title_height_px }}px"></img>
        </a>
      </div>
      <div style="float: right;">
        <button onclick="location.href='/'">About</button>
        <button onclick="location.href='/'">FAQ</button>
        @auth
          <button onclick="button_update_details()">My Details</button>
        @else
          <button onclick="button_login()">Returning voter</button>
          <button onclick="button_register()">New voter</button>
        @endauth
      </div>
    </div>


    <!--
      OVERLAY
    -->
  	<div id="pane_overlay" class="pane" style="background-color:#000000; visibility:visible">
    </div>


    <!--
      DATA
    -->
  	<div id="data_control_pane" class="pane">
      @auth
      <p>Your unique code is: <b>{{ auth()->user()->access_token }}</b></p>
      @endauth
      <div style="width:100%">
        <p id="staged-prompt-caption">Select a poll</p>
        <p id="staged-prompt-option0" style="float:left"></p>
        <p id="staged-prompt-option1" style="float:right"></p>
      </div>

      <div style="width:80%; margin-left:10%">
        <div id="stats-chart" style="display:flex; width:100%; height:40px; margin-bottom:5px"></div>
        @auth
        <form id="vote-form" action="/update_responses" method="POST" target="form_sink">
          @csrf
          <x-vote-slider :promptId="1" />
        </form>
        @endauth
        <!--
          <button>Wait for vote?</button>
          <button onclick="set_pane_mode('filter_pane')">Filters</button>
        -->
      </div>

      <p>Polls:</p>
      <div style="display:flex; flex-direction:column">
        @foreach ($prompts as $prompt)
          <button onclick="stage_prompt({{ $prompt }})" style="font-size:18pt; border-radius:10px; margin:5px; padding-top:5px; padding-bottom:5px">
            @if($prompt->is_mapped)
              &#127757;
            @endif
            {{ $prompt->caption }}
          </button>
        @endforeach
      </div>
    </div>


    <!--
      FILTERS
    -->
    <div id="filter_pane" class="pane" style="visibility:hidden">
      <h1>Filters</h1>
      <button onclick="set_pane_mode('data_control_pane')">Back</button><br>

      <!--
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
      -->
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
          <input type="checkbox" name="{{ $tag->slug }}">{{ $tag->name }}</input><br>
        @endforeach
        <button>Submit</button>
        <input type="checkbox" name="remember_me">Remember me on this device</input>
      </form>
      <button onclick="set_pane_mode('data_control_pane')">Cancel</button>
    </div>


    <!--
      UPDATE
    -->
    <div id="update_details_pane" class="pane" style="background-color:#ffffff; visibility:hidden">
      <h1>Update Vote</h1>
      <button onclick="start_select_location()">Select Loc</button>
      <button onclick="attach_loc_to_form('update')">Confirm Loc</button>
      <form id="update_details_form" action="/update_details" method="POST"> <!--target="form_sink">-->
        @csrf
        <input type="number" id="update-row" name="grid_row" style="display:none">
        <input type="number" id="update-col" name="grid_col" style="display:none">
        <label>Select any tags for your vote:</label><br>
        @foreach ($tags as $tag)
          <input type="checkbox" id="checkbox-{{ $tag->slug }}" name="{{ $tag->slug }}">{{ $tag->name }}</input><br>
        @endforeach
        <button>Submit</button>
        <input type="checkbox" name="remember_me"
        @auth
          {{ request()->cookie('access_token') ? 'checked' : '' }}
        @endauth
        >Remember me on this device</input>
      </form>
      <button onclick="set_pane_mode('data_control_pane')">Cancel</button>
    </div>


    <!--
      LOGIN
    -->
    <div id="login_pane" class="pane" style="background-color:#ffffff; visibility:hidden">
      <h1>Login</h1>
      <form id="login_form" action="/login" method="POST"> <!--target="form_sink">-->
        @csrf
        <label for="utoken">Unique token:</label><br>
        <input type="text" id="access_token" name="access_token">
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

        // TODO
        //slider.noUiSlider.on('update', (e) => { console.log('FILTER TODO'); });
      }
      /*
      @foreach ($tags as $tag)
        stylizeDoubleSlider("{{ $tag->slug }}");
      @endforeach
      */

  		mapboxgl.accessToken = 'pk.eyJ1IjoibWthdHplZmYiLCJhIjoiY2w1aTBqajB6MDNrOTNkcDRqOG8zZDRociJ9.5NEzcPb68a9KN04kSnI68Q';

      const pane_divs = [
        'pane_overlay',
        'data_control_pane',
        'filter_pane',
        'new_vote_pane',
        'login_pane',
        'update_details_pane',
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
  					'url': 'mapbox://mkatzeff.bwytfncw'
  				});

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

      function SC(val) {
        // Safe color
        return Math.max(0.1, Math.min(254.9, val));
      }

  		// Interactive map components
  		var activeLayerId = null;
  		function display_map_layer(dataId, colorSteps) {
        if (activeLayerId != null) {
          map.removeLayer(activeLayerId);
        }
        if (dataId == null) {
          return;
        }
        const C = colorSteps;
        activeLayerId =  'vote-layer-' + dataId;
        dataId = activeLayerId;
        map.addLayer({
          'id': activeLayerId,
          'type': 'fill',
          'source': 'vote-data',
          'source-layer': 'cells',
          'paint': {
            'fill-color': [
                            "case",
                            ["==", ["get", dataId], -1], 'rgba(0,0,0,0)', // transparent if demo0 == -1
                            ["rgba",
                              ["interpolate", ["linear"], ["get", dataId], 0, SC(C[0][0]), 0.5, SC(C[1][0]), 1, SC(C[2][0])],
                              ["interpolate", ["linear"], ["get", dataId], 0, SC(C[0][1]), 0.5, SC(C[1][1]), 1, SC(C[2][1])],
                              ["interpolate", ["linear"], ["get", dataId], 0, SC(C[0][2]), 0.5, SC(C[1][2]), 1, SC(C[2][2])],
                              0.5  // opacity
                            ]
                          ],
            'fill-outline-color': 'rgba(0,0,0,0)'
          },
          'layout': {
            'visibility': 'visible'
          }
        });
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

      function button_register() {
        set_pane_mode('new_vote_pane');
      }

      function button_login() {
        set_pane_mode('login_pane');
      }

      function button_update_details() {
        set_pane_mode('update_details_pane');

        // Set current user tags
        for (let i = 0; i < myTags.length; i++) {
          document.getElementById("checkbox-" + myTags[i]).checked = true;
        }
      }

      new_vote_form.addEventListener('submit',
        function (e) {
          if (document.getElementById('new-row').value == "" ||
              document.getElementById('new-col').value == "") {
            e.preventDefault();
            alert("Please confirm the location for your vote");
          }
        }
      );

      update_details_form.addEventListener('submit',
        function (e) {
          set_pane_mode('data_control_pane');
        }
      );

      // Note: barColors can be a different length than data - uses linear interpolation
      var currentBars = [];
      function displayStats(data, barColors) {
        const n_elems = data.length;
        const n_intervals = n_elems - 1;
        var canvas = document.getElementById('stats-chart');
        const width = 100 / n_elems;

        for (let i = 0; i < currentBars.length; i++) {
          canvas.removeChild(currentBars[i]);
        }
        currentBars = []
        var new_bars = []
        for (let i = 0; i < n_elems; i++) {
          var bar_container = document.createElement("div");
          bar_container.style = "display:flex; width:" + width + "%; height:100%; background-color:#FFFFFF";

          var color = colorLerp(i / n_intervals, barColors);
          var bar = document.createElement("div");
          bar.style = "background-color: rgb("+ color.join(',') +");" +
            "border-top-left-radius:5px; border-top-right-radius:5px; width:100%; height:" + 100*data[i] + "%; align-self:flex-end";

          bar_container.appendChild(bar);
          canvas.appendChild(bar_container);
          currentBars.push(bar_container);
        }
      }

      function colorLerp(weight, colorArr) {
        var n_colors = colorArr.length;
        var n_intervals = n_colors - 1;
        var n_passed = Math.floor(weight * n_intervals);
        var rgb1 = colorArr[n_passed];
        var rgb2 = colorArr[Math.ceil(weight * n_intervals)];
        var w2 = (weight * n_intervals) - n_passed;
        var w1 = 1 - w2;
        return [
          Math.round(rgb1[0] * w1 + rgb2[0] * w2),
          Math.round(rgb1[1] * w1 + rgb2[1] * w2),
          Math.round(rgb1[2] * w1 + rgb2[2] * w2),
        ];
      }

      function stage_prompt(prompt) {
        document.getElementById("staged-prompt-caption").innerText = prompt['caption'];
        document.getElementById("staged-prompt-option0").innerText = prompt['option0'];
        document.getElementById("staged-prompt-option1").innerText = prompt['option1'];
        var colorSteps = JSON.parse(prompt['colors']);
        if (colorSteps.length == 0)  {
          colorSteps = [[255, 0, 0], [255, 255, 255], [0, 255, 0]];
        }

        @auth
          // Slider colors
          document.getElementById("vote-slider-bg").style["background-image"] =
            "linear-gradient(to right, "+
            "rgb(" + colorSteps[0].join(',') + ")," +
            "rgb(" + colorSteps[1].join(',') + ")," +
            "rgb(" + colorSteps[2].join(',') + "))";

          slider = document.getElementById("vote-slider");
          // Visibility (note: hidden initially)
          document.getElementById("vote-slider-bg").style.display = 'block';
          slider.style.display = 'block';
          slider.name = prompt.id;
          slider.value = myResponses[prompt.id] ? myResponses[prompt.id] : prompt.n_steps / 2;
          slider.onmouseup = function () {
            myResponses[prompt.id] = slider.value;  // Record locally since last page load
            document.getElementById("vote-form").submit();
          }
        @endauth

        displayStats(JSON.parse(prompt['count_ratios']), colorSteps);
        if (prompt.is_mapped) {
          display_map_layer("" + prompt.id, colorSteps);
        } else {
          display_map_layer(null);
        }
      }

      @auth
        const myResponses = JSON.parse({{ Js::from(auth()->user()->responses) }});
        const myTags = JSON.parse({{ Js::from(auth()->user()->tags) }});
        document.getElementById("vote-slider-bg").style.display = 'none';
        document.getElementById("vote-slider").style.display = 'none';
      @endauth
  	</script>
  </body>
</html>
