@props(['tag', 'prefix' => null])

<li>
    <input type="checkbox" id="{{$prefix ? $prefix.'-' : ''}}checkbox-{{ $tag->slug }}" name="{{ $tag->slug }}" class="hidden peer">
    <label for="{{$prefix ? $prefix.'-' : ''}}checkbox-{{ $tag->slug }}" class="inline-flex justify-between items-center p-5 w-full text-gray-500 bg-white rounded-lg border-2 border-gray-200 cursor-pointer dark:hover:text-gray-300 dark:border-gray-700 peer-checked:border-blue-600 hover:text-gray-600 dark:peer-checked:text-gray-300 peer-checked:text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:bg-gray-800 dark:hover:bg-gray-700">
        <div class="block">
            <div class="w-full text-lg font-semibold">{{ $tag->name }}</div>
        </div>
    </label>
</li>
