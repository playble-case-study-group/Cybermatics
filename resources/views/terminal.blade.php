@extends('layouts.app')
@section('header-script')


    <link rel="import" href="/poly/cm-terminal.html" async>
    <script src="/js/bash-emulator.min.js"></script>
    <script src="/js/sha256.min.js"></script>
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

@endsection

@section('content')
    <cm-terminal id="terminal"></cm-terminal>
@endsection