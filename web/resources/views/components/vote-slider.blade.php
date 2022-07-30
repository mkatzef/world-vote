@props(['promptId', 'voteValue' => '5'])

@once
<style>
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
      outline: none; /* Remove outline */
      opacity: 0.6; /* Set transparency (for mouse-over effects on hover) */
      -webkit-transition: .2s; /* 0.2 seconds transition on hover */
      transition: opacity .2s;
      margin: 0;
    }

    /* Mouse-over effects */
    .slider:hover {
      opacity: 0.3; /* Fully shown on mouse-over */
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
@endonce

<div class="slidecontainer" style="position:relative">
    <div id="vote-slider-bg" style="position:absolute;height:25px;width:100%"></div>
    <input type="range" class="slider" style:"position:absolute;top:-25px" id="vote-slider" min="0" max="10" value="{{ $voteValue }}"/>
</div>
