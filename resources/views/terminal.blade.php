@extends('layouts.app')

@section('content')
    <link rel="import" href="js/bower_components/polymer/index.html">

    <link rel="import" href="js/bower_components/iron-ajax/iron-ajax.html">
    <link rel="import" href="js/bower_components/app-storage/index.html">

    <dom-module id="polymer-terminal">
        <template>
            <style>
                :host {
                    display: block;
                }
                header {
                    background-color: var(--app-primary-color);
                    color: #fff;
                    padding: 8px;
                    margin-top: 20px;
                }
                #terminal {
                    color: white;
                    min-height: 500px;
                    height: 100%;
                    width: 100%;
                    margin: 0;
                    padding: 0;
                    color: lime;
                }
                #output {
                    white-space: pre;
                    max-height: 500px;
                    overflow-y: auto;
                    overflow-x: hidden;
                    padding-left: 5px;
                }
                #terminal, #input {
                    background: black;
                    font-family: monospace;
                    line-height: 120%;
                }
                #input {
                    border: none;
                    font-size: inherit;
                    color: lime;
                    width: 50%;
                }
                #input:focus {
                    outline: none;
                }
                .input-wrap {
                    color: lime;
                    padding-left: 5px;
                }
                .error {
                    color: red;
                }
            </style>

            <app-localstorage-document

                    name="user-storage"
                    value="{{ 0 }}"
                    on-iron-localstorage-load="init">
            </app-localstorage-document>

            <iron-ajax
                    id="ajaxGetHosts"
                    url="/sim/hosts"
                    method="get"
                    handle-as="json"
                    on-response="loadTerminal">
            </iron-ajax>

            <div>
                <header>Terminal</header>
                <div id="terminal">
                    <div id="output"></div>
                    <div class="input-wrap">
                        <span>[[input_prefix]]</span>
                        <input id="input" type="text" autofocus spellcheck="false">
                    </div>
                </div>
            </div>
        </template>
        <script src="/js/bash-emulator.min.js"></script>
        <script src="/js/sha256.min.js"></script>
        <script>
            Polymer({
                is: 'cm-terminal',
                ready: function (){
                    var myThis = this
                    this.base = document.querySelector('#base')
                    this.attachHandlers()
                    this.ENTER = 13
                    this.UP = 38
                    this.DOWN = 40
                    this.CTLCHAR = '^C'
                    this.nextCmd = null
                    this.passwdInput = false
                    this.passBuffer = ''
                    this.passAttempts = 0
                    this.STARTING_HOST = 'cybermatics.io'
                    this.STARTING_USER = 'junior'
                    this.WORD_LIST = '/home/junior/wordlist.txt'
                    this.current_user = ''
                    this.current_host = ''
                    this.past_users = []
                    this.past_hosts = []
                    this.completeFunctions = {}
                    this.input_prefix = ''
                    this.input = this.$$('#input')
                    this.output = this.$$('#output')
                    this.customCommands = {
                        'clear': this.clear.bind(this),
                        'sudo': this.sudo.bind(this),
                        'ssh': this.ssh.bind(this),
                        'hostname': this.hostname.bind(this),
                        'exit': this.exit.bind(this),
                        'logout': this.logout.bind(this),
                        'id': this.id.bind(this),
                        'hashcrack': this.hashcrack.bind(this),
                        'locate': this.locate.bind(this)
                    }
                    this.input.addEventListener('keydown', function (e){
                        //ctr-c
                        if(e.ctrlKey && e.keyCode === 67) {
                            e.preventDefault()
                            if(myThis.passwdInput){
                                myThis.log(myThis.input_prefix)
                                myThis.log(myThis.passAttempts + ' incorrect password attempt' + (myThis.passAttempts > 1 ? 's': ''))
                            }else{
                                myThis.log(myThis.input_prefix + myThis.input.value + myThis.CTLCHAR)
                            }
                            myThis.reset()
                            myThis.input.value = ''
                            return
                        }
                        if(myThis.blockInput) return e.preventDefault()
                        //enter
                        if (e.which === myThis.ENTER){
                            e.preventDefault()
                            if(myThis.blockInput) return
                            if(myThis.passwdInput){
                                myThis.run(myThis.passBuffer).then(function (){
                                    myThis.input.value = ''
                                    myThis.output.scrollTop = myThis.output.scrollHeight
                                })
                            }else{
                                if (!myThis.input.value.length) return
                                myThis.run(myThis.input.value).then(function (){
                                    myThis.input.value = ''
                                    myThis.output.scrollTop = myThis.output.scrollHeight
                                })
                            }
                            return
                        }
                        //if tab just refocus
                        if (e.keyCode == 9) return e.preventDefault()
                        //if passwd don't show text
                        if (myThis.passwdInput){
                            var keycode = e.keyCode
                            //backspace
                            if (keycode === 8) return myThis.passBuffer = myThis.passBuffer.slice(0, -1)
                            //check if is valid password character
                            var valid =
                                (keycode > 47 && keycode < 58)   || // number keys
                                keycode == 32 || keycode == 13   || // spacebar & return key(s)
                                (keycode > 64 && keycode < 91)   || // letter keys
                                (keycode > 95 && keycode < 112)  || // numpad keys
                                (keycode > 185 && keycode < 193) || // ;=,-./` (in order)
                                (keycode > 218 && keycode < 223);   // [\]' (in order)
                            if(!valid) return
                            e.preventDefault()
                            myThis.passBuffer += e.key
                            return
                        }
                        if (e.which === myThis.UP || e.which === myThis.DOWN){
                            e.preventDefault()
                            myThis.complete(e.which)
                            return
                        }
                    })
                    document.body.addEventListener('click', function (){
                        myThis.input.focus()
                    })
                },
                attachHandlers: function (){
                    var myThis = this
                    if(this.base){
                        this.base.addEventListener('user_loggedin', function (){
                            myThis.init()
                        })
                    }else{
                        this.base = document.querySelector('#base')
                        this.attachHandlers()
                    }
                },
                init: function (){
                    this.$.ajaxGetHosts.generateRequest()
                },
                loadTerminal: function (e){
                    this.hosts = e.detail.response.hosts
                    this.current_host = this.hosts[this.STARTING_HOST]
                    this.current_user = this.current_host.users[this.STARTING_USER]
                    this.terminalInit()
                },
                terminalInit: function (){
                    this.set('input_prefix', this.current_user.name+'@'+this.current_host.hostname+'$ ')
                    this.log('Welcome to ' + this.current_host.version)
                    this.emulator = bashEmulator({
                        workingDirectory: this.current_user.home_directory,
                        homeDirectory: this.current_user.home_directory,
                        fileSystem: this.current_host.fs,
                        user: this.current_user.name,
                        users: this.current_host.users
                    })
                    this.completeFunctions[this.UP] = this.emulator.completeUp
                    this.completeFunctions[this.DOWN] = this.emulator.completeDown
                },
                resetPrefix: function (){
                    this.set('input_prefix', this.current_user.name+'@'+this.current_host.hostname+'$ ')
                },
                log: function (result){
                    if(!result) return
                    this.output.innerHTML += result + '\n'
                },
                run: function (cmd){
                    //allow for multi line execution such as password prompt
                    if(this.nextCmd) return this.nextCmd(cmd)
                    var first = cmd.split(' ')[0]
                    //for cmds that don't interact with the emulator
                    if(this.customCommands[first]){
                        this.emulator.addToHistory(cmd)
                        return this.customCommands[first](cmd)
                    }
                    this.log(this.input_prefix + cmd)
                    return this.emulator.run(cmd).then(this.log.bind(this), this.log.bind(this))
                },
                complete: function (direction){
                    var completeFunction = this.completeFunctions[direction]
                    if (!completeFunction) return
                    var myThis = this
                    var cursorPosition = this.input.selectionStart
                    var beforeCursor = this.input.value.slice(0, cursorPosition)
                    completeFunction(beforeCursor).then(function (completion){
                        if (completion){
                            myThis.input.value = completion
                            myThis.input.setSelectionRange(cursorPosition, cursorPosition)
                        }
                    })
                },
                clear: function (){
                    this.output.innerHTML = ''
                    return Promise.resolve()
                },
                ssh: function (cmd){
                    this.log(this.input_prefix + cmd)
                    var cmd_parts = cmd.split(' ')
                    var userOrHost = cmd_parts[1].split('@')
                    //matches 'ssh hostname', use current username
                    if(userOrHost.length == 1){
                        var host = userOrHost[0]
                        if(!this.hosts[host]){
                            this.log('ssh: Could not resolve hostname '+host+': Name or service not known')
                            return Promise.resolve()
                        }
                        this.sshPass(this.current_user.name, host)
                        //matches 'ssh user@hostname'
                    }else if(userOrHost.length == 2){
                        var user = userOrHost[0]
                        var host = userOrHost[1]
                        if(!this.hosts[host]){
                            this.log('ssh: Could not resolve hostname '+host+': Name or service not known')
                            return Promise.resolve()
                        }
                        this.sshPass(user, host)
                    }
                    return Promise.resolve()
                },
                sshPass: function (user, host){
                    this.passAttempts++
                    this.set('input_prefix', user+'@'+host + '\'s password: ')
                    this.passwdInput = true
                    this.passBuffer = ''
                    this.nextCmd = function (){
                        var myThis = this
                        this.log(user+'@'+host + '\'s password:')
                        if(!this.hosts[host].users[user]){
                            if(myThis.passAttempts > 2){
                                myThis.log('Connection closed by ' + host)
                                myThis.reset()
                                return Promise.resolve()
                            }
                            myThis.log('Permission denied, please try again.')
                            myThis.sshPass(user, host)
                            return Promise.resolve()
                        }
                        return this.sha256(this.passBuffer).then(function (hash){
                            if(hash === myThis.hosts[host].users[user].hash){
                                //change emulator
                                myThis.past_users.push(myThis.current_user)
                                myThis.past_hosts.push(myThis.current_host)
                                myThis.current_user = myThis.hosts[host].users[user]
                                myThis.current_host = myThis.hosts[host]
                                myThis.terminalInit()
                                myThis.reset()
                            }else{
                                if(myThis.passAttempts > 2){
                                    myThis.log('Connection closed by ' + host)
                                    myThis.reset()
                                }else{
                                    myThis.log('Permission denied, please try again.')
                                    myThis.sshPass(user, host)
                                }
                            }
                        })
                    }
                },
                sudo: function (cmd){
                    this.log(this.input_prefix + cmd)
                    this.originalCmd = cmd.substr(cmd.indexOf(" ") + 1)
                    this.passwdPrompt(cmd)
                    return Promise.resolve()
                },
                passwdPrompt: function (){
                    this.passAttempts++
                    this.set('input_prefix', '[sudo] password for '+this.current_user.name+': ')
                    this.passwdInput = true
                    this.passBuffer = ''
                    this.nextCmd = function (){
                        var myThis = this
                        this.log('[sudo] password for '+this.current_user.name+':')
                        return this.sha256(this.passBuffer).then(function (hash){
                            if(hash === myThis.current_user.hash){
                                var cmd = myThis.originalCmd
                                myThis.reset()
                                myThis.run(cmd)
                            }else{
                                if(myThis.passAttempts > 2){
                                    myThis.log('3 incorrect password attempts')
                                    myThis.reset()
                                }else{
                                    myThis.log('Sorry, try again.')
                                    myThis.passwdPrompt()
                                }
                            }
                        })
                    }
                },
                reset: function (){
                    this.nextCmd = null
                    this.originalCmd = null
                    this.passwdInput = false
                    this.passBuffer = ''
                    this.passAttempts = 0
                    this.blockInput = false
                    clearTimeout(this.timeout)
                    this.resetPrefix()
                },
                hostname: function (cmd){
                    this.log(this.input_prefix + cmd)
                    this.log(this.current_host.hostname)
                    return Promise.resolve()
                },
                id: function (cmd){
                    this.log(this.input_prefix + cmd)
                    var u = this.current_user
                    var groupStr = ''
                    u.groups.forEach(function (group, index, arr){
                        groupStr += group.id+'('+group.name+')' + (arr.length - 1 === index ? '' : ',')
                    })
                    this.log('uid='+u.uid+'('+u.name+') gid='+u.gid+'('+u.name+') groups='+groupStr)
                    return Promise.resolve()
                },
                exit: function (cmd){
                    if(!this.past_users.length) return this.clear()
                    this.log(this.input_prefix + cmd)
                    this.log('logout')
                    return this.goBackOneTerminal()
                },
                logout: function (cmd){
                    if(!this.past_users.length) return this.clear()
                    this.log(this.input_prefix + cmd)
                    return this.goBackOneTerminal()
                },
                goBackOneTerminal: function (){
                    this.log('Connection to '+this.current_host.hostname+' closed.')
                    this.current_user = this.past_users.pop()
                    this.current_host = this.past_hosts.pop()
                    this.terminalInit()
                    this.reset()
                    return Promise.resolve()
                },
                hashcrack: function (cmd){
                    this.log(this.input_prefix + cmd)
                    var args = cmd.split(' ')
                    args.shift()
                    var wordlist = ''
                    var hashType = ''
                    var hash = ''
                    var SUPPORTED_HASH_TYPES = ['md5', 'sha1', 'sha256', 'crc32']
                    var wFlagIndex = args.findIndex(function (arg){
                        return arg === '-w'
                    })
                    if(wFlagIndex !== -1){
                        wordlist = args[wFlagIndex + 1]
                        args.splice(wFlagIndex, 2)
                    }
                    var tFlagIndex = args.findIndex(function (arg){
                        return arg === '-t'
                    })
                    if(tFlagIndex !== -1){
                        hashType = args[tFlagIndex + 1]
                        args.splice(tFlagIndex, 2)
                    }
                    var hFlagIndex = args.findIndex(function (arg){
                        return arg === '-h'
                    })
                    if(hFlagIndex !== -1){
                        hash = args[hFlagIndex + 1]
                        args.splice(hFlagIndex, 2)
                    }
                    if(!hash){
                        this.log('hashcrack: no hash given (-h hash)')
                        return Promise.resolve()
                    }
                    if(!hashType){
                        this.log('hashcrack: no hash type given (-t hashtype)')
                        return Promise.resolve()
                    }
                    //check if unsupported hash type
                    if(SUPPORTED_HASH_TYPES.indexOf(hashType) === -1){
                        this.log('hashcrack: unsupported hash type given')
                        this.log('supported hash types: ' + SUPPORTED_HASH_TYPES.join(' '))
                        return Promise.resolve()
                    }
                    var myThis = this
                    if(!wordlist){
                        this.log('no wordlist given, this may take a while (-w wordlist_path)')
                        this.log('hashcrack starting on hash: ' + hash)
                        this.log('press ctl-c to cancel operation')
                        return new Promise(function (resolve, reject){
                            myThis.input_prefix = ''
                            myThis.input.value = ''
                            myThis.blockInput = true
                            myThis.timeout = setTimeout(function (){
                                myThis.log('hashcat: no results found')
                                myThis.reset()
                                resolve()
                            }, (Math.floor(Math.random() * 30) + 10) * 1000)
                        })
                    }
                    if(this.emulator.getPath(wordlist) !== this.WORD_LIST){
                        this.log('hashcrack: invalid wordlist given (-w wordlist_path)')
                        return Promise.resolve()
                    }
                    var notlookupable = {
                        'MTg1NzZkNGNmODFiYTdjYTU3Nzk3YWRjYTc3M2ZlMmVjZDc1MmNjMA==': {
                            n: 'YmxhY2toYXdr',
                            t: 20,
                            h: 'sha1'
                        },
                        'N2MyMjJmYjI5MjdkODI4YWYyMmY1OTIxMzRlODkzMjQ4MDYzN2MwZA==': {
                            n: 'MTIzNDU2Nzg=',
                            t: 15,
                            h: 'sha1'
                        },
                        'MTQ2NmFiYzhmYTRkODcyZTIzNDhlZDU5Nzc3MDkwNWM4ZDI1ZTIwNg==': {
                            n: 'bG92ZXlh',
                            t: 10,
                            h: 'sha1'
                        },
                        'MjcxOTYxNzI4Njc3MzA0NzMzNDk3MWFmNjY4NGMyZTBhMGRhMDUxMQ==': {
                            n: 'bWlzY2hpZWY=',
                            t: 10,
                            h: 'sha1'
                        },
                        'NTdiMmFkOTkwNDRkMzM3MTk3YzBjMzlmZDM4MjM1NjhmZjgxZTQ4YQ==': {
                            n: 'cEBzc3cwcmQ=',
                            t: 20,
                            h: 'sha1'
                        }
                    }
                    this.log('hashcrack starting on hash: ' + hash)
                    this.log('press ctl-c to cancel operation')
                    return new Promise(function (resolve, reject){
                        myThis.input_prefix = ''
                        myThis.input.value = ''
                        myThis.blockInput = true
                        var tmp = {}
                        hash = hash.toLowerCase()
                        if(notlookupable[btoa(hash)]){
                            tmp = notlookupable[btoa(hash)]
                            if(tmp.n && hashType === tmp.h){
                                tmp.p = atob(tmp.n)
                            }
                        }else{
                            tmp.t = Math.floor(Math.random() * 20) + 5
                        }
                        myThis.timeout = setTimeout(function (){
                            if(tmp.p){
                                myThis.log('hashcat: password cracked: ' + tmp.p)
                            }else{
                                myThis.log('hashcat: no results found')
                            }
                            myThis.reset()
                            resolve()
                        }, tmp.t * 1000)
                    })
                },
                locate: function (cmd){
                    this.log(this.input_prefix + cmd)
                    var args = cmd.split(' ')
                    args.shift()
                    if(!args.length){
                        this.log('locate: no pattern to search for specified')
                        return Promise.resolve()
                    }
                    var files = Object.keys(this.current_host.fs)
                    var args_length = args.length
                    for(var i = files.length - 1; i >= 0; --i){
                        for(var j = 0; j < args_length; j++){
                            if(files[i].indexOf(args[j]) > -1)
                                this.log(files[i])
                        }
                    }
                    return Promise.resolve()
                },
                sha256: function (str){
                    return new Promise(function (resolve, reject){
                        var hashed = sha256(str)
                        resolve(hashed)
                    })
                }
            })
        </script>
    </dom-module>
@endsection