<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1, initial-scale=1, user-scalable=yes">

    <title>CyberMatics</title>
    <meta name="description" content="Portal to the CyberMatics Penetration Testing system">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="/images/cybermatics.ico">
    <link rel="import" href="/poly/cm-terminal.html" async>
    <script src="/js/bash-emulator.min.js"></script>
    <script src="/js/sha256.min.js"></script>
    {{--<script src="{{ asset('js/app.js') }}"></script>--}}
    <script>
        window.Polymer = {
            dom: 'shadow',
            lazyRegister: true,
        };
        (function() { 'use strict';

            var webComponentsSupported = (
                'registerElement' in document
                && 'import' in document.createElement('link')
                && 'content' in document.createElement('template'))

            if (!webComponentsSupported){
                var script = document.createElement('script')
                script.async = true
                script.src = '/bower_components/webcomponentsjs/webcomponents-lite.min.js'
                script.onload = onload
                document.head.appendChild(script)
            } else{
                if (!window.HTMLImports){
                    document.dispatchEvent(new CustomEvent('WebComponentsReady', {bubbles: true}))
                }
            }
        })()
    </script>
    <style>
        body {
            margin: 0;
            font-family: 'Roboto', 'Noto', sans-serif;
            line-height: 1.5;
            min-height: 100vh;
        }
    </style>
</head>
<body>
<cm-terminal id="terminal"></cm-terminal>
</body>
</html>
