<div>
    <div class="modal fade modal-xl" wire:ignore.self id="ApproveFileModal" tabindex="-1"
         aria-labelledby="ApproveFileModal"
         aria-hidden="true">
        <div class="modal-dialog" wire:ignore.self>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Changes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="d-flex flex-column">
                        <div class="p-2">
                            <input type="text" class="form-control" placeholder="Write Path"
                                   aria-label="Write Path" wire:model="file">
                        </div>
                    </div>
                    <div class="p-2 flex-grow-1">
                        {!! $prettyContent !!}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="doApproveFile">Approve</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const approveFileModal = new bootstrap.Modal(document.getElementById('ApproveFileModal'));
        Livewire.on('doShowApproveFile', function () {
            approveFileModal.show();
        });
        Livewire.on('doHideApproveFile', function () {
            approveFileModal.hide();
        });
    </script>
</div>

