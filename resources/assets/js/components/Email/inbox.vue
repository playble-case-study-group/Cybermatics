<template>
    <div class="inbox">
        <div class="row">
            <div class="col-sm-12 col-lg-10">
                <table id="inbox">
                    <tr>
                        <th>From</th>
                        <th></th>
                        <th>Subject</th>
                        <th>Day</th>
                        <th></th>
                    </tr>
                    <tr v-for="email in characterEmails"
                        @click="readEmail(email)"
                        :class=" email.read ? 'read' : 'unread'"
                        :key="email.character_email_id">
                        <td> <img :src="email.img_small" alt=""> </td>
                        <td> {{ email.name }} </td>
                        <td class="truncate"> {{ email.subject }} - <span class="greyText">{{ email.body }}</span></td>
                        <td> {{ email.day }} </td>
                        <td> </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Read/Reply Modal -->
        <div class="modal fade" id="readModal" tabindex="-1" role="dialog" aria-labelledby="readModal" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header heading">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h5>
                            From: {{ readModalData.from }}
                        </h5>
                        <p>Subject: {{ readModalData.subject }}</p>
                        <p class="email-body">{{ readModalData.body }}</p>
                        <hr v-if="readModalData.reply">
                        <h5 v-if="this.readModalData.reply">
                            From: {{  this.$store.state.user.name }}
                        </h5>
                        <p v-if="this.readModalData.reply">Subject: {{ readModalData.subject }}</p>
                        <div class="email-body" v-if="this.readModalData.reply">{{ this.readModalData.reply.body }}</div>

                        <div class="replyForm" style="display: none;">
                            <div class="row form-group reply-contact col-sm-12">
                                <i class="material-icons reply">reply</i>
                                <span class="reply-contact-name">{{ readModalData.from }}</span>
                            </div>
                            <textarea class="col-sm-12" type="text" id="repBody" v-model="draftEmail.body"></textarea>
                        </div>

                    </div>
                    <div class="modal-footer" id="ReplyEmailId">
                        <button class="btn btn-success replyEmail" @click="replyEmail">Reply</button>
                        <input type="file" name="emailAttachment" ref="file" id="emailAttachment" @change="setAttachment()">
                        <button class="btn btn-success sendReplyEmail" style="display: none;" @click="sendReplyEmail(readModalData.character_email_id)">Send</button>
                    </div>
                </div>
            </div>
        </div>


    </div>
</template>

<script>
    import { mapGetter, mapActions } from 'vuex'

    export default {
        data: function () {
            return {
                readModalData: {
                    character_email_id: 0,
                    id: 0,
                    from: "",
                    subject: "",
                    body: "",
                    reply: ""
                },
                draftEmail: {
                    attachment: null,
                    character_email_id: 0,
                    to: 0,
                    reply: 0,
                    subject: "",
                    body: ""
                },
                currentEmail: {}
            }
        },
        props: {
            characterEmails: Array,
            characters: Array,
            studentEmails: Array
        },
        mounted() {
            $('#replyForm').hide();
            $('#emailAttachment').css('display', 'none');
            let appScope = this;
            $('#readModal').on('hidden.bs.modal', function (e) {

                appScope.currentEmail.read = true;
                let msg = appScope.characterEmails.find( email => email.character_email_id == appScope.currentEmail.character_email_id);
                msg.read = true;
                appScope.resetDraftEmail();
            })

        },
        methods: {
            ...mapActions([
                'SET_NEW_EMAILS'
            ]),
            readEmail: function (email) {
                this.currentEmail = email;
                this.resetDraftEmail();
                this.readModalData.id = email.character_email_id;
                this.readModalData.from = email.name;
                this.readModalData.subject = email.subject;
                this.readModalData.body = email.body;
                this.readModalData.reply = email.reply;
                this.readModalData.character_email_id = email.character_email_id;

                if (email.reply != null) {
                    //show player name and the email they wrote (using first()?) instead of reply button
                    $('#ReplyEmailId').hide();
                }
                else {
                    //show reply button
                    $('#ReplyEmailId').show();
                }
                 //indicate that an email has been read
                if (!email.read) {
                    let data = {
                        email_id: email.character_email_id
                    };
                    axios.post("/readEmail", data)
                        .then(res => {

                            const promise = new Promise((resolve, reject) => {
                                if (this.SET_NEW_EMAILS()) {
                                    resolve();
                                } else {
                                    reject(Error('it broke'));
                                }
                            });

                            promise.then( result => {
                                console.log('result: ', email);
                                email.read = true;
                            }, err => {
                                console.log(err);
                            });
                        })
                        .catch(err => console.log(err));
                }


                $('#readModal').modal()
            },
            sendReplyEmail: function (emailId) {
                this.$emit('sentReply', this.draftEmail, emailId);

                let formData = new FormData();
                formData.append('to', this.draftEmail.to.character_id);
                formData.append('reply', this.draftEmail.reply);
                formData.append('subject', this.draftEmail.subject);
                formData.append('body', this.draftEmail.body)
                formData.append('character_email_id', this.draftEmail.character_email_id)
                if(this.draftEmail.attachment != null) {
                    formData.append('attachment', this.draftEmail.attachment);
                }

                axios.post('/email', formData).then(response => {
                    this.$emit('submitSuccess');
                    this.resetDraftEmail();
                    this.$forceUpdate();
                }).catch(err => this.$emit('submitFailure'));
                $('#readModal').modal('hide');
                $('#emailAttachment').css('display', 'none');
            },
            replyEmail: function () {

//                this.draftEmail.reply = this.readModalData.id;
//
//                $('#replyForm').show();
//                $('.replyEmail').html('Send');

//                this.resetDraftEmail();
                $('.replyForm').css('display', 'inherit');
                $('.replyEmail').css('display', 'none');
                $('.sendReplyEmail').css('display', 'initial');
                $('#emailAttachment').css('display', 'initial');
                this.draftEmail.to = this.findCharData();
                this.draftEmail.subject = this.readModalData.subject;
                this.draftEmail.character_email_id = this.readModalData.character_email_id;

            },
            resetDraftEmail: function () {
                // Reset the draft email
                $('.replyForm').css('display', 'none');
                $('.replyEmail').css('display', 'initial');
                $('.sendReplyEmail').css('display', 'none');
                $('#emailAttachment').css('display', 'none');
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
            findCharData: function () {
                let appScope = this;
                var found = this.characters.find(function (element) {
                    console.log(element);
                    return element.name == appScope.readModalData.from;
                });
                return found;
            },
            setAttachment: function () {
                this.draftEmail.attachment = this.$refs.file.files[0];
            }
        }

    }
</script>

<style scoped lang="scss">
    @import "../../../sass/_variables.scss";
    textarea{
        resize: none;
        height: 20rem;
    }
    td, th {
        padding: 10px;
    }
    tr {
        cursor: pointer;
    }
    .row{
        margin: 0px;
    }
    .truncate{
        max-width: 115px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .email-body{
        margin: 30px 0 40px;
    }
    .reply{
        font-size: 25px;
    }
    .reply-contact{
        border: solid 1px;
        border-bottom: 0;
        margin-bottom: 0;
    }
    .reply-contact-name{
        padding-top: 9px;
        padding-left: 30px;
        position: absolute;
        height: 20px;
    }
    .greyText {
        color: grey;
        font-weight: 300;
    }
    .read {
        background-color: #f5f8fa;
    }
    .unread {
        font-weight: bold;
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
    .flex-header{
        display: flex;
        justify-content: space-between;
    }
    .close {
        color: #fff;
        line-height: 0;
        margin-top: -15px;
    }

    @media(min-width: 1024px){
        .truncate {
            max-width: 23rem;
        }
    }
    @media(min-width: 1224px){
        #readModal > .modal-dialog > .modal-content{
            width: 50rem;
        }
        .truncate{
            max-width: 35rem;
        }
    }
</style>
