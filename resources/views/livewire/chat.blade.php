<div>
    <div>
        <div class="container-fluid">
            <div class="row flex-nowrap">
                <div class="col-md-2 px-0 side-bar">
                    <div
                        class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100">
                    <span
                        class="d-flex align-items-center pt-1 md-0 md-auto text-white text-decoration-none">
                        <span class="logo d-sm-inline">
                            <pre>
░░░░░░░ ░░    ░░ ░░░    ░░ ░░░░░░░░ ░░   ░░
▒▒       ▒▒  ▒▒  ▒▒▒▒   ▒▒    ▒▒    ▒▒   ▒▒
▒▒▒▒▒▒▒   ▒▒▒▒   ▒▒ ▒▒  ▒▒    ▒▒    ▒▒▒▒▒▒▒
     ▓▓    ▓▓    ▓▓  ▓▓ ▓▓    ▓▓    ▓▓   ▓▓
███████    ██    ██   ████    ██    ██   ██
                            </pre>
                        </span>
                    </span>
                        <span
                            class="d-flex flex-column pb-3 pt-1 md-0 md-auto text-synth text-decoration-none border-bottom">
                            <span>System Stats</span>
                            <span>Model: {{ $system['model'] }}</span>
                            <span>Tokens: {{ $system['tokens'] }} ({{ $system['percent'] }}%)</span>
                            <span>Files: {{ $system['files'] }}</span>
                        </span>

                        <ul class="nav nav-pills flex-column mb-0 align-items-center align-items-sm-start"
                            id="menu">
                            <li class="nav-item">
                                <a href="#" class="nav-link align-middle px-0 text-white">
                                    <i class="bi-house"></i> <span class="ms-1 d-none d-sm-inline">Home</span>
                                </a>
                            </li>
                            <li>
                                <button data-bs-toggle="modal" data-bs-target="#AttachFileModal"
                                        class="nav-link px-0 align-middle text-white">
                                    <i class="bi-speedometer2"></i> <span
                                        class="ms-1 d-none d-sm-inline">Attach Files</span></button>
                            </li>
                            <li>
                                <a href="#" class="nav-link px-0 align-middle text-white">
                                    <i class="bi-table"></i> <span class="ms-1 d-none d-sm-inline">Orders</span></a>
                            </li>
                            <li>
                                <a href="#submenu2" data-bs-toggle="collapse"
                                   class="nav-link px-0 align-middle text-white">
                                    <i class="bi-bootstrap"></i> <span class="ms-1 d-none d-sm-inline">Bootstrap</span></a>
                                <ul class="collapse nav flex-column ms-1" id="submenu2" data-bs-parent="#menu">
                                    <li class="w-100">
                                        <a href="#" class="nav-link px-0"> <span class="d-none d-sm-inline">Item</span>
                                            1</a>
                                    </li>
                                    <li>
                                        <a href="#" class="nav-link px-0"> <span class="d-none d-sm-inline">Item</span>
                                            2</a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="#submenu3" data-bs-toggle="collapse"
                                   class="nav-link px-0 align-middle text-white">
                                    <i class="bi-grid"></i> <span class="ms-1 d-none d-sm-inline">Products</span> </a>
                                <ul class="collapse nav flex-column ms-1" id="submenu3" data-bs-parent="#menu">
                                    <li class="w-100">
                                        <a href="#" class="nav-link px-0"> <span
                                                class="d-none d-sm-inline">Product</span> 1</a>
                                    </li>
                                    <li>
                                        <a href="#" class="nav-link px-0"> <span
                                                class="d-none d-sm-inline">Product</span> 2</a>
                                    </li>
                                    <li>
                                        <a href="#" class="nav-link px-0"> <span
                                                class="d-none d-sm-inline">Product</span> 3</a>
                                    </li>
                                    <li>
                                        <a href="#" class="nav-link px-0"> <span
                                                class="d-none d-sm-inline">Product</span> 4</a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="#" class="nav-link px-0 align-middle text-white">
                                    <i class="bi-people"></i> <span class="ms-1 d-none d-sm-inline">Customers</span>
                                </a>
                            </li>
                        </ul>
                        <hr>
                        <div class="dropdown pb-4">
                            <a href="#"
                               class="d-flex align-items-center text-white text-decoration-none dropdown-toggle text-white"
                               id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="https://github.com/mdo.png" alt="hugenerd" width="30" height="30"
                                     class="rounded-circle">
                                <span class="d-none d-sm-inline mx-1">remove me</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark text-small shadow"
                                aria-labelledby="dropdownUser1">
                                <li><a class="dropdown-item" href="#">New project...</a></li>
                                <li><a class="dropdown-item" href="#">Settings</a></li>
                                <li><a class="dropdown-item" href="#">Profile</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="#">Sign out</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col py-3 body-area">
                    <div class="d-flex flex-column bd-highlight mb-3 body-area__innerContent">
                        <div class="d-flex flex-grow-1 flex-column">

                            @foreach ($chatHistory as $history)
                                @include('synth::livewire.chat-response', ['history' => $history])
                            @endforeach

                            <div
                                class="d-flex response response--is-assistant p-3 gap-3 {{$isLoading ? '' : 'visually-hidden' }}"
                            >
                                <div class="d-flex flex-column align-items-end flex-shrink-0 response__role">
                                <span class="badge bg-secondary ">
                                    Assistant
                                </span>
                                </div>
                                <div class="d-flex flex-column response__content">
                                    &nbsp;
                                </div>
                                <div class="d-flex flex-column response__loading">
                                    <div class="d-flex align-items-center">
                                        <div class="spinner-border ms-auto" role="status" aria-hidden="true"></div>
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
                            <div id="emailHelp" class="form-text text-white">/edit to edit last response, /commit to
                                accept
                                and generate the file.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
</div>
