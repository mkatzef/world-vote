<!DOCTYPE html>
@php
  $title_height_px = 35;
  $pane_width_perc = 25;
  $header_button_class = "bg-white hover:bg-orange-500 text-orange-700 font-semibold hover:text-white py-0 px-2 border border-orange-500 hover:border-transparent rounded"
@endphp

<html>
  <head>
  	<meta charset="utf-8">
  	<title>My World Vote</title>
    @guest
      <script src="https://www.google.com/recaptcha/enterprise.js?render=6LcwziwhAAAAAHOR6JERUohR4Z1FFJdSIUxUWSuT"></script>
    @endguest
    <!-- TODO: use alternative tailwind method -->
    <script src="https://cdn.tailwindcss.com"></script>
  	<meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no">
  	<link href="https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.css" rel="stylesheet">
  	<script src="https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.js"></script>
  	<style>
  		body { margin: 0; padding: 0; }
  		#map { position: absolute; top: {{ $title_height_px }}px; bottom: 0; left: {{ $pane_width_perc }}%; width: {{ 100 - $pane_width_perc }}%; }
      .pane { position:fixed; overflow-y:scroll; background-color:#AAAAAA; top: {{ $title_height_px }}px; bottom: 0px; width: {{ $pane_width_perc }}%; text-align:center } //
      .paneElement { position:fixed; top:0px; overflow-y:auto; width: 100%; height:100%; text-align:center } //
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
      <div style="float:left; width:{{ $pane_width_perc }}%; text-align:center">
        <a href="/">
          <img src="/logo.png" style="max-width:100%; max-height:{{ $title_height_px }}px"></img>
        </a>
      </div>

      <div style="float:right; height:{{ $title_height_px }}px">
        <button onclick="set_pane_mode('pane_polls')" class="{{ $header_button_class }}">
          Polls
        </button>
        <button onclick="set_pane_mode('pane_about')" class="{{ $header_button_class }}">
          About
        </button>
        @auth
          <button onclick="button_update_details()" class="{{ $header_button_class }}">
            My Details
          </button>
        @else
          <button onclick="button_login()" class="{{ $header_button_class }}">Returning voter</button>
          <button onclick="button_register()"
            class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-0 px-2 border border-orange-700 rounded">
            New voter
          </button>
        @endauth
      </div>
    </div>



    <div id="pane_container" class="pane">

    <!--
      OVERLAY
    -->
  	<div id="pane_overlay" style="position:absolute;width:100%;height:100%;background-color:#000000">
      <p style="color:#FFFFFF">Loading, please wait!</p>
    </div>

    <div id="pane_about" class="paneElement">
      <div
        class="block m-1 mt-5 p-2 bg-white rounded-lg border border-gray-200 shadow-md dark:bg-gray-800 dark:border-gray-700"
      >
        <h1>About</h1>
      </div>
      <div
        class="block m-1 mt-5 p-2 bg-white rounded-lg border border-gray-200 shadow-md dark:bg-gray-800 dark:border-gray-700"
      >
        Social media has a tendency to focus on the extremes.
        Here, everything is up to you &#128512;
      </div>
      <div
        class="block m-1 mt-5 p-2 bg-white rounded-lg border border-gray-200 shadow-md dark:bg-gray-800 dark:border-gray-700"
      >
          myworld.vote lets you express yourself along with your world
        <ul>
          <li>Honestly</li>
          <li>Anonymously</li>
          <li>Securely</li>
        </ul>
      </div>
      <div
        class="block m-1 mt-5 p-2 bg-white rounded-lg border border-gray-200 shadow-md dark:bg-gray-800 dark:border-gray-700"
      >
      We created myworld.vote to:
        <ul>
          <li>Give everyone a voice</li>
          <li>See how the world thinks</li>
          <li>Make this information visible to everyone</li>
        </ul>
      </div>
      <div
        class="block m-1 mt-5 p-2 bg-white rounded-lg border border-gray-200 shadow-md dark:bg-gray-800 dark:border-gray-700"
      >
        Written by <u><a href="http://www.katzef.com">Marc Katzef</a></u>
      </div>
    </div>

    <!--
      DATA
    -->
  	<div id="pane_polls" class="paneElement">
      <div
        class="block m-1 mt-5 p-2 bg-white rounded-lg border border-gray-200 shadow-md dark:bg-gray-800 dark:border-gray-700"
      >
        @auth
          <p>Your unique code is: <b>{{ auth()->user()->access_token }}</b></p>
        @endauth
        <div style="width:100%">
          <p id="staged-prompt-caption">Select a poll</p>
          <div>
            <span id="staged-prompt-option0" style="float:left; width:50%"></span>
            <span id="staged-prompt-option1" style="float:right; width:50%"></span>
          </div>
        </div>

        <div style="width:80%; margin-left:10%">
          <div id="stats-chart" style="display:flex; width:100%; height:40px; margin-bottom:5px"></div>
          @auth
          <form id="vote-form" action="/update_responses" method="POST" target="form_sink">
            @csrf
            <x-vote-slider :promptId="1" />
          </form>
          @endauth
        </div>
      </div>

      <div
        class="block m-1 mt-5 p-2 bg-white rounded-lg border border-gray-200 shadow-md dark:bg-gray-800 dark:border-gray-700"
      >
        <h5 class="mb-2 text-2xl font-bold tracking-tight text-orange-500 dark:text-white">
          Polls:
        </h5>
      </div>
      <div style="display:flex; flex-direction:column">
        @foreach ($prompts as $prompt)
          <button
            onclick="stage_prompt({{ $prompt }})"
            class="block m-1 p-2 bg-white rounded-lg border border-gray-200 shadow-md hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700"
          >

            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
              {!! $prompt->is_mapped ? "&#127757; " : "" !!}{{ $prompt->caption }}
            </h5>
            <p class="font-normal text-gray-700 dark:text-gray-400">
              {{ $prompt->option0 }} OR {{ $prompt->option1 }}
            </p>
          </button>
        @endforeach
      </div>

      <div id="captcha-container"></div>
      <div id="ad-container" style="top:0px; min-height:50px; bottom:0px; width:100%; background:white">
        <p>Big fat advertisement</p>
      </div>
    </div>


    <!--
      FILTERS
    -->
    <div id="pane_filters" class="paneElement" style="visibility:hidden">
      <h1>Filters</h1>
      <button onclick="set_pane_mode('pane_polls')">Back</button><br>

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
    <div id="pane_new_user" class="paneElement">
      <h1>New Vote</h1>
      <button onclick="set_up_select_ui('new')" class="{{ $header_button_class }}">
        Select location
      </button>

      <form id="new_vote_form" action="/new_vote" method="POST"> <!--target="form_sink">-->
        @csrf
        <input type="number" id="new-row" name="grid_row" style="display:none">
        <input type="number" id="new-col" name="grid_col" style="display:none">

        <h3 class="mb-5 text-lg font-medium text-gray-900 dark:text-white">Select any tags for your vote:</h3>
        <ul class="grid gap-6 w-full md:grid-cols-2">
          @foreach ($tags as $tag)
            <x-tag-checkbox :tag="$tag" prefix="new"/>
          @endforeach
        </ul>

        <button
          class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 border border-orange-700 rounded"
        >
          Submit
        </button><br>
        <input type="checkbox" name="remember_me">Remember me on this device</input>
      </form>
    </div>


    <!--
      UPDATE
    -->
    <div id="pane_my_details" class="paneElement">
      <h1>Update Vote</h1>
      <button onclick="set_up_select_ui('update')" class="{{ $header_button_class }}">
        Select location
      </button><br>
      <form id="update_details_form" action="/update_details" method="POST"> <!--target="form_sink">-->
        @csrf
        <input type="number" id="update-row" name="grid_row" style="display:none">
        <input type="number" id="update-col" name="grid_col" style="display:none">

        <h3 class="mb-5 text-lg font-medium text-gray-900 dark:text-white">Select any tags for your vote:</h3>
        <ul class="grid gap-6 w-full md:grid-cols-2">
          @foreach ($tags as $tag)
            <x-tag-checkbox :tag="$tag" prefix="update"/>
          @endforeach
        </ul>

        <button
          class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 border border-orange-700 rounded"
        >
          Submit
        </button><br>
        <input type="checkbox" name="remember_me"
        @auth
          {{ request()->cookie('access_token') ? 'checked' : '' }}
        @endauth
        >Remember me on this device</input>
      </form>
    </div>


    <!--
      LOGIN
    -->
    <div id="pane_login" class="paneElement">
      <h1>Login</h1>
      <form id="login_form" action="/login" method="POST"> <!--target="form_sink">-->
        @csrf
        <label for="utoken">Unique token:</label><br>
        <input
          id="access_token"
          name="access_token"
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
        >
        <button
          class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 border border-orange-700 rounded"
        >
          Submit
        </button>
      </form>
    </div>

    </div>

    <!--
      MAP
    -->
    <div id="map"></div>


  	<script>
      @guest
      grecaptcha.enterprise.ready(function() {
        grecaptcha.enterprise.execute('6LcwziwhAAAAAHOR6JERUohR4Z1FFJdSIUxUWSuT', {action: 'login'}).then(function(token) {
          // TODO: connect with user actions!
        });
      });
      @endguest
  		mapboxgl.accessToken = 'pk.eyJ1IjoibWthdHplZmYiLCJhIjoiY2w1aTBqajB6MDNrOTNkcDRqOG8zZDRociJ9.5NEzcPb68a9KN04kSnI68Q';

      const map = new mapboxgl.Map({
  	    container: 'map', // container ID
  	    style: 'mapbox://styles/mapbox/streets-v11', // style URL
  	    center: [-74.5, 40], // starting position [lng, lat]
  	    zoom: 1, // starting zoom
  	    projection: 'globe', // Alternative: 'mercator'
  			maxZoom: 6
  	  });

      /* Page layout */
      const pane_divs = [
        'pane_overlay',
        'pane_about',
        'pane_polls',
        'pane_filters',
        'pane_new_user',
        'pane_login',
        'pane_my_details',
      ];

      function set_pane_mode(pane_mode) {
        // Remove all map elements
        tear_down_select_ui();
        display_mapped_prompt(null);

        // disable all divs that aren't pane_mode
        pane_divs.forEach((pane_id) => {
          if (pane_mode == pane_id) {
            document.getElementById(pane_id).style.display = 'inline';
          } else {
            document.getElementById(pane_id).style.display = 'none';
          }
        });
      }

      function optimizeLayout() {
        if (window.innerWidth < 800) {
          document.getElementById('pane_container').style.top = "40%";
          document.getElementById('pane_container').style['margin-top'] = "{{ $title_height_px }}px";
          document.getElementById('pane_container').style.width = "100%";
          document.getElementById('map').style.height = "40%";
          document.getElementById('map').style.width = "100%";
          document.getElementById('map').style.left = "0px";
        } else {
          document.getElementById('pane_container').style.top = "{{ $title_height_px }}px";
          document.getElementById('pane_container').style['margin-top'] = "0px";
          document.getElementById('pane_container').style.width = "{{ $pane_width_perc }}%";
          document.getElementById('map').style.height = "";
          document.getElementById('map').style.width = "{{ 100 - $pane_width_perc }}%";
          document.getElementById('map').style.left = "{{ $pane_width_perc }}%";
        }
        map.resize();
      }

      addEventListener('resize', optimizeLayout);
      optimizeLayout();
      /* End of page layout */


  	  map.on('style.load', () => {
  	    map.setFog({}); // Set the default atmosphere style
  	  });

      /* MAP ON LOAD */
  		map.on('load', () => {
  			map.addSource('vote-data', {
					'type': 'vector',
					'url': "mapbox://{{ $tileset->mb_tile_id }}"
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
				});

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
				});

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

        set_pane_mode('pane_polls');
  		});
      /* END OF MAP ON LOAD */

      /*
        Mapbox's interpolation has numerical issues resulting in colors
        [min-eps, max+eps] which causes errors in colors [0, 255]
      */
      function SC(val) {
        return Math.max(0.1, Math.min(254.9, val));
      }

  		var activeLayerId = null;
  		function display_mapped_prompt(promptId, colorSteps) {
        if (activeLayerId != null) {
          map.removeLayer(activeLayerId);
        }
        if (promptId == null) {
          return;
        }

        const C = colorSteps;
        activeLayerId = 'layer-' + promptId;
        const dataId = 'prompt-' + promptId;
        map.addLayer({
          'id': activeLayerId,
          'type': 'fill',
          'source': 'vote-data',
          'source-layer': 'cells',
          'paint': {
            'fill-color':
              [
                "case",
                ["==", ["get", dataId], -1], 'rgba(0,0,0,0)', // transparent if -1
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

      // FILTER COMPONENTS
  		function demo_filter_func() {
        // TODO: remove/replace
  			let threshold = ranger.value / 10;
  			map.setPaintProperty(
  				'vote-data-layer0',
  				'fill-color', [
  					"case",
  					[">", ["get", 'demo1'], threshold], 'rgba(0,0,0,0)',
  					["==", ["get", 'demo0'], -1], 'rgba(0,0,0,0)',
  					["rgba", 255, 0, 0, ["get", 'demo0']]
  				]
  			)
  		}
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

        //slider.noUiSlider.on('update', (e) => { console.log('FILTER TODO'); });
      }
      /*
      foreach ($tags as $tag)
        stylizeDoubleSlider("{{ $tag->slug }}");
      endforeach
      */

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

      function handler_clicked_cell_wrapper(form_prefix) {
        return (e) => { handler_clicked_cell(form_prefix, e) };
      }

  		function handler_clicked_cell(form_prefix, e) {
        console.log("Handled");
        console.log(form_prefix);
        console.log(e);
  			selected_xy = get_xy(e.lngLat, maxZoom);
  			display_clicked_cell(e.lngLat);

        console.log(selected_xy[0]);
        console.log(selected_xy[1]);
        document.getElementById(form_prefix + '-col').value = selected_xy[0];
        document.getElementById(form_prefix + '-row').value = selected_xy[1];
  		}

  		function handler_hover_cell(e) {
  			display_hover_cell(e.lngLat);
  		}

      var currentHoverHandler = null;
      var currentClickHandler = null;
  		function set_up_select_ui(form_prefix) {
        tear_down_select_ui();  // remove any existing elements
  			map.setLayoutProperty('clicked_loc_layer', 'visibility', 'visible');
  			map.setLayoutProperty('hover_loc_layer', 'visibility', 'visible');
  			currentHoverHandler = handler_hover_cell;
        map.on('mousemove', handler_hover_cell);
        currentClickHandler = handler_clicked_cell_wrapper(form_prefix);
  			map.on('click', currentClickHandler);
  		}

  		function tear_down_select_ui() {
        if (currentHoverHandler != null) {
          map.off('mousemove', currentHoverHandler);
          map.setLayoutProperty('hover_loc_layer', 'visibility', 'none');
          currentHoverHandler = null;
        }
        if (currentClickHandler != null) {
          map.off('click', currentClickHandler);
          map.setLayoutProperty('clicked_loc_layer', 'visibility', 'none');
          currentClickHandler = null;
        }
  		}

      function button_register() {
        set_pane_mode('pane_new_user');
      }

      function button_login() {
        set_pane_mode('pane_login');
      }

      function button_update_details() {
        set_pane_mode('pane_my_details');

        // Set current user tags
        for (let i = 0; i < myTags.length; i++) {
          document.getElementById("update-checkbox-" + myTags[i]).checked = true;
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
          set_pane_mode('pane_polls');
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
          display_mapped_prompt(prompt.id, colorSteps);
        } else {
          display_mapped_prompt(null);
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

  @guest
    <style>
      .grecaptcha-badge {
       width: 70px !important;
       overflow: hidden !important;
       transition: all 0.3s ease !important;
       left: 4px !important;
      }
      .grecaptcha-badge:hover {
       width: 256px !important;
      }
    </style>
  @endguest

</html>
