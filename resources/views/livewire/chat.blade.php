<div class="d-flex flex-column bd-highlight mb-3 body-area__innerContent">
    <div class="d-flex flex-grow-1 flex-column">

        @foreach ($chatHistory as $history)
            @include('synth::livewire.chat-response', ['history' => $history])
        @endforeach

        <div
            class="response response--is-assistant p-3 {{$isLoading ? '' : 'visually-hidden' }}"
        >
            <div class="d-flex justify-content-center">
                <div class="spinner-border text-synth">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>


    </div>
    <div class="p-2 mb-4 body-area__chatBox">
        <div class="input-group">
            <input type="text" class="form-control" placeholder="start chat"
                   aria-label="Input chat" aria-describedby="button-addon2"
                   id="synth_chat_input"
                   wire:model="chatText"
            >
        </div>
        <div id="chatHelp" class="form-text text-white"><kbd>/attach</kbd> to attach files, <kbd>/edit</kbd> to edit
            last user message,
            <kbd>/commit</kbd>
            to
            accept
            and generate the file.
        </div>
    </div>
</div>
