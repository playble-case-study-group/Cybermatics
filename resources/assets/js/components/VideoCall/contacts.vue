<template>
    <div id="contacts" class="container">

        <div class="contact-list" v-if="clickedCharacter == 0">
            <div class="heading">
                <h3>Contacts</h3>
            </div>
            <div class="contact-inner col-sm-12"
                 v-for="person in characters"
                 :key="person.character_id"
                 @click="selectCharacter(person.character_id)">
                <div class="col-sm-2">
                    <img class="characterImage" :src="person.img_small">
                    <span class="characterActive" v-if="activeContacts.includes(person.character_id)">
                            <i class="material-icons activeIcon">fiber_manual_record</i>
                    </span>
                    <span class="characterInactive" v-else>
                            <i class="material-icons activeIcon">fiber_manual_record</i>
                    </span>
                    <!--<span class="badge badge-pill badge-danger notify" v-if="contactMessages.includes(person.character_id)">
                        1
                    </span>-->
                </div>
                <div class="characterInfo col-sm-6">
                    <span class="characterName">{{ person.name }}</span><br>
                    <span class="characterPosition">{{ person.role }}</span>
                </div>
                <span id="call" class="col-sm-3">

                    <i class="videocam material-icons">videocam</i>
                </span>
            </div>
        </div>

        <character-video
                v-else
                @alertSuccess="alertSaveSuccess"
                @alertFailure="alertSaveFailure"
                @showContacts="showContacts"
                :clickedCharacter="this.clickedCharacter"
                :characters="this.characters"
                :calls="this.calls"
                :questions="this.questions"
                :disabledQuestions="this.disabledQuestions">

        </character-video>
    </div>
</template>

<script>
    import { mapActions } from 'vuex'
    import notes from './notes.vue'
    import videos from './videos.vue'

    export default {
        mounted() {
            $('#submitSuccess').hide();
            $('#submitFailure').hide();
        },
        components: {
            'character-video': videos
        },
        props: {
            calls: Array,
            questions: Array,
            disabledQuestions: Array,
            characters: Array
        },
        data: function() {
            return {
                clickedCharacter: 0
            }
        },
        computed: {
            activeContacts: function() {
                return this.calls.filter((character) => {
                    if(!character.video_message){
                        return character.character_id;
                    }
                }).map((character) => {
                    return character.character_id;
                })
            },
            contactMessages: function() {
                return this.calls.filter((character) => {
                    if(character.video_message){
                        return character.character_id;
                    }
                }).map((character) => {
                    return character.character_id;
                })
            }
        },
        methods: {
            selectCharacter: function(character_id) {
                this.clickedCharacter = character_id;
            },
            alertSaveSuccess: function() {
                this.$emit('alertSuccess');
            },
            alertSaveFailure: function() {
                this.$emit('alertFailure');
            },
            showContacts: function() {
                this.clickedCharacter = 0;
            }
        }
    }
</script>

<style scoped>
    h3 {
        padding: 10px;
    }
    img {
        align-self: center;
        position: relative;
    }
    .notify {
        position: relative;
        margin-left: 30px;
        bottom: 17px
    }
    .videocam {
        color: grey;
    }
    .contact-list {
        background-color: white;
    }
    .characterActive{
        color: #57AB58;
        position: relative;

        margin-left: 30px;
        bottom: 14px;
    }
    .characterInactive{
        color: #dc3545;
        position: relative;

        margin-left: 30px;
        bottom: 14px;
    }
    .contact-inner{
        padding: 10px;
        display: flex;
        flex-flow: row;
        cursor: pointer;
        justify-content: space-between;
    }
    .contact-inner:nth-child(even) {
        border-top: solid 1px;
        border-bottom: solid 1px;
        border-color: #d9dcde;
    }

    #call {
        align-self: center;
        font-size: 24px;
    }
    @media(min-width: 1092px){
        .notify {
            margin-left: -16px;
            bottom: -17px
        }
        .characterActive{
            margin-left: -16px;
            bottom: -24px;
        }
    }



</style>