<!--suppress ALL -->
<template>
    <div id="notes">
        <div class="heading">
            <h3>My Notes </h3>
            <span class="saved">saved <i class="material-icons">check_circle_outline</i></span>
        </div>
        <textarea v-model="note" @keyup="setTime" placeholder="Keep personal notes here."></textarea>
    </div>
</template>

<script>
    import { mapActions } from 'vuex'

    export default {

        data: function() {
            return {
                note: this.notes.note,
                timeout: null,
            }
        },
        props: {
            notes: Object
        },
        methods: {
            postUpdatedNote: function () {
                let data = {
                    note: this.note,
                    user: this.$store.state.user.user_id,
                }

                axios.post("/videocall", data)
                    .then(r => console.log(r))
                    .catch(e => console.log(e));
            },
            updateNote: function () {
                let data = {
                    note: this.note,
                    user: this.$store.state.user.user_id,
                }

                axios.post("/videocall", data)
                    .then(r => this.savedNotification())
                    .catch(e => console.log(e));

            },
            setTime: function () {
                //use this to adjust how long it takes to make an update in the database. The program will wait for a pause in typing.
                clearTimeout(this.timeout);
                this.timeout = setTimeout(this.updateNote, 1500);
            },
            savedNotification: function () {
                console.log('saved');
                clearTimeout(this.timeout);
                $('.saved').show()
                this.timeout = setTimeout(function() {$('.saved').hide()}, 3000)
            }
        }
    }

</script>

<style scoped lang="scss">
    @import "../../../sass/_variables.scss";

    textarea{
        width: 100%;
        height: 91%;
        border:none;
        padding: 20px;
        resize:none;

    }
    h3{
        padding: 10px;
        display: inline-block;
    }

    .saved {
        font-weight: light;
        display: none;
        position: relative;
        left: 30px;
    }

</style>