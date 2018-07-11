<template>
    <li>
        <div class="message-data">
            <span class="message-data-name"><i class="fa fa-circle online"></i> {{ name }}</span>
            <span class="message-data-time">{{ message.time }}</span>
        </div>
        <div class="message my-message">
            {{ message.message }}
        </div>
    </li>
</template>

<script>

    export default {

        mounted() {
//            console.log('Receive mounted.');
            this.lastChatMessage();
        },
        props: ['name', 'message', 'isLast'],
        methods: {
            lastChatMessage: function () {
                if(this.isLast) {
                    this.$emit('last_msg');
                }
            }
        }
    }
</script>
