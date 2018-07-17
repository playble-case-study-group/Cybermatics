<template>
    <div class="container clearfix">
        <div class="people-list" id="people-list">
            <h2 class="list-title">Online</h2>
            <!--<div class="search">-->
                <!--<input type="text" placeholder="search"/>-->
                <!--<i class="fa fa-search"></i>-->
            <!--</div>-->
            <ul class="list">
                <slack-channel v-for="(channel, key) in channels"
                               class="pointer"
                            :channel="channel"
                            v-on:click.native="changeChannel(key)"
                            :key="key">
                </slack-channel>
            </ul>
        </div>

        <div class="chat">
            <div class="chat-header clearfix">
                <img v-if="channels[current_client_index].character_id != 0" class="selected-character" :src="channels[current_client_index].img_small" alt="avatar"/>
                <img v-else class="selected-character" src="img/characters/group.png" alt="avatar"/>
                <div class="chat-about">
                    <h5 class="chat-with">{{ channels[current_client_index].name }}</h5>
                </div>
                <i class="fa fa-star"></i>
            </div> <!-- end chat-header -->

            <div id="chat-history">
                <ul>
                  <span v-for="(message, key) in client_messages[current_channel_id]">
                    <slack-response v-if="!message.type"
                                    :message="message"
                                    :isLast="key === (client_messages[current_channel_id].length - 1)? true: false"
                                    v-on:last_msg="scrollChat"
                                    :key="key">
                    </slack-response>
                    <slack-receive v-if="message.type"
                                   :name="channels[current_client_index].name"
                                   :message="message"
                                   :defaultName = "defaultName"
                                   :isLast="key === (client_messages[current_channel_id].length - 1)? true: false"
                                   v-on:last_msg="scrollChat"
                                   :key="key">
                    </slack-receive>
                  </span>
                </ul>

            </div> <!-- end chat-history -->

            <div class="chat-message clearfix">
                <textarea name="message-to-send"
                          id="message-to-send"
                          placeholder="Type your message"
                          rows="3" v-model="sendMessage"
                          @keyup.enter="submitMessage">
                </textarea>

                <i class="fa fa-file-o"></i> &nbsp;&nbsp;&nbsp;
                <i class="fa fa-file-image-o"></i>

                <button @click="submitMessage">Send</button>

            </div> <!-- end chat-message -->

        </div> <!-- end chat -->

    </div> <!-- end container -->

</template>

<script>
    // https://codepen.io/drehimself/pen/KdXwxR
    import SlackUser from './SlackUser.vue'
    import SlackResponse from './SlackResponse.vue'
    import SlackReceive from './SlackReceive.vue'
    import SlackChannel from './SlackChannel.vue'
    import { ApiAiClient } from 'api-ai-javascript'
    import { mapGetters, mapActions } from 'vuex'

    export default {
        created() {

            // Variables created here are constants (const) this initialized the channels for the day.
            this.channels.forEach(channel => {
                this.clients.push(new ApiAiClient({accessToken: channel.channel_key}))
            });


        },
        computed: {
            currentTime: function () {
                return Date.now().toString()
            },
            ...mapGetters({
                currentDay: 'CURRENT_DAY'
            })
        },
        components: {
            "slack-user": SlackUser,
            "slack-response": SlackResponse,
            "slack-receive": SlackReceive,
            "slack-channel": SlackChannel
        },
        props: ['msgs', 'channels'],
        mounted() {
            // Creates emtpy array for each channel
            this.channels.forEach ( channel => {
                this.client_messages[channel.channel_id] = [];
            });

            this.current_channel_id = this.channels[0].channel_id;

            // Fills channel arrays with messages from msg prop
            this.msgs.forEach ( msg => {
                this.client_messages[msg.channel_id].push(msg);
            });

        },
        data: function () {
            return {
                messages: [],
                clients: [],
                current_client_index: 0,
                current_channel_id: 1,
                sendMessage: "",
                client_messages: {},
                userName: '',
                defaultName: 'Kimberly Smitherton'
            }
        },
        methods: {
            dayContext: function () {
                console.log("day: ", this.currentDay );
                this.clients[this.current_client_index].eventRequest("day_1_context").then(response =>console.log('context: ', response));
            },
            submitMessage: function () {

//                this.dayContext();
                this.userName = this.$store.state.user.name;
                this.clients[this.current_client_index].eventRequest("day_" + this.currentDay + "_context").then(response1 => {
                    console.log('context: ', response1)

                    this.clients[this.current_client_index].textRequest(this.sendMessage).then( response => {

                        console.log(response);

                        // Disable text area to prevent double input
                        document.getElementById('message-to-send').disabled = "true";

                        let date = new Date;
                        date = date.toLocaleDateString("en-US");

                        let message = {
                            name: this.userName,
                            time: date,
                            message: this.sendMessage,
                            day: this.$store.getters.CURRENT_DAY,
                            type: 0,
                            channel_id: this.current_channel_id
                        };

                        this.client_messages[this.current_channel_id].push(message);


                        axios.post('/chat', message).then( res => {/* console.log(response) */

                            // empty the message each time it sends
                            this.sendMessage = "";

                            if(response.result.fulfillment.messages) {
                                //let payload = response.result.fulfillment.messages.filter(msg => msg.type === 4);
                                let payload = response.result.fulfillment.messages;
                                console.log(payload);

                                if (payload.length) {

                                    payload.forEach(msg => {

                                        if(msg.type == 4) {
                                            let custom = msg.payload.msgs;
                                            console.log(custom);

                                            custom.forEach(single => {
                                                console.log(single);
                                                let data = {
                                                    day: this.$store.getters.CURRENT_DAY,
                                                    channel_id: this.current_channel_id,
                                                    message: single.msg,
                                                    type: 1,
                                                    from: single.name
                                                };

                                                axios.post('/chat', data).then( response => { /* console.log(response) */ });
                                                this.client_messages[this.current_channel_id].push(data)
                                            })
                                        } else {
                                            console.log(msg);
                                            if(msg.speech) {
                                                let saved_name = '';
                                                if(this.channels[this.current_client_index].character_id != 0) {
                                                    saved_name = this.channels[this.current_client_index].name
                                                }
                                                else {
                                                    saved_name = this.defaultName;
                                                }
                                                let data = {
                                                    day: this.$store.getters.CURRENT_DAY,
                                                    channel_id: this.current_channel_id,
                                                    message: msg.speech,
                                                    type: 1,
                                                    from: saved_name
                                                };

                                                axios.post('/chat', data).then(response => { /* console.log(response) */
                                                });
                                                this.client_messages[this.current_channel_id].push(data)
                                            }

                                        }

                                    });

                                }

                            }
                        });
                        // Disable text area to prevent double input
                        document.getElementById('message-to-send').removeAttribute('disabled');
                        document.getElementById('message-to-send').focus();


                    });
                });
            },
            scrollChat: function () {
                let chatHist = document.getElementById('chat-history');
                chatHist.scrollTop = chatHist.scrollHeight;
            },
            changeChannel: function (channel_index) {
                this.scrollChat();
                console.log(channel_index);
                this.current_client_index = channel_index;
                this.current_channel_id = this.channels[this.current_client_index].channel_id;
            }
        }
    }
</script>

<style lang="scss">
    @import url(https://fonts.googleapis.com/css?family=Lato:400,700);

    $green: #596d6f;
    $blue: #8baaad;
    $orange: #E38968;
    $gray: #92959E;

    *, *:before, *:after {
        box-sizing: border-box;
    }

    li {
        list-style: none;
    }

    .pointer {
        cursor: pointer;
    }


    .list-title {
        padding-left: 20px;
    }

    .people-list {
        width: 260px;
        float: left;
        color: #4a4a4a;
        margin-top: 35px;

        .search {
            padding: 20px;
        }

        input {
            border-radius: 3px;
            border: none;
            padding: 14px;
            color: white;
            background: #6A6C75;
            width: 90%;
            font-size: 14px;
        }

        .fa-search {
            position: relative;
            left: -25px;
        }

        ul {
            padding: 20px;
            height: 770px;

            li {
                padding-bottom: 20px;
            }
        }

        img {
            float: left;
        }

        .about {
            float: left;
            margin-top: 8px;
        }

        .about {
            padding-left: 8px;
        }

        .status {
            color: $gray;
        }

    }

    .chat {
        width: 650px;
        float: left;
        background: #fff;
        border-top-right-radius: 5px;
        border-bottom-right-radius: 5px;

        color: #434651;

        .chat-header {
            padding: 20px;
            border-bottom: 1px solid #f1f1f1;

            img {
                float: left;
                border-radius: 50%;
                border: 2px solid $green;
            }

            .chat-about {
                float: left;
                padding-left: 10px;
                margin-top: 6px;
            }

            .chat-with {
                font-weight: bold;
                font-size: 16px;
            }

            .chat-num-messages {
                color: $gray;
            }

            .fa-star {
                float: right;
                color: #D8DADF;
                font-size: 20px;
                margin-top: 12px;
            }
        }

        #chat-history {
            padding: 0px 20px;
            border-bottom: 2px solid white;
            overflow-y: scroll;
            height: 675px;

            .message-data {
                margin-bottom: 10px;
            }

            .message-data-time {
                color: lighten($gray, 8%);
                padding-left: 6px;
            }

            .message {
                color: white;
                padding: 15px 20px;
                line-height: 26px;
                font-size: 14px;
                font-weight: 300;
                letter-spacing: 0.5px;
                border-radius: 1px;
                margin-bottom: 15px;
                width: 90%;
                position: relative;

                &:after {
                    bottom: 100%;
                    left: 7%;
                    border: solid transparent;
                    content: " ";
                    height: 0;
                    width: 0;
                    position: absolute;
                    pointer-events: none;
                    border-bottom-color: $green;
                    border-width: 10px;
                    margin-left: -10px;
                }
            }

            .my-message {
                background: $green;
            }

            .other-message {
                background: $blue;

                &:after {
                    border-bottom-color: $blue;
                    left: 93%;
                }
            }

        }

        .chat-message {
            padding: 20px;
            border-top: 1px solid #f1f1f1;

            textarea {
                width: 100%;
                border: 1px solid #BFBFBF;
                padding: 10px 20px;
                font: 14px/22px "Nunito", Arial, sans-serif;
                margin-bottom: 10px;
                border-radius: 6px;
                resize: none;

            }

            .fa-file-o, .fa-file-image-o {
                font-size: 16px;
                color: gray;
                cursor: pointer;

            }

            button {
                float: right;
                color: $blue;
                font-size: 16px;
                text-transform: uppercase;
                border: none;
                cursor: pointer;
                font-weight: bold;
                background: #F2F5F8;

                &:hover {
                    color: darken($blue, 7%);
                }
            }
        }
    }

    .online, .offline, .me {
        margin-right: 3px;
        font-size: 10px;
    }

    .online {
        color: $green;
    }

    .offline {
        color: $orange;
    }

    .me {
        color: $blue;
    }

    .align-left {
        text-align: left;
    }

    .align-right {
        text-align: right;
    }

    .float-right {
        float: right;
    }

    .clearfix:after {
        visibility: hidden;
        display: block;
        font-size: 0;
        content: " ";
        clear: both;
        height: 0;
    }

</style>
