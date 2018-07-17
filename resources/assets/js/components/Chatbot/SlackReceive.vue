<template>
    <li>
        <div class="message-data">
            <span v-if="message.from" class="message-data-name"><i class="fa fa-circle online"></i> {{ message.from }}</span>
            <span v-else-if="name == 'Group Chat'" class="message-data-name"><i class="fa fa-circle online"></i> {{ defaultName }} </span>
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
        props: ['name', 'message', 'isLast', 'defaultName'],
        methods: {
            lastChatMessage: function () {
                if(this.isLast) {
                    this.$emit('last_msg');
                }
            }
        }
    }
</script>
