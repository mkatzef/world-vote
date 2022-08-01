@props(['tag', 'prefix' => null])

<li>
    <input type="checkbox" id="{{$prefix ? $prefix.'-' : ''}}checkbox-{{ $tag->slug }}" name="{{ $tag->slug }}" class="hidden peer">
    <label for="{{$prefix ? $prefix.'-' : ''}}checkbox-{{ $tag->slug }}"
      class="inline-flex justify-between items-center p-3 w-full text-gray-500
      bg-white rounded-lg border-4 border-gray-200 cursor-pointer peer-checked:border-orange-600 hover:text-gray-600 peer-checked:text-gray-600 hover:bg-gray-50">
        <div class="block">
            <div class="w-full text-lg font-semibold">{{ $tag->name }}</div>
        </div>
    </label>
</li>
