<div>
    <div class="d-flex response response--is-{{$history['role']}} p-3 gap-3">
        <div class="d-flex flex-column align-items-end flex-shrink-0 response__role">
                                <span class="badge bg-secondary ">
                                    {{$history['role']}}
                                </span>
        </div>
        <div class="d-flex flex-column response__content">
            <x-markdown>
                {{ $history['content'] }}
            </x-markdown>
        </div>
    </div>
</div>
