@props(['slug', 'name'])

<div
  class="hover:bg-gray-200 m-2 mt-6 p-2 rounded"
  style="text-align:left; width:90%; margin-left:5%"
>
  <fieldset style="border-top:2px solid orange">
    <legend>
      <span id="folder-icon-{{ $slug }}" style="color:orange">
        >
      </span>
      <span>{{ $name }}&nbsp;</span>
    </legend>
  </fieldset>
</div>
