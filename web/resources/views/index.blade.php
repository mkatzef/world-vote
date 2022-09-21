<!DOCTYPE html>
@php
  $title_height_px = 45;
  $pane_width_perc = 30;
  $ad_width_perc = 0;
  $ad_height_px = 0;
  $compat_buttons_height_px = 35;
  $chart_height_px = 60;
  $header_button_class = "bg-white hover:bg-orange-500 text-orange-300 font-semibold hover:text-white py-1 px-4 border border-orange-400 hover:border-transparent rounded";
  $chart_n_elems = 12; // false but go with it to appease the html gods
@endphp

<html>
  <head>
  	<meta charset="utf-8">
  	<title>myworld.vote</title>
    @guest
      <script src="https://www.google.com/recaptcha/enterprise.js"></script>
    @endguest
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
  	<link href="https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.css" rel="stylesheet">
  	<script src="https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.js"></script>
  	<style>
  		body { margin: 0; padding: 0; }
  		#map { position: absolute; top: {{ $title_height_px }}px; bottom: 0; left: {{ $pane_width_perc }}%; width: {{ 100 - $pane_width_perc - $ad_width_perc }}%; }
      #navOverlay {
        position: fixed; /* Sit on top of the page content */
        display: none; /* Hidden by default */
        width: 100%; /* Full width (cover the whole page) */
        top: {{ $title_height_px }}px;
        left: 0;
        right: 0;
        background-color: rgb(255, 255, 255, 0.85);
        z-index: 2; /* Specify a stack order in case you're using a different order for other elements */
        cursor: pointer; /* Add a pointer on hover */
      }
      .scrolling-y {
        overflow-y: scroll;
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
      }
      .scrolling-y::-webkit-scrollbar {
        display: none;
      }

      .pane { position:fixed; background-color:#ffffff; top: {{ $title_height_px }}px; bottom: 0px; width: {{ $pane_width_perc }}%; text-align:center } //
      .paneElement { position:fixed; top:0px; width: 100%; height:100%; text-align:center } //

      .map_toggle_transition {
        -webkit-transition: all 0.35s ease;
        -moz-transition: all 0.35s ease;
        -o-transition: all 0.35s ease;
        -ms-transition: all 0.35s ease;
        transition: all 0.35s ease;
      }

      .main_transition {
        -webkit-transition: all 0.5s ease;
        -moz-transition: all 0.5s ease;
        -o-transition: all 0.5s ease;
        -ms-transition: all 0.5s ease;
        transition: all 0.5s ease;
      }

      .button_transition {
        -webkit-transition: all 0.25s ease;
        -moz-transition: all 0.25s ease;
        -o-transition: all 0.25s ease;
        -ms-transition: all 0.25s ease;
        transition: all 0.25s ease;
      }

      .folder_transition {
        overflow-y: hidden;
        -webkit-transition: all 0.35s ease;
        -moz-transition: all 0.35s ease;
        -o-transition: all 0.35s ease;
        -ms-transition: all 0.35s ease;
        transition: all 0.35s ease;
      }

      input[type="color"] {
         padding: 0;
         margin: 0;
         border: none;
         box-shadow: none;
         background: none;
         width: 25px;
       }

      input[type="color"]::-webkit-color-swatch {
        border: none;
        border-radius: 10px;
      }

      .slidecontainer {
        width: 100%; /* Width of the outside container */
      }

      /* The slider itself */
      .slider {
        -webkit-appearance: none;  /* Override default CSS styles */
        appearance: none;
        width: 100%; /* Full-width */
        height: 25px; /* Specified height */
        left: 25px;
        background: #d3d3d3; /* Grey background */
        border-bottom-left-radius:12px;
        border-bottom-right-radius:12px;
        outline: none; /* Remove outline */
        opacity: .6; /* Set transparency (for mouse-over effects on hover) */
        -webkit-transition: .2s; /* 0.2 seconds transition on hover */
        transition: opacity .2s;
        margin: 0;
      }

      /* Mouse-over effects */
      .slider:hover {
        opacity: .4; /* Fully shown on mouse-over */
      }

      /* The slider handle (use -webkit- (Chrome, Opera, Safari, Edge) and -moz- (Firefox) to override default look) */
      .slider::-webkit-slider-thumb {
        -webkit-appearance: none; /* Override default look */
        appearance: none;
        width: 25px; /* Set a specific slider handle width */
        height: 25px; /* Slider handle height */
        background: url('/arrows.png');
        cursor: pointer; /* Cursor on hover */
      }

      .slider::-moz-range-thumb {
        width: 25px; /* Set a specific slider handle width */
        height: 25px; /* Slider handle height */
        background: #000000; /* Green background */
        cursor: pointer; /* Cursor on hover */
      }

      input::-webkit-outer-spin-button,
      input::-webkit-inner-spin-button {
          -webkit-appearance: none;
          margin: 0;
      }
  	</style>
  </head>

  <body>
    <iframe style="display:none" name="form_sink"></iframe>

    <!--
      TITLE
    -->
    <div id="title_bar"
      style="position:fixed; height:{{ $title_height_px }}px; width:calc(100% - {{ $ad_width_perc }}%);
      background-color:#ffffff; margin-top:2px; margin-left:2px">
      <div style="float:left;max-width:70%;">
        <a href="/">
          <img id="logo_img" src="/logo-w.png" style="height:{{ $title_height_px - 4 }}px"></img>
        </a>
      </div>

      <div style="float:right">
        <a id="hammy" href="javascript:void(0)" onclick="hamburgerOpen()"
          style="display:none; margin-top:10px; height:{{ $title_height_px }}px; margin-right:10px">
          <div class="space-y-2">
            <div class="w-8 h-0.5" style="background-color:black"></div>
            <div class="w-8 h-0.5" style="background-color:black"></div>
            <div class="w-8 h-0.5" style="background-color:black"></div>
          </div>
        </a>

        <div id="title_buttons" style="display:none">
          <button
            id="title_bar_pane_polls"
            style="margin:2px"
            onclick="set_pane_mode('pane_polls')" class="{{ $header_button_class }}">
            Polls
          </button>
          @auth
            <button
              id="title_bar_pane_create_poll"
              style="margin:2px"
              onclick="set_pane_mode('pane_create_poll')" class="{{ $header_button_class }}">
              New Poll
            </button>
          @endauth
          <button
            id="title_bar_pane_about"
            style="margin:2px"
            onclick="set_pane_mode('pane_about')" class="{{ $header_button_class }}">
            About
          </button>
          @auth
            </button>
            <button
              id="title_bar_pane_my_details"
              style="margin:2px"
              onclick="button_update_details()" class="{{ $header_button_class }}">
              My Details
            </button>
            <a href="/logout">
              <button
                style="margin:2px 10px 2px 2px"
                class="{{ $header_button_class }}">
                Logout
              </button>
            </a>
          @else
            <button onclick="set_pane_mode('pane_user_type')"
              style="margin:2px 10px 2px 2px"
              class="bg-orange-300 hover:bg-orange-500 text-white font-bold py-1 px-4 border border-orange-400 rounded">
              Vote!
            </button>
          @endauth
        </div>
      </div>

      <div id="vert_options" style="display:none; margin:9px 16px 0 0; height:{{ $title_height_px }}px; float:right">
        <a href="javascript:void(0)" onclick="changeMapSize(-1)">
          <button
            class="bg-orange-300 hover:bg-orange-500 text-white"
            style="width:28px; margin-right:-6px; border-radius:14px 0 0 14px"
          >
            -
          </button>
        </a>
        <a href="javascript:void(0)" onclick="changeMapSize(0)">
          <button
            class="bg-orange-300 hover:bg-orange-500 text-white tracking-tight"
            style="width:40px"
          >
            Map
          </button>
        </a>
        <a href="javascript:void(0)" onclick="changeMapSize(1)">
          <button
            class="bg-orange-300 hover:bg-orange-500 text-white"
            style="width:28px; margin-left:-6px; border-radius:0 14px 14px 0"
          >
            +
          </button>
        </a>
      </div>
    </div>

    <div id="navOverlay">
      <p>
        <a id="hammy_pane_polls" href="javascript:void(0)" onclick="set_pane_mode('pane_polls')"
         class="text-2xl m-2"
         style="color:black">
          Polls
        </a>
      </p>
      @auth
      <hr>
      <p>
        <a id="hammy_pane_create_poll" href="javascript:void(0)" onclick="set_pane_mode('pane_create_poll')"
        class="text-2xl m-2"
        style="color:black">
          New Poll
        </a>
      </p>
      @endauth
      <hr>
      <p>
        <a id="hammy_pane_about" href="javascript:void(0)" onclick="set_pane_mode('pane_about')"
        class="text-2xl m-2"
        style="color:black">
          About
        </a>
      </p>
      <hr>
      @auth
      <p>
        <a id="hammy_pane_my_details" href="javascript:void(0)" onclick="button_update_details()"
        class="text-2xl m-2"
        style="color:black">
          My Details
        </a>
      </p>
      <hr>
      <p>
        <a id="hammy_pane_logout" href="/logout"
        class="text-2xl m-2"
        style="color:black">
          Logout
        </a>
      </p>
      @else
      <p>
        <a id="hammy_pane_user_type" href="javascript:void(0)" onclick="set_pane_mode('pane_user_type')"
        class="text-2xl m-2"
        style="color:black">
          Vote!
        </a>
      </p>
      @endauth
    </div>

    <div id="pane_container" class="pane main_transition">

    <!--
      OVERLAY
    -->
  	<div id="pane_overlay"
      style="position:absolute; width:100%; height:100%; background-color:white">
    </div>

    <div id="pane_about" class="paneElement scrolling">
      <div class="scrolling-y" style="height:100%">
        <div
          class="block m-2 mt-5 p-2 bg-white rounded-lg border border-gray-200 shadow-md"
        >
          <h3 class="text-lg font-medium text-gray-900">
            About
          </h3><hr>
          <div class="py-2">
            Social media likes to put us into opposing political boxes;
            <i>people are more complex than that.</i>
          </div>
        </div>
        <div
          class="block m-2 p-2 bg-white rounded-lg border border-gray-200 shadow-md"
        >
          <h3 class="text-lg font-medium text-gray-900">
            Purpose
          </h3><hr>
          <div class="py-2">
            I created <span style="color:orange">myworld.vote</span> to:
          </div>
          <div class="hover:bg-gray-200 m-1" style="border-radius:10px">
            See how the world thinks
          </div>
          <div class="hover:bg-gray-200 m-1" style="border-radius:10px">
            Compare opinions on individual topics
          </div>
          <div class="hover:bg-gray-200 m-1" style="border-radius:10px">
            Make this information visible to everyone
          </div>
        </div>
        <div
          class="block m-2 p-2 bg-white rounded-lg border border-gray-200 shadow-md"
        >
          <h3 class="text-lg font-medium text-gray-900">
            Promise
          </h3><hr>
          <div class="py-2">
            <span style="color:orange">myworld.vote</span> lets you express yourself:
          </div>
          <div class="hover:bg-gray-200" style="border-radius:10px">
            <span class="font-bold">
              Securely<br>
            </span>
            Using network security standard practices
          </div>
          <div class="hover:bg-gray-200" style="border-radius:10px">
            <span class="font-bold">
              Anonymously<br>
            </span>
            With only the bare minimum information recorded
          </div>
          <div class="hover:bg-gray-200" style="border-radius:10px">
            <span class="font-bold">
              Honestly<br>
            </span>
            Without being singled out - votes are always counted in batches
          </div>
        </div>
        <div
          class="block m-2 p-2 bg-white rounded-lg border border-gray-200 shadow-md"
        >
          <h3 class="text-lg font-medium text-gray-900">
            Author
          </h3><hr>
          <div class="py-2">
            Written by <u><a href="http://www.katzef.com">Marc Katzef</a></u><br>
            Law data sourced from <u><a href="https://worldpopulationreview.com/">World Population Review</a></u><br>
            To keep the stats server running:
          </div>

          <div class="mt-2">
            <form action="https://www.paypal.com/donate" method="post" target="_top">
              @csrf
              <input type="hidden" name="business" value="YMK37RQB69REG" />
              <input type="hidden" name="no_recurring" value="1" />
              <input type="hidden" name="item_name" value="To keep myworld.vote active" />
              <input type="hidden" name="currency_code" value="AUD" />
              <input type="image" src="https://www.paypalobjects.com/en_AU/i/btn/btn_donateCC_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" />
              <img alt="" border="0" src="https://www.paypal.com/en_AU/i/scr/pixel.gif" width="1" height="1" />
            </form>
          </div>
        </div>
      </div>
    </div>

    <!--
      Polls
    -->
  	<div id="pane_polls" class="paneElement">
      <div
        style="height:50px"
        class="block rounded-t-lg border-0 shadow-md"
      >
        <button id="poll_tab_vote_button" onclick="set_pane_poll_mode('votes')"
          class="mb-0 bg-white text-orange-300 text-xl font-semibold tracking-tight rounded-t-lg"
          style="height:100%; width:50%; float:left;
            border-top-width:2px; border-right-width:2px;">
            <span id="votes_indicator" style="visibility:hidden">•</span>
            Votes
            <span style="visibility:hidden">•</span>
        </button>
        <button id="poll_tab_voter_button" onclick="set_pane_poll_mode('voters')"
          class="mb-0 bg-orange-300 hover:bg-orange-500 text-white text-xl font-semibold tracking-tight rounded-t-lg"
          style="height:100%; width:50%; float:right;
            border-top-width:2px; border-left-width:2px">
            <span id="voters_indicator" style="visibility:hidden">•</span>
            Voters<span style="padding-right:20px"></span>
        </button>
      </div>

    <div style="background-color:#FF9D47; height:100%; width:100%"><!-- Cosmetic -->
      <div
        id="poll_tab_votes"
        style="height:calc(100% - {{ $ad_height_px }}px)"
      >
        <div
          id="prompt_scolling_div"
          onscroll="autoNextPage()"
          class="scrolling-y"
          style="height:calc(100% - 50px - {{ $compat_buttons_height_px }}px); display:flex; flex-direction:column;
            background-color:white; border-top-right-radius:5px">
          <div
            class="block bg-white rounded-lg shadow-md p-2 m-2 border-2 border-gray-200 grid place-items-center"
          >
            @auth
              <div style="width:100%; text-align:center">
                <h3 class="text-lg font-medium text-gray-900">
                  Your login code is:
                </h3>
                <button
                  onclick="code_copy_msg.innerText='✅&nbsp;';navigator.clipboard.writeText('{{ auth()->user()->access_token }}')"
                  class="rounded-lg shadow-m hover:bg-gray-100 px-1">
                  <div>
                    <img src="/copy.png" style="float:left; height:16px; width:16px; margin:4px"></img>
                    <b style="float:right">{{ auth()->user()->access_token }} <span id="code_copy_msg"></span></b>
                  </div>
                </button>
              </div>
            @else
              <h3 class="text-lg font-medium text-gray-900 px-1">
                Click on a question below to see the responses from <b>over {{ $n_voters }} users</b>!
              </h3>
            @endauth

            Last updated: {{ $last_updated->diffForHumans() }}
          </div>

          <div style="margin:5px">
            Sort by:
            <select style="width:150px" id='prompt_sort_key' onchange="setPromptOrder()">
              <option value="id">Date added</option>
              <option value="n_votes">Number of votes</option>
            </select>
            <button id="prompt_sort_order" data-val="asc" onclick="togglePromptOrder()"
              style="border-width:1px; width:110px; border-radius:5px; margin-left:10px"
            >
              <span id="prompt_sort_order_label">Ascending</span>
            </button>
          </div>

          <div id="prompt_content">
            @if($is_query)
              <span style="margin-top:10px">Showing individual prompt</span>
              <button onclick="setPromptOrder()" style="padding:0 5px 0 5px; border-width:1px; border-radius:5px">Show all<button>
            @endif
          </div>

          <button id="prompt_next_button" onclick="nextPage()"
            style="margin:10px; {{ $is_query ? "display:none" : "" }}"
          >Next</button>
        </div>

        <div id="compat_button_bar" style="background-color:#f0f0f0; height:{{ $compat_buttons_height_px }}px">
          Compatibility:
          <button
            type="button"
            style="width:60px; margin-top:5px; margin-bottom:5px"
            class="bg-orange-300 hover:bg-orange-500 text-white font-bold border border-orange-400 rounded"
            @auth
              onclick="jumpToCompat('vote')"
            @else
              onclick="set_pane_mode('pane_user_type')"
            @endauth
          >Votes</button>
          <button
            type="button"
            style="width:60px; margin-top:5px; margin-bottom:5px"
            class="bg-orange-300 hover:bg-orange-500 text-white font-bold border border-orange-400 rounded"
            @auth
              onclick="jumpToCompat('law')"
            @else
              onclick="set_pane_mode('pane_user_type')"
            @endauth
          >Laws</button>
          <button style="float:right" onclick="closeCompatButtons()">×&nbsp;</button>
        </div>
      </div>

      <div
        id="poll_tab_voters"
        class="scrolling-y"
        style="height:calc(100% - 50px - {{ $ad_height_px }}px); display:flex; flex-direction:column;
          display:none; background-color:white; border-top-left-radius:5px">

        <div
          class="block bg-white rounded-lg shadow-md p-2
            m-2 border-2 border-gray-200"
        >
          <h3 class="text-lg font-medium text-gray-900">
            Voter demographics
          </h3>
          View info about voters around the world!
        </div>

        <div>
          <a id="voter-folder-button-general"
            href="javascript:void(0)"
            onclick="openVoterFolder('general')"
          >
            <x-folder-handle slug="general" name="General" />
          </a>
          <div
            id='voter-folder-general'
            class="folder_transition"
            style="max-height:801px"
          >

            <div
              id="voter_container_all"
              class="text-xl font-semibold tracking-tight text-gray-900
                block bg-white rounded-lg shadow-md hover:bg-gray-100
                mb-2 mt-1 ml-2 mr-2 border-2 border-gray-200 button_transition"
            >
              <div style="width:100%; height:85px">
                <a
                  id="voter_button_all"
                  href="javascript:void(0)"
                  onclick="stageVoter('all')"
                >
                  <div class="h-full w-full p-3">
                    All voters
                    <p class="text-sm font-medium">The popular places people have voted from</p>
                  </div>
                </a>
              </div>

              <div id="tag_key_container_all"
                style="width:100%; height:50px; display:none">
                <table style="width:100%; text-align:center; margin-bottom:5px">
                  <tr>
                    <td style="width:25%" class="text-base">
                      Min
                    </td>
                    <td style="width:50%">
                      <div class="rounded" style="width:100%; height:30px; background:linear-gradient(to right, rgba(255,157,71,0.1), rgba(255,157,71,1))"></div>
                    </td>
                    <td style="width:25%" class="text-base">
                      Max
                    </td>
                  </tr>
                </table>
              </div>
            </div>

          @foreach(['comp_vote', 'comp_law'] as $comp_type)
            <div
              id="voter_container_{{ $comp_type }}"
              class="text-xl font-semibold tracking-tight text-gray-900
                block rounded-lg shadow-md
                mb-1 mt-1 ml-2 mr-2 border-2 border-gray-200 button_transition
                @auth
                  bg-white hover:bg-gray-100
                @else
                  bg-gray-400
                @endauth
                "
            >
              <div style="width:100%; height:85px">
                <a
                  id="voter_button_{{ $comp_type }}"
                  href="javascript:void(0)"
                  @auth
                    onclick="stageVoter('{{ $comp_type }}')"
                  @else
                    onclick="set_pane_mode('pane_user_type')"
                  @endauth
                >
                  <div class="h-full w-full p-3">
                    {{ $comp_type == 'comp_vote' ? 'Vote' : 'Law' }} compatibility
                    <p class="text-sm font-medium">How well your votes match up with {{ $comp_type == 'comp_vote' ? 'votes' : 'laws' }} across the world</p>
                  </div>
                </a>
              </div>

              <div id="tag_key_container_{{ $comp_type }}"
                style="width:100%; height:50px; display:none">
                <table style="width:100%; text-align:center; margin-bottom:5px">
                  <tr>
                    <td style="width:25%" class="text-base">
                      Min
                    </td>
                    <td style="width:50%">
                      <div class="rounded" id="color_scale_{{ $comp_type }}" style="width:100%; height:30px"></div>
                    </td>
                    <td style="width:25%" class="text-base">
                      Max
                    </td>
                  </tr>
                </table>
              </div>
            </div>
          @endforeach

          </div>
        </div>

        @foreach ($tag_types as $tag_type)
        <div>
          <a id="voter-folder-button-{{ $tag_type->slug }}"
            href="javascript:void(0)"
            onclick="openVoterFolder('{{ $tag_type->slug }}')"
          >
            <x-folder-handle :slug='$tag_type->slug' :name='$tag_type->name' />
          </a>
          <div
            id='voter-folder-{{ $tag_type->slug }}'
            class="folder_transition"
            style="max-height:0px"
            >
          </div>
        </div>
        @endforeach

        @foreach ($tags as $tag)
        <div id="voter-checkbox-{{ $tag->slug }}" class="mb-2 mt-1 ml-2 mr-2">
          <div
            id="voter_container_{{ $tag->id }}"
            class="text-xl font-semibold tracking-tight text-gray-900
              block bg-white rounded-lg shadow-md hover:bg-gray-100
              border-2 border-gray-200 button_transition"
          >
            <a
              id="voter_button_{{ $tag->id }}"
              href="javascript:void(0)"
              onclick="stageVoter({{ $tag->id }})"
            >
              <div class="h-full w-full p-3">
                {{ $tag->name }}
              </div>
            </a>

            <div id="tag_key_container_{{ $tag->id }}"
              style="width:100%; height:50px; display:none">
              <table style="width:100%; text-align:center; margin-bottom:5px">
                <tr>
                  <td style="width:25%" class="text-base">
                    Min %
                  </td>
                  <td style="width:50%">
                    <div class="rounded" style="width:100%; height:30px; background:linear-gradient(to right, rgba(255,157,71,0.1), rgba(255,157,71,1))"></div>
                  </td>
                  <td style="width:25%" class="text-base">
                    Max %
                  </td>
                </tr>
              </table>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div><!-- Cosmetic -->

      <div id="captcha_container"
        style="display:none"
        class="g-recaptcha"
        data-sitekey="6LcwziwhAAAAAHOR6JERUohR4Z1FFJdSIUxUWSuT";
        data-callback="submitWithCaptcha"
        data-size="invisible">
      </div>
    </div>


    <div id="pane_create_poll" class="paneElement scrolling">
      <div class="scrolling-y" style="height:100%">
        <h3 class="mt-2 text-lg font-medium text-gray-900">
          Create new poll
        </h3>
        <form id="new_poll_form" action="/create_poll" method="POST"> <!--target="form_sink">-->
          @csrf
          <label for="new_poll_summary">Summary</label>
          <input
            id="new_poll_summary"
            placeholder="e.g., global warming"
            name="summary"
            style="width:90%"
            class="m-1 shadow appearance-none border rounded py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          >
          <br>
          <label for="new_poll_prompt">Poll question</label>
          <textarea
            id="new_poll_prompt"
            placeholder="e.g., should we be doing more about global warming?"
            name="prompt"
            maxlength="140"
            style="width:90%"
            class="m-1 shadow appearance-none border rounded py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          ></textarea>
          <br>
          <label for="create_poll_answer_type">Answers:</label>
          <select style="width:150px" id="create_poll_answer_type" name='answer_type'>
            <option value="yes_no">Yes / No</option>
            <option value="zero_ten">0 to 10</option>
            <option value="high_low">High / Low</option>
            <option value="good_bad">Good / Bad</option>
          </select>
          <br>

          <button
            style="width:100%; max-width:200px; margin-top:20px"
            class="m-1 bg-orange-300 hover:bg-orange-500 text-white font-bold py-2 px-4 border border-orange-400 rounded"
          >
            Submit
          </button>
          <br>
          <button
            type="button"
            onclick="set_pane_mode('pane_polls')"
            style="width:100%; max-width:200px"
            class="m-1 bg-white hover:bg-orange-500 text-orange-300 font-semibold hover:text-white py-2 px-4 border border-orange-400 hover:border-transparent rounded"
          >
            Back
          </button>
        </form>
      </div>
    </div>

    <!--
      NEW user
    -->
    <div id="pane_user_type" class="paneElement">
      <div style="display:flex; flex-direction:column; height:100%; width:100%;
        padding-top:10%; padding-bottom: 10%; text-align:center; align-items:center">
        <button onclick="set_pane_mode('pane_new_user')"
          class="bg-orange-300 hover:bg-orange-500 text-white font-bold py-2 px-4 border border-orange-400 rounded"
          style="margin-left:7px; width:100%; max-width:200px">
          New user
        </button>

        <hr style="width:100%; max-width:200px; margin:20px 0px 20px 0px">

        <div style="width:100%; max-width:200px">
          Returning users:
          <form id="login_details_form" action="/login" method="POST"> <!--target="form_sink">-->
            @csrf
            <input type="text" id="captcha_val_login" name="g-recaptcha-response" style="display:none">
            <input
              id="access_token"
              placeholder="Login code"
              name="access_token"
              class="m-1 shadow appearance-none border rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <button
              type="button"
              style="width:100%; max-width:200px"
              onclick="primeForCaptcha('login')"
              class="m-1 bg-orange-300 hover:bg-orange-500 text-white font-bold py-2 px-4 border border-orange-400 rounded"
            >
              Submit
            </button>
            <button
              type="button"
              onclick="set_pane_mode('pane_polls')"
              style="width:100%; max-width:200px"
              class="m-1 bg-white hover:bg-orange-500 text-orange-300 font-semibold hover:text-white py-2 px-4 border border-orange-400 hover:border-transparent rounded"
            >
              Back
            </button>
          </form>
        </div>

      </div>
    </div>


    <div id="pane_new_user" class="paneElement">
      <div class="scrolling-y" style="height:100%">
        <h3 class="mt-2 text-lg font-medium text-gray-900">
          Choose a location for your vote
        </h3>
        <span id="new_location_button">
          In progress...
        </span>

        <form id="new_details_form" action="/new_vote" method="POST"> <!--target="form_sink">-->
          @csrf
          <input type="text" id="captcha_val_new" name="g-recaptcha-response" style="display:none">
          <input type="number" id="new-row" name="grid_row" style="display:none">
          <input type="number" id="new-col" name="grid_col" style="display:none">

          <div style="background-color:white">
            <h3 class="text-lg font-medium text-gray-900 mt-2">
              Tell us about yourself
            </h3>
          </div>
          @foreach ($tag_types as $tag_type)
            <div class="block bg-white rounded-lg shadow-md hover:bg-gray-100 p-2
              m-2 border-2 border-gray-200">
              <h3 class="cursor-pointer text-xl font-semibold tracking-tight text-gray-900">
                {{ $tag_type->name }}
              </h3>
              <select style="text-align:center" id='new-folder-{{ $tag_type->slug }}' name='{{ $tag_type->slug }}'>
                <!-- Options get entered here dynamically based on categories -->
                <option value="empty">Prefer not to say</option>
              </select>
            </div>
          @endforeach
          @foreach ($tags as $tag)
            <option id="new-checkbox-{{ $tag->slug }}" value="{{ $tag->slug }}">{{ $tag->name }}</option>
          @endforeach

          <button
            type="button"
            style="width:100%; max-width:200px"
            class="m-1 bg-orange-300 hover:bg-orange-500 text-white font-bold py-2 px-4 border border-orange-400 rounded"
            onclick="newVoteSubmit()"
          >
            Submit
          </button><br>
          <div style="background-color:white">
            <input type="checkbox" name="remember_me" class="mb-2"> Remember me on this device (uses cookies)</input>
          </div>
        </form>
      </div>
    </div>


    <!--
      UPDATE
    -->
    <div id="pane_my_details" class="paneElement">
      <div class="scrolling-y" style="height:100%">
        <form id="update_details_form" action="/update_details" method="POST"> <!--target="form_sink">-->
          @csrf
          <input type="number" id="update-row" name="grid_row" style="display:none">
          <input type="number" id="update-col" name="grid_col" style="display:none">

          <div style="background-color:white">
            <h3 class="text-lg font-medium text-gray-900">
              Update your info
            </h3>
          </div>
          @foreach ($tag_types as $tag_type)
            <div class="block bg-white rounded-lg shadow-md hover:bg-gray-100 p-2
              m-2 border-2 border-gray-200">
              <h3 class="cursor-pointer text-xl font-semibold tracking-tight text-gray-900">
                {{ $tag_type->name }}
              </h3>
              <select style="text-align:center" id='update-folder-{{ $tag_type->slug }}' name='{{ $tag_type->slug }}'>
                <!-- Options get entered here dynamically based on categories -->
                <option value="empty">Prefer not to say</option>
              </select>
            </div>
          @endforeach
          @foreach ($tags as $tag)
            <option id="update-checkbox-{{ $tag->slug }}" value="{{ $tag->slug }}">{{ $tag->name }}</option>
          @endforeach

          <button id="update_location_button" onclick="set_up_select_ui('update')"
            type="button"
            style="width:100%; max-width:200px"
            class="mb-2 bg-orange-300 hover:bg-orange-500 text-white font-bold py-2 px-4 border border-orange-400 rounded"
          >
            Update location
          </button>
          <br>
          <button
            style="width:100%; max-width:200px"
            class="bg-orange-300 hover:bg-orange-500 text-white font-bold py-2 px-4 border border-orange-400 rounded"
          >
            Submit
          </button><br>
          <div style="display:inline; background-color:white; width:100%; max-width:200px; padding-bottom:15px"
          >
            <input type="checkbox" name="remember_me"
              @auth
                {{ request()->cookie('access_token') ? 'checked' : '' }}
              @endauth
              > Remember me on this device (uses cookies)
            </input>
          </div>
        </form>
        <button
          class="mt-2 bg-white hover:bg-orange-500 text-orange-300 font-semibold hover:text-white py-2 px-4 border border-orange-400 hover:border-transparent rounded"
          type="button"
          style="width:100%; max-width:200px"
          onclick="set_pane_mode('pane_polls')"
        >
          Cancel
        </button>
      </div>
    </div>

    <!-- End of pane divs -->
    </div>


    <!--
      MAP
    -->
    <div id="map" class="main_transition"></div>

  	<script>
      const allPromptsRaw = {{ Js::from($prompts->all()) }};
      var allPrompts = allPromptsRaw.reduce((a, v) => ({ ...a, [v.id]: v}), {});
      const tagTypesArr = {{ Js::from($tag_types) }};
      const allTagTypes = tagTypesArr.reduce((a, v) => ({ ...a, [v.id]: v}), {});
      const tagsArr = {{ Js::from($tags) }};
      const allTags = tagsArr.reduce((a, v) => ({ ...a, [v.id]: v}), {});
      const lawPromptIds = {{ $law_prompt_ids }};
      @auth
        const userId = {{ auth()->user()->id }};
        var myResponses = JSON.parse({{ Js::from(auth()->user()->responses) }});
        const myTags = JSON.parse({{ Js::from(auth()->user()->tags) }});
        const myRow = {{ auth()->user()->grid_row }};
        const myCol = {{ auth()->user()->grid_col }};

        const compColorSteps = [
          [-1, "rgba(255,0,0,0.9)"],
          [0, "rgba(200,200,200,0.5)"],
          [1, "rgba(0,255,0,0.9)"]
        ];
      @else
        const userId = -1;
      @endauth

      function dElem (v) {
        return document.getElementById(v);
      }

      function getPromptHtml(promptObj) {
        return `
        <div
          id="vote_button_${promptObj.id}"
          style="margin-bottom:10px"
          class="block bg-white rounded-lg shadow-md hover:bg-gray-100 p-2
            mt-1 ml-2 mr-2 border-2 border-gray-200 button_transition"
        >
          <table style="width:100%; color:#a0a0a0; font-size:10px; margin:-5px 0 0 0; padding:0">
            <tr>
              <td style="text-align:left">
                <a href="javascript:void(0)" onclick="navigator.clipboard.writeText('${window.location.origin}/poll/${promptObj.id}');this.innerHTML='Copied'">Copy link</a>
              </td>
              <td style="text-align:center">
                ${(promptObj.creator_id == userId) ? 'Mine! ' : ''}
                ${promptObj.reviewed ? '' : 'Under review'}
              </td>
              <td style="text-align:right">
                Votes: ${promptObj.n_votes}+
              </td>
            </tr>
          </table>
          <h5
            onclick="stage_vote(${promptObj.id})"
            class="cursor-pointer mb-2 text-xl font-semibold tracking-tight text-gray-900">
            ${promptObj.caption}
          </h5>

          <div id="prompt_content_${promptObj.id}" style="width:100%; padding:5px; display:none;">
            <table id="stats_chart_${promptObj.id}"
              style="table-layout:fixed; width:100%; height:{{ $chart_height_px }}px; border-bottom: 1px solid gray"
            >
              <tr valign=bottom>
                @for($i = 0; $i < $chart_n_elems; ++$i)
                  <td style="height:{{ $chart_height_px }}px; width:{{ $i == ($chart_n_elems-1) ? 0 : 100 / ($chart_n_elems - 1) }}%">
                    <div id="stats_${promptObj.id}_cell_{{ $i }}" style="border-top-left-radius:5px; border-top-right-radius:5px; width:100%; height:100%">
                    </div>
                  </td>
                @endfor
              </tr>
            </table>
            <a href="javascript:void(0)" onclick="revealStats()">
              <div id="stats_mask_${promptObj.id}" style="margin-top:-{{ $chart_height_px }}px; width:100%; height:{{ $chart_height_px }}px;
                -webkit-backdrop-filter: blur(20px); backdrop-filter: blur(20px);
                border-top-left-radius:5px; border-top-right-radius:5px; padding:20px"
              >
                Show stats
              </div>
            </a>
            @auth
              <form id="vote_form_${promptObj.id}" action="/update_responses" method="POST" target="form_sink">
                @csrf
                <div class="slidecontainer" style="position:relative">
                    <div id="vote_slider_bg_${promptObj.id}"
                      style="position:absolute;height:25px;width:100%;
                      border-bottom-left-radius:12px; border-bottom-right-radius:12px">
                    </div>
                    <input
                      type="range"
                      class="slider"
                      id="vote_slider_${promptObj.id}"
                      min="0"
                      max="10"
                      value="5"
                    />
                </div>
              </form>
              <div id="vote_status_msg_${promptObj.id}" style="width:100%">
                Slide to vote
              </div>
              <div style="float:left; width:45%; font-size:12px; text-align:center">
                <input id="color_input_${promptObj.id}_option0" type="color"
                  style="width:40px; height:20px; border-radius:10px; margin-top:2px; padding:0px 2px 0px 2px; background-color:#cccccc"
                />
                ${promptObj.option0}
              </div>
              <button
                class="bg-orange-300 hover:bg-orange-500 text-white font-bold text-sm border border-orange-400 rounded"
                style="width:2px; visibility:hidden; margin-top:5px"
              >
                -
              </button>
              <div style="float:right; width:45%; font-size:12px; text-align:center">
                ${promptObj.option1}
                <input id="color_input_${promptObj.id}_option1" type="color"
                  style="width:40px; height:20px; border-radius:10px; margin-top:2px; padding:0px 2px 0px 2px; background-color:#cccccc"
                />
              </div>
            @else
              <div style="float:left; width:35%; font-size:12px; text-align:center">
                <input id="color_input_${promptObj.id}_option0" type="color"
                  style="width:40px; height:20px; border-radius:10px; margin-top:2px; padding:0px 2px 0px 2px; background-color:#cccccc"
                />
                <br>${promptObj.option0}
              </div>
              <button onclick="set_pane_mode('pane_user_type')"
                class="bg-orange-300 hover:bg-orange-500 text-white font-bold py-1 text-sm border border-orange-400 rounded"
                style="width:25%; margin-top:5px"
              >
                Vote!
              </button>
              <div style="float:right; width:35%; font-size:12px; text-align:center">
                <input id="color_input_${promptObj.id}_option1" type="color"
                  style="width:40px; height:20px; border-radius:10px; margin-top:2px; padding:0px 2px 0px 2px; background-color:#cccccc"
                />
                <br>${promptObj.option1}
              </div>
            @endauth

            <div
              id="vote_extras_msg_${promptObj.id}"
              style="width:100%; font-size:12px; margin:-4px 0 -4px 0"
              class="block grid place-items-center">
            </div>
          </div>
        </div>
        `
      }

      function addPromptObj(promptObj) {
        prompt_content.insertAdjacentHTML('beforeend', getPromptHtml(promptObj));
        allPrompts[promptObj.id] = promptObj;
      }

      @foreach ($prompts as $prompt)
        addPromptObj({{ Js::from($prompt) }});
      @endforeach

      var currentPagination = {{ Js::from($prompts) }};
      function acceptPageData(pageData) {
        currentPagination = JSON.parse(pageData);
        for (let i = 0; i < currentPagination['data'].length; i++) {
          addPromptObj(currentPagination['data'][i]);
        }
        prompt_next_button.innerHTML = "Next";
      }

      function requestAndLoadPage(url) {
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
          acceptPageData(this.responseText);
        };
        xhttp.open("GET", url, true);
        xhttp.send();
      }

      function nextPage() {
        const dst_url = currentPagination.next_page_url;
        if (dst_url != null) {
          prompt_next_button.innerHTML = "Loading";
          requestAndLoadPage(dst_url);
        } else {
          prompt_next_button.innerHTML = "No more results";
        }
      }

      function autoNextPage() {
        var atScrollBottom = prompt_scolling_div.scrollTop == (prompt_scolling_div.scrollHeight - prompt_scolling_div.offsetHeight);
        var isLoading = prompt_next_button.innerHTML == "Loading";
        if (atScrollBottom && !isLoading) {
          nextPage();
        }
      }

      function newPagination(key, order) {
        requestAndLoadPage(`/pages/${key}/${order}`);
      }

      function deleteAllChildren(elemName) {
        const e = dElem(elemName);
        var child = e.lastElementChild;
        while (child) {
          e.removeChild(child);
          child = e.lastElementChild;
        }
      }

      function setPromptOrder() {
        prompt_next_button.style.display = 'block';
        deleteAllChildren('prompt_content');
        var order = null;
        if (prompt_sort_order.getAttribute('data-val') == 'asc') {
          order = "asc";
        } else {
          order = "desc";
        }
        newPagination(prompt_sort_key.value, order);
      }

      function togglePromptOrder() {
        if (prompt_sort_order.getAttribute('data-val') == 'asc') {
          order = "desc";
          prompt_sort_order_label.innerHTML = "Descending";
        } else {
          order = "asc";
          prompt_sort_order_label.innerHTML = "Ascending";
        }
        prompt_sort_order.setAttribute('data-val', order);
        setPromptOrder();
      }

      var mapDataIsVisible = {};
      function toggleMapData(pId) {
        if (mapDataIsVisible.hasOwnProperty(pId)) {
          mapDataIsVisible[pId] = !mapDataIsVisible[pId];
        } else {
          mapDataIsVisible[pId] = false;
        }
        dElem("map_checkbox_" + pId).checked = mapDataIsVisible[pId];
        paint_prompt();
      }

      var lawDataIsVisible = {};
      function toggleLawData(pId) {
        if (lawDataIsVisible.hasOwnProperty(pId)) {
          lawDataIsVisible[pId] = !lawDataIsVisible[pId];
        } else {
          lawDataIsVisible[pId] = true;
        }
        dElem("law_checkbox_" + pId).checked = lawDataIsVisible[pId];
        paint_prompt();
      }

      var activeCaptchaForm = null;  // 'new' or 'login'
      function primeForCaptcha(formType) {
        activeCaptchaForm = formType;
        {{ App::environment('local') ? "submitWithCaptcha();" : "grecaptcha.enterprise.execute();" }}
      }

      function submitWithCaptcha(token=null) {
        if (token) {
          dElem('captcha_val_' + activeCaptchaForm).value = token;
        }
        dElem(activeCaptchaForm + "_details_form").submit();
      }

      class ProjectionControl {
        onAdd(map) {
          this._map = map;
          this._container = document.createElement('div');
          this._container.className = 'mapboxgl-ctrl';
          this._container.innerHTML = `
          <select
            name="projections"
            id="projections_select"
            onchange="map.setProjection(this.options[this.selectedIndex].value)"
            class="rounded"
          >
            <option value="globe">3D - Globe</option>
            <option value="mercator">2D - Mercator</option>
          </select>`;
          return this._container;
        }

        onRemove() {
          this._container.parentNode.removeChild(this._container);
          this._map = undefined;
        }
      }

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
      const pane_divs = {
        'pane_overlay': {},
        'pane_about': {},
        'pane_polls': {},
        'pane_create_poll': {},
        'pane_new_user': {
          'on_entry': () => {
            set_up_select_ui('new');
            captcha_container.style.display = "block"
          }
        },
        'pane_user_type': {
          'on_entry': () => {
            captcha_container.style.display = "block"
          }
        },
        'pane_my_details': {},
      };

      const titleStagedClasses = ['bg-orange-300', 'text-white'];
      const titleUnstagedClasses = ['bg-white', 'text-orange-300', 'hover:text-white'];
      function set_pane_mode(pane_mode) {
        captcha_container.style.display = "none";
        // Remove all map elements
        if (mapHasLoaded) {
          tear_down_select_ui();
          stage_vote(null);
          hamburgerClose();
          if (stagedVoterId != null) {
            unstageVoter(stagedVoterId);
          }
        } else if (pane_mode != "pane_polls") {
          return;
        }

        // disable all divs that aren't pane_mode
        for (let pane_id in pane_divs) {
          var hammy_elem = dElem("hammy_" + pane_id);
          var title_elem = dElem("title_bar_" + pane_id);
          if (pane_mode == pane_id) {
            dElem(pane_id).style.display = 'inline';
            if ('on_entry' in pane_divs[pane_id]) {
              pane_divs[pane_id]['on_entry']();
            }
            if (hammy_elem) {
              hammy_elem.style.color = 'orange';
            }
            @auth
              if (title_elem) {
                replaceClasses(title_elem, titleUnstagedClasses, titleStagedClasses);
              }
            @endauth
          } else {
            dElem(pane_id).style.display = 'none';
            if (hammy_elem) {
              hammy_elem.style.color = 'black';
            }
            @auth
              if (title_elem) {
                replaceClasses(title_elem, titleStagedClasses, titleUnstagedClasses);
              }
            @endauth
          }
        }
        optimizeLayout();
      }

      const voteVoterStagedClasses = ['bg-white', 'text-orange-300'];
      const voteVoterUnstagedClasses = ['bg-orange-300', 'hover:bg-orange-500', 'text-white'];
      function set_pane_poll_mode(pane_poll_mode) {
        if (!mapHasLoaded) {
          return;
        }
        if (pane_poll_mode == "votes") {
          poll_tab_votes.style.display = 'block';
          poll_tab_voters.style.display = 'none';
          replaceClasses(poll_tab_vote_button, voteVoterUnstagedClasses, voteVoterStagedClasses);
          replaceClasses(poll_tab_voter_button, voteVoterStagedClasses, voteVoterUnstagedClasses);
        } else {
          poll_tab_votes.style.display = 'none';
          poll_tab_voters.style.display = 'flex';
          replaceClasses(poll_tab_vote_button, voteVoterStagedClasses, voteVoterUnstagedClasses);
          replaceClasses(poll_tab_voter_button, voteVoterUnstagedClasses, voteVoterStagedClasses);
        }
      }

      function canDisplayVoteCompatOrAlert() {
        if (Object.keys(myResponses).length < 1) {
          alert("Please vote on at least 1 topic");
          return false;
        }
        return true;
      }

      function canDisplayLawCompatOrAlert() {
        var lawResponseCount = 0;
        for (let i = 0; i < lawPromptIds.length; i++) {
          if (lawPromptIds[i] in myResponses) {
            lawResponseCount++;
          }
        }
        if (lawResponseCount < 1) {
          alert("Please vote on one of the following to see law info: " + lawPromptIds.map(pId => {return lowerOrAcronym(allPrompts[pId].summary);}).join(", "));
          return false;
        }
        return true;
      }

      var stagedVoterId = null;
      function stageVoter(tagId) {
        if (tagId == stagedVoterId) {
          unstageVoter(tagId);
          return;
        } else if (stagedVoterId != null) {
          unstageVoter(stagedVoterId);
        }
        stagedVoterId = tagId;

        // TODO fetch all prompts answered by user
        if (tagId == "comp_vote") {
          if (!canDisplayVoteCompatOrAlert()) {
            return;
          }
          initCompatHandler();
          color_scale_comp_vote.style.background = "linear-gradient(to right," + compColorSteps.map((v) => {return v[1];}).join(',') + ")";
        } else if (tagId == "comp_law") {
          if (!canDisplayLawCompatOrAlert()) {
            return;
          }
          initCompatHandler();
          color_scale_comp_law.style.background = "linear-gradient(to right," + compColorSteps.map((v) => {return v[1];}).join(',') + ")";
        } else {
          removeCompatHandler();
        }
        var voterContainer = dElem("voter_container_" + tagId);
        replaceClasses(voterContainer, unstagedClasses, stagedClasses);
        map.setLayoutProperty('tags_vote', 'visibility', 'visible');
        map.setLayoutProperty('tags_law', 'visibility', 'visible');
        var filterContainer = dElem("tag_key_container_" + tagId);
        filterContainer.style.display = 'inline';
        voters_indicator.style.visibility = 'visible';
        paint_tag();
      }

      function unstageVoter(tagId) {
        voters_indicator.style.visibility = 'hidden';
        var filterContainer = dElem("tag_key_container_" + tagId);
        filterContainer.style.display = 'none';
        var voterContainer = dElem("voter_container_" + tagId);
        replaceClasses(voterContainer, stagedClasses, unstagedClasses);
        map.setLayoutProperty('tags_vote', 'visibility', 'none');
        map.setLayoutProperty('tags_law', 'visibility', 'none');
        removeCompatPopup();
        stagedVoterId = null;
      }

      function hamburgerOpen() {
        navOverlay.style.display = 'block';
        hammy.onclick = hamburgerClose;
      }

      function hamburgerClose() {
        navOverlay.style.display = 'none';
        hammy.onclick = hamburgerOpen;
      }

      const mapSizes = {
        1: "0%",
        2: "calc(50% - {{ $title_height_px }}px)",
        3: "calc(100% - {{ $title_height_px }}px)"
      };
      const defaultMapSize = 2;
      var mapSize = defaultMapSize;
      function changeMapSize(delta) {
        if (delta == 0) {
          mapSize = defaultMapSize;
        } else if ((mapSize + delta) in mapSizes) {
          mapSize += delta;
        }
        optimizeLayout();
      }

      var compatButtonsAreVisible = true;
      function closeCompatButtons() {
        compatButtonsAreVisible = false;
        compat_button_bar.style.display = 'none';
        prompt_scolling_div.style.height = 'calc(100% - 50px)';
      }

      function optimizeLayout() {
        if (window.innerWidth < 800) {
          const mapHeightPerc = mapSizes[mapSize];
          dElem('pane_container').style.top = mapHeightPerc;
          dElem('map').style.height = mapHeightPerc;
          logo_img.src = "/logo-w-stacked.png";

          dElem('pane_container').style['margin-top'] = "{{ $title_height_px }}px";
          dElem('pane_container').style.width = "100%";
          dElem('map').style.width = "100%";
          dElem('map').style.left = "0px";
          title_buttons.style.display = "none";
          vert_options.style.display = "block";
          hammy.style.display = "block";

          poll_tab_votes.style.height = `calc(100% - {{ $ad_height_px }}px)`;
          poll_tab_voters.style.height = "calc(100% - 50px - {{ $ad_height_px }}px)";
          title_bar.style.width = "100%";
          delayedMapRefresh(550);
        } else {
          logo_img.src = "/logo-w.png";
          dElem('map').style.display = "inline";
          dElem('pane_container').style.top = "{{ $title_height_px }}px";
          dElem('pane_container').style['margin-top'] = "0px";
          dElem('pane_container').style.width = "{{ $pane_width_perc }}%";
          dElem('map').style.height = "";
          dElem('map').style.width = "{{ 100 - $pane_width_perc - $ad_width_perc }}%";
          dElem('map').style.left = "{{ $pane_width_perc }}%";
          title_buttons.style.display = "block";
          vert_options.style.display = "none";
          hammy.style.display = "none";
          poll_tab_votes.style.height = '100%';
          poll_tab_voters.style.height = 'calc(100% - 50px)';
          title_bar.style.width = "calc(100% - {{ $ad_width_perc }}%)";
          delayedMapRefresh(20);
        }
      }

      var nResizesWaiting = 0;
      function delayedMapRefresh(delayMs) {
        nResizesWaiting++;
        setTimeout(function () {
          nResizesWaiting = Math.max(0, nResizesWaiting - 1);
          if (nResizesWaiting == 0) {
            map.resize();
          }
        },
        delayMs);
      }

      addEventListener('resize', optimizeLayout);
      optimizeLayout();
      /* End of page layout */


  	  map.on('style.load', () => {
  	    map.setFog({}); // Set the default atmosphere style
  	  });

      /* MAP ON LOAD */
      var mapHasLoaded = false;
  		map.on('load', () => {
        mapHasLoaded = true;
  			map.addSource('vote-data', {
					'type': 'vector',
					'url': "mapbox://{{ $tileset_id }}"
				});

        map.addSource('law-data', {
					'type': 'vector',
					'url': "mapbox://{{ $law_tileset_id }}"
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

        @auth
        map.addSource('my_loc', {
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
          'id': 'my_loc_layer',
          'type': 'line',
          'source': 'my_loc',
          'layout': {},
          'paint': {
            'line-color': '#000000',
            'line-opacity': 0.5
          }
        });
        @endauth

        map.addLayer({
          'id': 'prompts',
          'type': 'fill',
          'source': 'vote-data',
          'source-layer': 'cells',
          'paint': {'fill-outline-color': 'rgba(0,0,0,0)'},
          'layout': {
            'visibility': 'none'
          }
        });

        map.addLayer({
          'id': 'laws',
          'type': 'line',
          'source': 'law-data',
          'source-layer': 'laws',
          'paint': {'line-width': 1.5},
          'layout': {
            'visibility': 'none'
          }
        });

        map.addLayer({
          'id': 'tags_vote',
          'type': 'fill',
          'source': 'vote-data',
          'source-layer': 'cells',
          'paint': {'fill-outline-color': 'rgba(0,0,0,0)'},
          'layout': {
            'visibility': 'none'
          }
        });

        map.addLayer({
          'id': 'tags_law',
          'type': 'fill',
          'source': 'law-data',
          'source-layer': 'laws',
          'paint': {'fill-outline-color': 'rgba(0,0,0,0)'},
          'layout': {
            'visibility': 'none'
          }
        });

        map.addControl(new ProjectionControl(), 'top-right');

        set_pane_mode('pane_polls');
        @auth
          displayLoc();
        @endauth
  		});
      /* END OF MAP ON LOAD */

      /*
        Mapbox's interpolation has numerical issues resulting in colors
        [min-eps, max+eps]
      */
      function SC(val) {
        return Math.max(0.1, Math.min(254.9, val));
      }

      var activePromptColorSteps = null;
  		function display_prompt(promptId, isMapped, colorSteps) {
        activePromptColorSteps = colorSteps;
        stagedVoteId = promptId;
        if (promptId) {
          paint_prompt(promptId);
        } else {
          map.setLayoutProperty('prompts', 'visibility', 'none');
          map.setLayoutProperty('laws', 'visibility', 'none');
        }
  		}

      function paint_prompt() {
        if (lawDataIsVisible.hasOwnProperty(stagedVoteId) && lawDataIsVisible[stagedVoteId]) {
          map.setLayoutProperty('laws', 'visibility', 'visible');
        } else {
          map.setLayoutProperty('laws', 'visibility', 'none');
        }

        if (!mapDataIsVisible.hasOwnProperty(stagedVoteId) || mapDataIsVisible[stagedVoteId]) {
          map.setLayoutProperty('prompts', 'visibility', 'visible');
        } else {
          map.setLayoutProperty('prompts', 'visibility', 'none');
        }

        // Used as a workaround so that filters can affect map and charts
        if (!stagedVoteId) {
          return;
        }
        const C = activePromptColorSteps;
        displayStats(JSON.parse(allPrompts[stagedVoteId]['count_ratios'])['all'], activePromptColorSteps);
        if (allPrompts[stagedVoteId].is_mapped) {
          const dataId = 'prompt-' + stagedVoteId + '-all';
          map.setPaintProperty(
            'prompts',
            'fill-color',
            [
              "case",
              ["==", ["get", dataId], -1], 'rgba(0,0,0,0)', // transparent if -1
              ["rgba",
                ["interpolate", ["linear"], ["get", dataId], 0, SC(C[0][0]), 0.5, SC(C[1][0]), 1, SC(C[2][0])],
                ["interpolate", ["linear"], ["get", dataId], 0, SC(C[0][1]), 0.5, SC(C[1][1]), 1, SC(C[2][1])],
                ["interpolate", ["linear"], ["get", dataId], 0, SC(C[0][2]), 0.5, SC(C[1][2]), 1, SC(C[2][2])],
                0.5  // opacity
              ]
            ]
          );

          if (lawPromptIds.includes(stagedVoteId)) {
            const dataId = 'prompt-' + stagedVoteId + '-all';
            map.setPaintProperty(
              'laws',
              'line-color',
              [
                "case",
                ["==", ["get", dataId], -1], 'rgba(0,0,0,0)', // transparent if -1
                ["rgba",
                  ["interpolate", ["linear"], ["get", dataId], 0, SC(C[0][0]), 0.5, SC(C[1][0]), 1, SC(C[2][0])],
                  ["interpolate", ["linear"], ["get", dataId], 0, SC(C[0][1]), 0.5, SC(C[1][1]), 1, SC(C[2][1])],
                  ["interpolate", ["linear"], ["get", dataId], 0, SC(C[0][2]), 0.5, SC(C[1][2]), 1, SC(C[2][2])],
                  0.5  // opacity
                ]
              ]
            );
          }
        } else {
          map.setLayoutProperty('prompts', 'visibility', 'none');
        }
      }

      function getCompatFillExpression(compResponses, srcLookup=null) {
        // compResponses: {pId : response_val}
        var maxSources = ["max"];
        var a_b = ["+"];
        var a_len_2 = ["+"];
        var b_len_2 = ["+"];
        for (let pId in compResponses) {
          if (srcLookup != null && !srcLookup(pId)) {
            continue;
          }
          var a_val = compResponses[pId] - 5;  // [-5, 5]
          var b_src = 'prompt-' + pId + '-all';
          var b_val_raw = ["get", b_src];
          var b_val_invalid = ["==", b_val_raw, -1];
          var b_val = ["-", b_val_raw, 0.5];  // [-0.5, 0.5]
          maxSources.push(b_val_raw);
          a_b.push(
            ["case",
              b_val_invalid, 0,
              ["*", a_val, b_val]
            ]);
          a_len_2.push(
            ["case",
              b_val_invalid, 0,
              a_val * a_val
            ]);
          b_len_2.push(
            ["case",
              b_val_invalid, 0,
              ["^", b_val, 2]
            ]);
        }
        var cosine_sim = ["/",  // a.b / |a||b|
          a_b,  // a.b
          ["*",  // |a||b|
            ["sqrt", a_len_2],  // |a|
            ["sqrt", b_len_2]  // |b|
          ]
        ];
        var ret = [
          "case",
          ["==", maxSources, -1], 'rgba(0,0,0,0)',  // none are present in cell
          ["==", ["min", a_len_2, b_len_2], 0], 'rgba(0,0,0,0)',
          ["interpolate",
            ["linear"],
            cosine_sim,
          ].concat(compColorSteps.flat())
        ];

        return ret;
      }

      function paint_tag() {
        if (stagedVoterId == "comp_vote") {
          map.setPaintProperty('tags_law', 'fill-color', 'rgba(255, 255, 255, 0)');
          map.setPaintProperty(
            'tags_vote',
            'fill-color',
            getCompatFillExpression(myResponses, srcLookup=(pId) => {return allPrompts[pId].is_mapped})
          );
        } else if (stagedVoterId == "comp_law") {
          map.setPaintProperty('tags_vote', 'fill-color', 'rgba(255, 255, 255, 0)');
          map.setPaintProperty(
            'tags_law',
            'fill-color',
            getCompatFillExpression(myResponses, srcLookup=(pId) => {return lawPromptIds.includes(pId-0)})
          );
        } else {
          map.setLayoutProperty('tags_law', 'visibility', 'none');
          const dataId = (stagedVoterId == 'all') ? 'tag-all' : 'tag-' + allTags[stagedVoterId].slug;
          map.setPaintProperty(
            'tags_vote',
            'fill-color', [
              "case",
              ["==", ["get", dataId], -1], 'rgba(0,0,0,0)', // transparent if -1
              ["rgba",
                255,157,71,
                ["interpolate", ["linear"], ["get", dataId], 0, 0.1, 1, 1]
              ]
            ]
          );
        }
      }

  		const maxZoom = 3;
  		const maxStepDeg = 15;
  		const zSteps = [0, 1, 2, 3].map((i) => { return maxStepDeg / Math.pow(2, i) });
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
      function get_cell_coords_xy(xy, zoom) {
        const zStep = zSteps[zoom];
        const col = xy[0];
        const row = xy[1];
        const anc_lat = oLat - zStep * row;
        const anc_lng = oLng + zStep * col;
        return [
          [anc_lng, anc_lat],
          [anc_lng + zStep, anc_lat],
          [anc_lng + zStep, anc_lat - zStep],
          [anc_lng, anc_lat - zStep],
          [anc_lng, anc_lat],
        ];
      }

  		function get_cell_coords_lnglat(lngLat, zoom) {
  			const xy = get_xy(lngLat, zoom);
        return get_cell_coords_xy(xy, zoom);
  		}

      @auth
        var lastZoomChange = null;
        function displayLoc() {
          var zoom = Math.min(Math.floor(map.getZoom()), maxZoom);
          if (lastZoomChange != null && zoom == lastZoomChange) {
            return;
          }
          lastZoomChange = zoom;
          map.getSource('my_loc').setData({
    				'type': 'Feature',
    				'geometry': {
    					'type': 'Polygon',
    					'coordinates': [
                get_cell_coords_xy([
                  Math.floor(myCol / Math.pow(2, maxZoom - zoom)),
                  Math.floor(myRow / Math.pow(2, maxZoom - zoom))
                ], zoom)
              ]
    				}
    			});
        }

        map.on('zoom', (e) => { displayLoc() });
      @endauth

  		function display_clicked_cell(lngLat) {
  			map.getSource('clicked_loc').setData({
  				'type': 'Feature',
  				'geometry': {
  					'type': 'Polygon',
  					'coordinates': [get_cell_coords_lnglat(lngLat, maxZoom)]
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
  						'coordinates': [get_cell_coords_lnglat(lngLat, i)]
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

      const locDoneText = "✅ Done!";
  		function handler_clicked_cell(form_prefix, e) {
  			selected_xy = get_xy(e.lngLat, maxZoom);
  			display_clicked_cell(e.lngLat);

        dElem(form_prefix + "_location_button").innerText = locDoneText;
        dElem(form_prefix + '-col').value = selected_xy[0];
        dElem(form_prefix + '-row').value = selected_xy[1];
  		}

  		function handler_hover_cell(e) {
  			display_hover_cell(e.lngLat);
  		}

      var currentHoverHandler = null;
      var currentClickHandler = null;
  		function set_up_select_ui(form_prefix) {
        tear_down_select_ui();  // remove any existing elements
        button = dElem(form_prefix + "_location_button");
        if (button.innerText != locDoneText) {
          dElem(form_prefix + "_location_button").innerText = "🌎 Selecting";
        }
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

      function button_update_details() {
        set_pane_mode('pane_my_details');

        // Set current user tags using user slugs
        for (let i = 0; i < myTags.length; i++) {
          for (let j in allTags) {
            var tagOption = allTags[j];
            if (myTags[i] == tagOption.slug) {
              var myTag_i = tagOption;
              var optionToSet = "update-folder-" + allTagTypes[myTag_i.tag_type].slug;
              dElem(optionToSet).value = myTag_i.slug;
              break;
            }
          }
        }
      }

      function newVoteSubmit() {
        if (dElem('new-row').value == "" ||
            dElem('new-col').value == "") {
          alert("Please confirm the location for your vote");
        } else {
          primeForCaptcha('new');
        }
      }

      update_details_form.addEventListener('submit',
        function (e) {
          set_pane_mode('pane_polls');
        }
      );

      // Note: barColors can be a different length than data - uses linear interpolation
      function displayStats(data, barColors) {
        if (! ('has_been_opened' in allPrompts[stagedVoteId])) {
          dElem('stats_mask_' + stagedVoteId).style.display = 'block';  // Initially mask all stats
        } else {
          dElem('stats_mask_' + stagedVoteId).style.display = 'none';
        }
        var n_elems = data.length;
        var n_intervals = data.length - 1;
        for (let i = 0; i < n_elems; i++) {
          var color = colorLerp(i / n_intervals, barColors);
          var bar = dElem("stats_" + stagedVoteId + "_cell_" + i);
          bar.style['background-color'] = "rgb("+ color.join(',') +")";
          bar.style['height'] = 100*data[i] + "%";
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

      function setVoteStatus(wasSubmitted=false, previously=false) {
        var voteSliderStyle = document.querySelector('[data="test"]');
        var vsm = dElem("vote_status_msg_" + stagedVoteId);
        if (wasSubmitted) {
          voteSliderStyle.innerHTML = ".slider::-webkit-slider-thumb {background:url('/tick.png');} .slider::-moz-range-thumb {background:url('/tick.png');}";
          if (previously) {
            vsm.style.display = 'none';
          } else {
            vsm.innerHTML = "Submitted!";
          }
        } else {
          voteSliderStyle.innerHTML = ".slider::-webkit-slider-thumb {background:url('/arrows.png');} .slider::-moz-range-thumb {background:url('/arrows.png');}";
          vsm.style.display = 'block';
          vsm.innerHTML = "Slide to vote";
        }
      }

      function hidePromptContent(promptId) {
        var target_div = dElem("vote_button_" + promptId);
        if (target_div) {
          dElem("prompt_content_" + promptId).style.display = "none";
          replaceClasses(target_div, stagedClasses, unstagedClasses);
        }
      }

      function updateStagedColors(pId) {
        allPrompts[stagedVoteId].userPromptColors = [
          hexToColorArr(dElem('color_input_' + pId + '_option0').value),
          hexToColorArr(dElem('color_input_' + pId + '_option1').value),
        ];
        showPromptContent();
      }

      @foreach ($prompts as $prompt)
        color_input_{{ $prompt->id }}_option0.addEventListener(
          'change', () => {
            updateStagedColors({{ $prompt->id }});
          });
        color_input_{{ $prompt->id }}_option1.addEventListener(
          'change', () => {
            updateStagedColors({{ $prompt->id }});
          });
      @endforeach

      function toggleExtrasVisibilty(pId) {
        var elem = dElem('extras_' + stagedVoteId);
        elem.style.display = (elem.style.display == 'block') ? 'none' : 'block';
      }

      function showPromptContent() {
        var prompt = allPrompts[stagedVoteId];
        dElem('prompt_content_' + stagedVoteId).style.display = "block";
        replaceClasses(
          dElem("vote_button_" + stagedVoteId),
          unstagedClasses,
          stagedClasses
        );

        if ('userPromptColors' in allPrompts[stagedVoteId]) {
          var stagedColors = allPrompts[stagedVoteId].userPromptColors;
        } else {
          var stagedColors = getRandomColorCombo();
        }
        //var colorSteps = JSON.parse(prompt['colors']);
        var colorSteps = [stagedColors[0], [200,200,200], stagedColors[1]];
        dElem('color_input_' + stagedVoteId + '_option0').value = colorArrToHex(stagedColors[0]);
        dElem('color_input_' + stagedVoteId + '_option1').value = colorArrToHex(stagedColors[1]);

        var extrasElem = dElem("vote_extras_msg_" + stagedVoteId);
        var extrasContent = [];
        if (prompt.is_mapped) {
          extrasContent.push(`
            <form>
              <a href="javascript:void(0)" onclick="toggleMapData(${stagedVoteId})">
                <div
                  class="rounded-lg shadow-m hover:bg-gray-100">
                  <input style="vertical-align:-2px;" type="checkbox" id="map_checkbox_${stagedVoteId}" class="mb-2"
                  ${
                    (!mapDataIsVisible.hasOwnProperty(stagedVoteId) || mapDataIsVisible[stagedVoteId]) ? 'checked' : ''
                  }> Show map data</input>
                </div>
              </a>
            </form>
          `);
        }
        if (lawPromptIds.includes(stagedVoteId)) {
          extrasContent.push(
            `<form>
              <a href="javascript:void(0)" onclick="toggleLawData(${stagedVoteId})">
                <div
                  class="rounded-lg shadow-m hover:bg-gray-100">
                  <input style="vertical-align:-2px;" type="checkbox" id="law_checkbox_${stagedVoteId}" class="mb-2"
                  ${
                    (lawDataIsVisible.hasOwnProperty(stagedVoteId) && lawDataIsVisible[stagedVoteId]) ? 'checked' : ''
                  }> Show law data</input>
                </div>
              </a>
            </form>`
          );
        }

        if (extrasContent.length == 0) {
          extrasElem.style.display = 'none';
        } else {
          extrasElem.innerHTML =
            `<hr class="m-2" style="border-width:1px; width:100%">
            <button onclick="toggleExtrasVisibilty(${stagedVoteId})">
              <b>Extras</b>
            </button>
            <div id="extras_${stagedVoteId}" style="width:100%; display:none" class="grid place-items-center">
              ${extrasContent.join('')}
            </div>
          `
        }

        var shouldRevealStats = false;
        @auth
          // Slider colors
          dElem("vote_slider_bg_" + stagedVoteId).style["background-image"] =
            "linear-gradient(to right, "+
            "rgb(" + colorSteps[0].join(',') + ")," +
            "rgb(" + colorSteps[1].join(',') + ")," +
            "rgb(" + colorSteps[2].join(',') + "))";

          slider = dElem("vote_slider_" + stagedVoteId);
          // Visibility (note: hidden initially)
          dElem("vote_slider_bg_" + stagedVoteId).style.display = 'block';
          slider.style.display = 'block';
          slider.name = prompt.id;
          if (prompt.id in myResponses) {
            slider.value = "" + myResponses[prompt.id];
            setVoteStatus(true, true);
            shouldRevealStats = true;
          } else {
            slider.value = prompt.n_steps / 2;
            setVoteStatus(false);
          }

          var endVoteSelect = function () {
            myResponses[prompt.id] = slider.value - 0;  // Record locally since last page load
            dElem("vote_form_" + stagedVoteId).submit();
            setVoteStatus(true);
            revealStats();
          }

          var startVoteSelect = function () {
            setVoteStatus(false);
          }

          if (!isTouchDevice) {
            slider.onmousedown = startVoteSelect;
            slider.onmouseup = endVoteSelect;
          }

          slider.ontouchstart = function () {
            if (!isTouchDevice) {
              slider.onmousedown = () => {};
              slider.onmouseup = () => {};
              isTouchDevice = true;
            }
            startVoteSelect();
          }

          slider.ontouchend = function () {
            if (!isTouchDevice) {
              slider.onmousedown = () => {};
              slider.onmouseup = () => {};
              isTouchDevice = true;
            }
            endVoteSelect();
          }
        @endauth

        display_prompt(prompt.id, prompt.is_mapped, colorSteps);
        if (shouldRevealStats) {
          revealStats();
        }
      }

      function addClasses(elem, classes) {
        for (let i = 0; i < classes.length; i++) {
          elem.classList.add(classes[i]);
        }
      }

      function removeClasses(elem, classes) {
        for (let i = 0; i < classes.length; i++) {
          elem.classList.remove(classes[i]);
        }
      }

      function replaceClasses(elem, toRemove, toAdd) {
        removeClasses(elem, toRemove);
        addClasses(elem, toAdd);
      }

      function colorArrToHex(colArr) {
        // Expects [r, g, b] in 0--255
        return '#' + colArr.map(
          (v) => {var ret = v.toString(16); return v > 15 ? ret: '0' + ret;}
        ).join('');
      }

      function hexToColorArr(hexStr) {
        return [
          parseInt(hexStr.slice(1,3), 16),
          parseInt(hexStr.slice(3,5), 16),
          parseInt(hexStr.slice(5,7), 16),
        ];
      }

      var isTouchDevice = false;  // assume not touch, but change after first touch
      var stagedVoteId = null;
      const stagedClasses = ["border-orange-300"];
      const unstagedClasses = ["border-gray-200"];
      function stage_vote(promptId) {
        if (promptId == stagedVoteId || promptId == null) {
          if (stagedVoteId != null) {
            hidePromptContent(stagedVoteId);
          }
          stagedVoteId = null;
          display_prompt(null);
          votes_indicator.style.visibility = 'hidden';
          return;
        }
        if (stagedVoteId != null) {
          hidePromptContent(stagedVoteId);
        }
        stagedVoteId = promptId;
        votes_indicator.style.visibility = 'visible';
        showPromptContent();
      }

      const colorOptions = [[255,0,0], [0, 255, 0], [0, 0, 255], [255, 255, 0], [255, 0, 255], [0, 255, 255]];
      function getRandomColorCombo(cOptions=colorOptions) {
        const nOptions = cOptions.length;
        var index1 = Math.floor(Math.random() * nOptions);
        var index2 = Math.floor(Math.random() * (nOptions - 1));
        if (index2 >= index1) {
          index2++;
        }

        return [cOptions[index1], cOptions[index2]];
      }

      function revealStats() {
        dElem('stats_mask_' + stagedVoteId).style.display = 'none';
        allPrompts[stagedVoteId].has_been_opened = true;
      }

      const tag_poses = ['new', 'update', 'voter'];
      for (let tag_i = 0; tag_i < tag_poses.length; tag_i++) {
        var tag_pos = tag_poses[tag_i];
        for (let tag_id in allTags) {
          var parent_name = tag_pos + '-folder-' + allTagTypes[allTags[tag_id].tag_type].slug;
          var child_name = tag_pos + '-checkbox-' + allTags[tag_id].slug;
          dElem(parent_name).appendChild(dElem(child_name));
        }
      }

      function openVoterFolder(typeSlug) {
        var folderButton = dElem("voter-folder-button-" + typeSlug);
        var folderIcon = dElem("folder-icon-" + typeSlug);
        var folderContainer = dElem("voter-folder-" + typeSlug);

        folderButton.onclick = () => {closeVoterFolder(typeSlug);};
        folderIcon.innerText = "v";
        folderContainer.style['max-height'] = "801px";  // auto
      }

      openVoterFolder('general');

      function closeVoterFolder(typeSlug) {
        var folderButton = dElem("voter-folder-button-" + typeSlug);
        var folderIcon = dElem("folder-icon-" + typeSlug);
        var folderContainer = dElem("voter-folder-" + typeSlug);

        folderButton.onclick = () => {openVoterFolder(typeSlug);};
        folderIcon.innerText = ">";
        folderContainer.style['max-height'] = "0px";
      }

      function dot(vecA, vecB) {
        var s = 0;
        for (i = 0; i < vecA.length; i++) {
          s += vecA[i] * vecB[i];
        }
        return s;
      }

      function norm(vecA) {
        var s = 0;
        for (i = 0; i < vecA.length; i++) {
          s += vecA[i] * vecA[i];
        }
        return Math.sqrt(s);
      }

      function get_cosine_similarity(vecA, vecB) {
        return dot(vecA, vecB) / (norm(vecA) * norm(vecB));
      }

      function getCompatScore(properties, compatType='law') {
        var ret = {
          compatScore: null,
          srcVals: {}
        }

        var myVec = [];
        var lawVec = [];
        var agreeList = [];
        var disagreeList = [];
        const consideredPrompts = compatType == 'law' ? lawPromptIds : Object.keys(allPrompts);
        for (let i = 0; i < consideredPrompts.length; i++) {
          var pId = consideredPrompts[i];
          if (pId in myResponses) {
            var lawVal = properties['prompt-' + pId + '-all'];
            if (lawVal == -1) {
              continue;
            }
            var myVal = myResponses[pId];
            const myValHat = myVal - 5;
            const lawValHat = lawVal - 0.5;

            myVec.push(myValHat);
            lawVec.push(lawValHat);
            var summary = allPrompts[pId].summary;
            ret.srcVals[pId] = lawVal;
            if (myValHat * lawValHat > 0) {  // ignore 0
              agreeList.push(summary);
            } else if (myValHat * lawValHat < 0) {
              disagreeList.push(summary);
            }
          }
        }
        if (myVec.length == 0) {
          return ret;
        }

        var cosSim = get_cosine_similarity(myVec, lawVec);
        ret.compatScore = 100 * (cosSim + 1) / 2;
        return ret;
      }

      var cachedData = {'compatType': null, 'data': null};
      function displayCompatPopup(e, compatType='law') {
        if (e.features.length == 0) {
          return;
        }

        const properties = e.features[0].properties;
        if (cachedData.compatType == null) {
          cachedData.compatType = compatType;
          cachedData.data = properties;
          return;
        }

        if (compatType == 'law') {
          var lawProperties = properties;
          var voteProperties = cachedData.data;
        } else {
          var lawProperties = cachedData.data;
          var voteProperties = properties;
        }
        cachedData = {'compatType': null, 'data': null};

        const lawCompatResults = getCompatScore(lawProperties, compatType='law');
        const voteCompatResults = getCompatScore(voteProperties, compatType='vote');

        var locationName = ('country' in lawProperties) ? capitalize(lawProperties.country) : '-';
        createNewCompatPopup(e.lngLat, locationName, lawCompatResults, voteCompatResults);
      }

      function resetShareButtonText() {
        popupShareButtonSansDetails.innerText = "Share";
      }

      function doShareCompat() {
        const lawCompatScore = currentPopupData.lawCompatResults.compatScore;
        const voteCompatScore = currentPopupData.voteCompatResults.compatScore;
        const locationName = currentPopupData.locationName;

        var hasLaw = lawCompatScore != null;
        var hasVote = voteCompatScore != null;

        var shareString = `My world views agree ` +
          (hasVote ? `${Math.round(voteCompatScore)}% with the people ` : '') +
          ((hasVote && hasLaw) ? 'and ' : '') +
          (hasLaw ? `${Math.round(lawCompatScore)}% with the laws ` : '') +
          `in ${locationName}!\nFind yours at https://myworld.vote`;

        if (navigator.share) {
          navigator.share({'text': shareString})
        } else {
          navigator.clipboard.writeText(shareString);
          popupShareButtonSansDetails.innerText = "Copied!";
        }
      }

      function capitalize(text) {
        return text.split(' ').map(element => {
          return element.charAt(0).toUpperCase() + element.slice(1).toLowerCase();
        }).join(' ');
      }

      function lowerOrAcronym(elem) {
        return (elem[1] == elem[1].toLowerCase()) ? elem.toLowerCase() : elem.toUpperCase();
      }

      function capitalizeOrAcronym(elem) {
        return (elem[1] == elem[1].toLowerCase()) ? capitalize(elem) : elem.toUpperCase();
      }

      var currentCompatPopup = null;
      function removeCompatPopup() {
        if (currentCompatPopup != null) {
          currentCompatPopup.remove();
        }
      }

      function setPopupView(viewName) {
        if (viewName == 'details') {
          dElem('popupViewDetails').style.display = 'inline';
          dElem('popupViewSummary').style.display = 'none';
        } else {
          dElem('popupViewDetails').style.display = 'none';
          dElem('popupViewSummary').style.display = 'inline';
        }
        resetShareButtonText();
      }

      function setPopupAgreeTabVisible(makeAgreeVisible) {
        var enabledClasses = ['bg-white', 'text-orange-300'];
        var disabledClasses = ['bg-orange-300', 'hover:bg-orange-500', 'text-white'];

        var enabledButton = 'popupDisagreeButton';
        var disabledButton = 'popupAgreeButton';
        var agreeDisp = 'none';
        var disagreeDisp = 'inline';
        if (makeAgreeVisible) {
          var agreeDisp = 'inline';
          var disagreeDisp = 'none';
          var enabledButton = 'popupAgreeButton';
          var disabledButton = 'popupDisagreeButton';
        }
        popupAgreeTabContent.style.display = agreeDisp;
        popupDisagreeTabContent.style.display = disagreeDisp;

        replaceClasses(dElem(enabledButton), disabledClasses, enabledClasses);
        replaceClasses(dElem(disabledButton), enabledClasses, disabledClasses);
      }

      function insertIntoSorted(sortedArr, lval, label) {
        for (var dst_i = 0; dst_i < sortedArr.length; dst_i++) {
          if (lval < sortedArr[dst_i][0]) {
            break;
          }
        }
        return sortedArr.slice(0, dst_i)
                        .concat([[lval, label]])
                        .concat(
                          sortedArr.slice(dst_i, sortedArr.length)
                        );
      }

      function getCompatEntry(cd) {
        var labelWidthPx = 10;
        var labelHeightPx = 10;
        var lineWidthPerc = 65;
        var lineMLPerc = (100 - lineWidthPerc) / 2;
        var markerColors = {
          'my': '#ff9d47',
          'laws': '#bbbbbb',
          'votes': '#0000cc'
        };
        var markerLabels = {
          'my': 'Me',
          'laws': 'Laws',
          'votes': 'Voters'
        };
        var markerAlpha = '80';

        var ret = `
          <h3>${cd.summary}</h3>
          <div style="width:100%; height:${labelHeightPx}px">
            <span style="float:left; height:${labelHeightPx}px; margin-top:-2px; font-size:${labelHeightPx}px">${allPrompts[cd.pId].option0}</span>
            <span style="float:right; height:${labelHeightPx}px; margin-top:-2px; margin-right:2px; font-size:${labelHeightPx}px">${allPrompts[cd.pId].option1}</span>
          </div>
          <div style="margin-left:${lineMLPerc}%; width:${lineWidthPerc}%; height:${labelHeightPx}px; margin-top:${-labelHeightPx/2 - 1}px; padding-top:${labelHeightPx/2 - 1}px"><div style="border-width:1px; height:1px"></div></div>
          <div style="margin-left:${lineMLPerc}%; width:${lineWidthPerc}%; height:${labelHeightPx}px; margin-top:${-labelHeightPx}px"><div style="width:${labelWidthPx}px; height:100%; margin-left:calc(${cd.myResponse * 100}% - ${labelWidthPx/2}px); border-radius:${labelWidthPx/2}px; background-color:${markerColors.my}${markerAlpha}"></div></div>` +
          ((cd.lawVal == null) ? '' : `<div style="margin-left:${lineMLPerc}%; width:${lineWidthPerc}%; height:${labelHeightPx}px; margin-top:${-labelHeightPx}px"><div style="width:${labelWidthPx}px; height:100%; border-radius:${labelWidthPx/2}px; margin-left:calc(${cd.lawVal * 100}% - ${labelWidthPx/2}px); background-color:${markerColors.laws}${markerAlpha}"></div></div>`) +
          ((cd.voteVal == null) ? '' : `<div style="margin-left:${lineMLPerc}%; width:${lineWidthPerc}%; height:${labelHeightPx}px; margin-top:${-labelHeightPx}px"><div style="width:${labelWidthPx}px; height:100%; border-radius:${labelWidthPx/2}px; margin-left:calc(${cd.voteVal * 100}% - ${labelWidthPx/2}px); background-color:${markerColors.votes}${markerAlpha}"></div></div>`);

        var labels = [[cd.myResponse, 'my']];
        if (cd.lawVal != null) {
          labels = insertIntoSorted(labels, cd.lawVal, 'laws');
        }
        if (cd.voteVal != null) {
          labels = insertIntoSorted(labels, cd.voteVal, 'votes');
        }

        const nLabelCells = 9;
        var cellContents = [];
        for (let i = 0; i < nLabelCells; i++) {
          cellContents.push(null);
        }
        const nLabels = labels.length;
        var maxCellUsed = -1;
        for (let i = 0; i < nLabels; i++) {
          var idealCell = Math.floor(labels[i][0] * nLabelCells);
          var maxDst = nLabelCells - (nLabels - i);
          var setCell = Math.max(maxCellUsed + 1, Math.min(maxDst, idealCell));
          cellContents[setCell] = i;
          maxCellUsed = setCell;
        }

        const labelRowWidthPerc = Math.min(100, lineWidthPerc + 15);
        const labelRowMarginPerc = (100 - labelRowWidthPerc) / 2;
        const labelCellWidthPerc = labelRowWidthPerc / nLabelCells;

        var labelRow = `<table style="margin-bottom:5px; width:${labelRowWidthPerc}%; margin-left:${labelRowMarginPerc}%"><tr>`;
        for (let i = 0; i < nLabelCells; i++) {
          var lbl = (cellContents[i] == null) ? null : labels[cellContents[i]][1];
          labelRow += `
            <td style="width:${labelCellWidthPerc}%; text-align:center">
              ${(lbl == null) ? '' : `<span style="font-size:10px; color:${markerColors[lbl]}" class="tracking-tight">${markerLabels[lbl]}</span>`}
            </td>`;
        }
        labelRow += `</tr></table>`;

        ret += labelRow;
        return ret;
      }

      var currentPopupData = null;
      function createNewCompatPopup(lngLat, locationName, lawCompatResults, voteCompatResults) {
        if (lawCompatResults.compatScore == null && voteCompatResults.compatScore == null) {
          return;
        }

        removeCompatPopup();

        currentPopupData = {
          "locationName": locationName,
          "lawCompatResults": lawCompatResults,
          "voteCompatResults": voteCompatResults
        }

        const allPromptIds = Object.keys(allPrompts);
        var compatBarData = [];
        for (let i = 0; i < allPromptIds.length; i++) {
          var promptId = allPromptIds[i];
          if (!(promptId in myResponses)) {
            continue;
          }

          compatBarData.push({
            'pId': promptId,
            'summary': capitalizeOrAcronym(allPrompts[promptId].summary),
            'myResponse': myResponses[promptId] / 10.0,
            'lawVal': lawCompatResults.srcVals[promptId],
            'voteVal': voteCompatResults.srcVals[promptId]
          });
        }

        var compatHtmlEntries = [];
        for (let j = 0; j < compatBarData.length; j++) {
          compatHtmlEntries.push(getCompatEntry(compatBarData[j]));
        }
        const compatHtml = compatHtmlEntries.join('\n');

        var lawCompatScore = lawCompatResults.compatScore == null ? '-' : Math.round(lawCompatResults.compatScore);
        var voteCompatScore = voteCompatResults.compatScore == null ? '-' : Math.round(voteCompatResults.compatScore);

        const popupHtml = `
<div id="popupViewSummary" style="display:inline">
  <h3 style="width:100%; text-align:center">You agree</h3>
  <h3 style="width:100%; text-align:center" class="text-lg font-medium text-gray-900">${voteCompatScore}%</h3>
  <h3 style="width:100%; text-align:center">with the people, and</h3>
  <h3 style="width:100%; text-align:center" class="text-lg font-medium text-gray-900">${lawCompatScore}%</h3>
  <h3 style="width:100%; text-align:center">with the laws in</h3>
  <h3 style="width:100%; text-align:center; margin-bottom:10px" class="text-lg font-medium text-gray-900">${locationName}</h3>
  <button
    id="popupShareButtonSansDetails"
    style="width:150px; margin-bottom:5px"
    class="bg-orange-300 hover:bg-orange-500 text-white tracking-tight rounded"
    onclick="doShareCompat()"
  >
    Share
  </button>
  <br>
  <button
    style="width:150px"
    class="bg-orange-300 hover:bg-orange-500 text-white tracking-tight rounded"
    onclick="setPopupView('details')"
  >
    Details
  </button>
</div>

<div id="popupViewDetails" style="display:none">
  <h3 style="width:100%; text-align:center; margin-bottom:5px" class="font-semibold">Details</h3>

  <div style="height:144px; margin-bottom:10px; overflow-y:scroll">
    ${compatHtml}
  </div>

  <button
    style="width:150px"
    class="bg-orange-300 hover:bg-orange-500 text-white tracking-tight rounded"
    onclick="setPopupView('summary')"
  >
    Back
  </button>
</div>
`;

        var newPopup = new mapboxgl.Popup()
          .setLngLat(lngLat)
          .setHTML(popupHtml);
        currentCompatPopup = newPopup;
        newPopup.addTo(map);
      }

      function displayCompatPopupLaw(e) {
        displayCompatPopup(e, 'law');
      }

      function displayCompatPopupVote(e) {
        displayCompatPopup(e, 'vote');
      }

      function initCompatHandler() {
        map.on('click', 'tags_law', displayCompatPopupLaw);
        map.on('click', 'tags_vote', displayCompatPopupVote);
      }

      function removeCompatHandler() {
        map.off('click', 'tags_law', displayCompatPopupLaw);
        map.off('click', 'tags_vote', displayCompatPopupVote);
      }

      // Display a popup after: the last moveend PLUS some offset (e.g. 50ms)
      function triggerPopup() {
        map.fire('click', {
          lngLat: popupLngLat,
          point: map.project(popupLngLat),
          originalEvent: {}
        });
      }

      var popupWatchdogFood = -1;
      var currentPopupInterval = null;
      var popupIntervalMs = 150;  // will wait at MOST 2 x this interval
      function eatPopupWatchdog() {
        if (popupWatchdogFood == 1) {
          popupWatchdogFood = 0;
        } else if (popupWatchdogFood == -1) {
          console.log("Invalid state encountered");
        } else { // == 0 (eaten previously, and now eaten into the negatives)
          popupWatchdogFood = -1;
          clearInterval(currentPopupInterval);
          isFlying = false;
          triggerPopup();
        }
      }

      function feedPopupWatchdog() {
        if (popupWatchdogFood < 0) {  // start watchdog
          currentPopupInterval = setInterval(eatPopupWatchdog, popupIntervalMs);
        }
        popupWatchdogFood = 1;
      }

      var isFlying = false;
      var popupLngLat = null;
      map.on('moveend', function(e){
        if (isFlying) {
          feedPopupWatchdog();
        }
      });

      function jumpToCompat(compatType='law') {
        if (compatType == 'vote') {
          if (!canDisplayVoteCompatOrAlert()) {
            return;
          }
        } else {
          if (!canDisplayLawCompatOrAlert()) {
            return;
          }
        }
        stage_vote(null);
        set_pane_poll_mode('voter');
        openVoterFolder('general');
        if (stagedVoterId != null) {
          unstageVoter(stagedVoterId);
        }
        stageVoter('comp_' + compatType);
        var maxZoomStep = zSteps[maxZoom];
        popupLngLat = [oLng + (myCol + 0.5) * maxZoomStep, oLat - (myRow + 0.5) * maxZoomStep];  // lngLat
        isFlying = true;
        map.flyTo({center: popupLngLat});
      }
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

  <!-- Needed for the current workaround/hack to give feedback on sliders -->
  <style data="test" type="text/css"></style>
  <!-- See above -->
</html>
