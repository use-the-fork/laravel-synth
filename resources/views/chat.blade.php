@extends('synth::layouts.app')

@section('title', 'Chat')

@section('content')
    @livewire('synth-chat')
@endsection

@section('extra-content')
    <div class="modal fade modal-xl" wire:ignore id="AttachFileModal" tabindex="-1"
         aria-labelledby="AttachFileModal"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="AttachFileModal">Select Files To Attach</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @livewire('synth-attach-files')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        var attachFileModal = new bootstrap.Modal(document.getElementById('AttachFileModal'));
        document.getElementById("synth_chat_input").addEventListener("keyup", function (event) {
            event.preventDefault();
            if (event.keyCode === 13) {
                switch (event.target.value) {
                    case '/edit' || '/e':
                        Livewire.emit('doChatCommand', 'doChatEdit');
                        break;
                    case '/commit' || '/c':
                        Livewire.emit('doChatCommand', 'doCommit');
                        break;
                    case '/attach' || '/a':
                        attachFileModal.show();
                        break;
                    default:
                        Livewire.emit('doChatCommand', 'doChat');
                }
                console.log(event.target.value);
            }
        });
    </script>
@endsection

