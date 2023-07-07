<div class="d-flex flex-column bd-highlight mb-3 body-area__innerContent">
    <div class="d-flex flex-column">
        <div class="p-2">

            <input type="text" class="form-control" placeholder="Search"
                   aria-label="File Search" wire:model="searchTerm">

        </div>
        <div class="p-3 {{$isLoading ? '' : 'visually-hidden' }}">
            <div class="d-flex justify-content-center">
                <div class="spinner-border text-synth">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
        <div class="p-2 flex-grow-1">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th scope="col">File</th>
                    <th scope="col">Options</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($foundFiles as $file)

                    <tr wire:key="file-{{$file}}">
                        <th scope="row">
                            {{$file}}
                        </th>
                        <td>
                            <button type="button" class="btn btn-secondary btn-sm"
                                    {{$isLoading ? 'disabled' : ''}}
                                    wire:click="$emit('doCommand', {'command': 'doOptimize', 'payload': {'file': '{{$file}}'}})">
                                Optimize
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
