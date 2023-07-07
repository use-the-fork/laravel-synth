<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>A Basic HTML5 Template</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    @include('synth::partials.styles')
    @livewireStyles
</head>

<body>

@livewire('synth-chat')

@livewireScripts

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
        crossorigin="anonymous"></script>
<script>
    document.getElementById("synth_chat_input")
        .addEventListener("keyup", function (event) {
            event.preventDefault();
            if (event.keyCode === 13) {
                switch (event.target.value) {
                    case '/edit' || '/e':
                        Livewire.emit('doChatCommand', 'doChatEdit');
                        break;
                    case '/commit' || '/c':
                        Livewire.emit('doChatCommand', 'doCommit');
                        break;
                    default:
                        Livewire.emit('doChatCommand', 'doChat');
                }
                //Livewire.emit('postAdded');
                console.log(event.target.value);
            }
        });
</script>

</body>
</html>
