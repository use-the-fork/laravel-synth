<div>
    <div class="d-flex flex-column">
        <div class="p-2">

            <input type="text" class="form-control" placeholder="Search"
                   aria-label="File Search" wire:model="searchTerm">

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
                            @if(isset($attachedFiles[$file]))
                                <button type="button" class="btn btn-danger btn-sm"
                                        wire:click="$emit('doRemoveFile', '{{$file}}')">Remove
                                </button>
                            @else
                                <button type="button" class="btn btn-success btn-sm"
                                        wire:click="$emit('doAttachFile', '{{$file}}')">Attach
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
