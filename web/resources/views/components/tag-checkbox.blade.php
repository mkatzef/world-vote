@props(['tag', 'prefix' => null])

<li>
    <input type="checkbox" id="{{$prefix ? $prefix.'-' : ''}}checkbox-{{ $tag->slug }}" name="{{ $tag->slug }}" class="hidden peer">
    <label for="{{$prefix ? $prefix.'-' : ''}}checkbox-{{ $tag->slug }}"
      class="
      peer-checked:border-orange-600 peer-checked:text-gray-600
       tracking-tight text-gray-900
        block bg-white rounded-lg shadow-md hover:bg-gray-100
        m-2 border-4 border-gray-200 button_transition">
        <div class="block">
            <div class="text-2xl font-bold p-2">{{ $tag->name }}</div>
        </div>
    </label>
</li>
