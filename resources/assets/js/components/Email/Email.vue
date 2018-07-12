<template>
    <div class="container">
        <div class="alert alert-success alert-dismissible fade show hidden" id="submitSuccess" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Success!</strong> Your email was sent successfully.
        </div>

        <div class="alert alert-danger alert-dismissible fade show hidden" id="submitFailure" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Uh oh!</strong> There was an error in sending your email.
        </div>

        <div class="main">
            <div class="row">
                <div role="banner" class="heading flex-header col-sm-12">
                    <h2>Messages</h2>
                    <div id="mySidenav" class="sidenav">
                        <a href="javascript:void(0)" class="closebtn" @click="closeNav">&times;</a>
                        <button class="btn btn-success compose" @click="composeModal">Compose</button>
                        <div class="toggle" @click="toggleInbox"><span>Inbox</span></div>
                        <div class="toggle" @click="toggleSent"><span>Sent</span></div>
                    </div>
                    <a href="#"><i class="material-icons mobile-menu" @click="openNav">menu</i></a>
                </div>
            </div>

            <div class="row content">
                <div class="sidebar col-md-2">
                    <button class="btn btn-success compose" @click="composeModal">Compose</button>
                    <ul>
                        <li class="inboxToggle" @click="toggleInbox"> <div class="keyline keyline-inbox">Inbox</div> </li>
                        <li class="sentToggle" @click="toggleSent"> <div class="keyline keyline-sent">Sent</div> </li>
                    </ul>
                </div>
                <div class="col-sm-12 col-lg-10 emailList">

                    <inbox
                            v-if="showInbox"
                            @submitSuccess="submitSuccess"
                            @submitFailure="submitFailure"
                            @sentReply="updateSent"
                            :characterEmails="characterEmails"
                            :characters="characters"
                            :studentEmails="studentEmails">
                    </inbox>

                    <sent-mail
                            v-if="showSent"
                            :studentEmails="studentEmails">
                    </sent-mail>
                </div>
            </div>
        </div>
        <!-- Compose Modal -->
        <div class="modal fade" id="composeModal" tabindex="-1" role="dialog" aria-labelledby="readModal" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header heading">
                        <h5 class="modal-title">New Email</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row form-group">
                            <label for="toList" class="col-sm-2">To:</label>
                            <div class="col-sm-10">
                                <v-select :options="characters" label="name" v-model="draftEmail.to" id="toList">

                                </v-select>
                            </div>
                        </div>
                        <div class="row form-group">
                            <label for="toSubject" class="col-sm-2">Subject:</label>
                            <input type="text" id="toSubject" name="toSubject" v-model="draftEmail.subject">
                        </div>
                        <div class="row form-group">
                            <textarea class="col-sm-12" type="text" id="toBody" v-model="draftEmail.body"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="file" name="emailAttachment" ref="file" id="emailAttachment" @change="setAttachment()">
                        <button class="btn btn-success" @click="sendEmail(draftEmail.character_email_id)">Send</button>
                    </div>
                </div>
            </div>
        </div>


    </div>
</template>

<script>
    import { mapGetter, mapActions } from 'vuex'
    import  vSelect  from 'vue-select'
    import inbox from './inbox.vue'
    import sentMail from './sentMail.vue'

    export default {

        components: {
            'v-select': vSelect,
            'inbox': inbox,
            'sent-mail': sentMail
        },
        data: function () {
            return {
                showInbox: 1,
                showSent: 0,
                draftEmail: {
                    attachment: null,
                    character_email_id: 0,
                    to: 0,
                    reply: 0,
                    subject: "",
                    body: ""
                }
            }
        },
        props: {
            characterEmails: Array,
            characters: Array,
            studentEmails: Array
        },
        mounted() {
            $('#submitFailure').show();
            $('#submitSuccess').hide();
            let appScope = this;
            $('#composeModal').on('hidden.bs.modal', function (e) {
                appScope.resetDraftEmail();
            })

        },
        methods: {
            toggleInbox: function () {
                this.showInbox = 1;
                this.showSent = 0;
                $('.keyline-inbox').css('border-color', '#636b6f');
                $('.keyline-sent').css('border-color', 'white');
            },
            toggleSent: function () {
                this.showInbox = 0;
                this.showSent = 1;
                $('.keyline-inbox').css('border-color', 'white');
                $('.keyline-sent').css('border-color', '#636b6f');

            },
            openNav: function () {
                document.getElementById("mySidenav").style.width = "200px";
            },
            closeNav: function () {
                document.getElementById("mySidenav").style.width = "0";
            },
            resetDraftEmail: function () {
                // Reset the draft email
                $('.replyForm').css('display', 'none');
                $('.replyEmail').css('display', 'initial');
                $('.sendReplyEmail').css('display', 'none');
                $('#emailAttachment').val(null);
                this.$refs.file.value = '';
                this.draftEmail = {
                    attachment: null,
                    to: "Please Select Character from Dropdown",
                    reply: 0,
                    subject: "",
                    body: ""
                };
            },
            composeModal: function () {
                this.resetDraftEmail();

                $('#composeModal').modal('show');
            },
            sendEmail: function (emailId) {
                let formData = new FormData();
                formData.append('to', this.draftEmail.to.character_id);
                formData.append('reply', this.draftEmail.reply);
                formData.append('subject', this.draftEmail.subject);
                formData.append('body', this.draftEmail.body)
                if (this.draftEmail.attachment != null) {
                    formData.append('attachment', this.draftEmail.attachment);
                }

                //update student emails to show sent message
                this.draftEmail.name = this.draftEmail.to.name;
                this.draftEmail.day = this.$store.state.user.current_day;
                this.studentEmails.unshift(this.draftEmail);

                axios.post('/email', formData).then(response => {
                    this.submitSuccess();
                    this.resetDraftEmail();
                }).catch(err => this.submitFailure());
                $('#composeModal').modal('hide');
            },
            updateSent: function (draft, emailId) {

                //update studentEmails to reflt the reply
                let appScope = this;
                this.characterEmails.find(function (email) {
                    if (email.character_email_id == emailId) {
                        email.reply = draft;

                        draft.day = appScope.$store.state.user.current_day;
                        draft.name = draft.to.name;
                        draft.reply = email;

                        appScope.studentEmails.unshift(draft);

                        return email
                    }
                });
            },
            setAttachment: function () {
                this.draftEmail.attachment = this.$refs.file.files[0];
            },
            submitSuccess: function () {
                $('#submitFailure').hide();
                $('#submitSuccess').show();
                $('#submitSuccess').removeClass("hidden");
                $('#submitSuccess').alert();
                setTimeout(function () {
                    $('#submitSuccess').addClass("hidden");
                }, 2500);
            },
            submitFailure: function () {
                $('#submitSuccess').hide();
                $('#submitFailure').show();
                $('#submitFailure').alert();
                setTimeout(function () {
                    $('#submitFailure').hide();
                }, 2500);
            }
        }
    }
</script>

<style scoped lang="scss">
    @import "../../../sass/_variables.scss";
    ul {
        padding-left: 0;
        list-style: none;
        cursor: pointer;
        margin-top: 20px;
    }
    li {
        height: 40px;
        margin-left: -10px;
        margin-right: -10px;
        padding: 10px 12px;
    }
    .main{
        height: 60rem;
        overflow-y: scroll;
    }
    h2.heading {
        margin-top: 6px;
    }
    .row{
        margin: 0px;

    }
    .hidden {
        visibility: hidden;
    }
    .emailList{
        margin-top: 20px;
    }
    .truncate{
        max-width: 115px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .compose{
        margin: 20px;
    }
    #toBody{
        height: 30rem;
        resize: none;
    }
    #toSubject {
        width: 80%;
        border-radius: 4px;
        border-width: 1px;
        margin-left: 14px;
    }
    .email-body{
        margin: 30px 0 40px;
    }
    #composeModal > .modal-dialog > .modal-content{
        height: 44rem;
        width: 35rem;
    }
    #readModal > .modal-dialog > .modal-content{
        width: 35rem;
    }
    .modal-body{
        height: 80%
    }
    .modal-title{
        margin: -9px 0;
        justify-content: flex-start;
    }
    .keyline{
        border-left: solid 4px #636b6f;
        padding-left: 20px;
    }
    .keyline-sent{
        border-color: white;
    }
    .keyline-inbox{
        border-color: #636b6f;
    }
    .sidebar{
        display: none;
    }
    .toggle {
        margin: 20px;
        cursor: pointer;
    }
    .flex-header{
        display: flex;
        justify-content: space-between;
    }
    .mobile-menu{
        font-size: 26px;
        color: white;
    }
    .close {
        color: #fff;
        line-height: 0;
        margin-top: -15px;
    }
    /* The side navigation menu */
    .sidenav {
        height: 100%; /* 100% Full-height */
        width: 0; /* 0 width - change this with JavaScript */
        height: 60rem;
        position: absolute; /* Stay in place */
        z-index: 1; /* Stay on top */
        right: 0;
        background-color: #4a4a4a;
        overflow-x: hidden; /* Disable horizontal scroll */
        padding-top: 60px; /* Place content 60px from the top */
        transition: 0.5s; /* 0.5 second transition effect to slide in the sidenav */
    }

    /* The navigation menu links */
    .sidenav a {
        padding: 8px 8px 8px 32px;
        text-decoration: none;
        font-size: 25px;
        color: #818181;
        display: block;
        transition: 0.3s;
    }

    /* When you mouse over the navigation links, change their color */
    .sidenav a:hover {
        color: #f1f1f1;
    }

    /* Position and style the close button (top right corner) */
    .sidenav .closebtn {
        position: absolute;
        top: 0;
        right: 25px;
        font-size: 36px;
        margin-left: 50px;
    }

    /* Style page content - use this if you want to push the page content to the right when you open the side navigation */
    .content {
        transition: margin-left .5s;
        padding: 20px;
    }

    /* On smaller screens, where height is less than 450px, change the style of the sidenav (less padding and a smaller font size) */
    @media screen and (max-height: 450px) {
        .sidenav {padding-top: 15px;}
        .sidenav a {font-size: 18px;}
    }
    @media(min-width: 1024px){
        .truncate {
            max-width: 23rem;
        }
    }
    @media(min-width: 1224px){
        .sidebar{
            display: initial;
            background-color: white;
            height: 56rem;
            border-right: 1px solid rgb(200, 200, 200);
            padding-left: 0px;
        }
        .compose{
            margin: 0;
            margin-top: 20px;
            //height: auto;
            //width: auto;
        }
        .mobile-menu{
            display: none;
        }
        #composeModal > .modal-dialog > .modal-content{
            width: 50rem;
        }
        #readModal > .modal-dialog > .modal-content{
            width: 50rem;
        }
        .truncate{
            max-width: 20rem;
        }
    }
    @media(min-width: 1400px){
        .compose{
            height: 40px;
            width: 130px;
        }
    }
</style>
