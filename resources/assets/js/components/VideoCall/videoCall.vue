<template>
    <div id="videocall" class="container">

        <div class="alert alert-success alert-dismissible fade show hidden" id="submitSuccess" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Success!</strong> Your video was sent successfully.
        </div>

        <div class="alert alert-danger alert-dismissible fade show hidden" id="submitFailure" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Uh oh!</strong> There was an error in sending your video.
        </div>


        <div class="row">
            <notes id="notesParent" class="col-sm-11 col-lg-3"
                   :notes="this.notes">
            </notes>
            <contacts class="col-sm-11 col-lg-8"
                      @alertSuccess="alertSaveSuccess"
                      @alertFailure="alertSaveFailure"
                      :characters="this.contacts"
                      :calls="this.calls"
                      :questions="this.questions"
                      :disabledQuestions="this.disabled">

            </contacts>
        </div>
    </div>
</template>

<script>
    import { mapActions } from 'vuex'
    import notes from './notes.vue'
    import contacts from './contacts.vue'

    export default {
        mounted() {
            $('#submitFailure').show();
        },
        components: {
            'notes': notes,
            'contacts': contacts
        },
        props: {
            calls: Array,
            questions: Array,
            disabled: Array,
            notes: Object,
            contacts: Array
        },
        methods: {
            alertSaveSuccess: function() {
                $('#submitFailure').hide();
                $('#submitSuccess').show();
                $('#submitSuccess').removeClass("hidden");
                $('#submitSuccess').alert();
                setTimeout(function(){
                    $('#submitSuccess').addClass("hidden");
                }, 2500);

            },
            alertSaveFailure: function() {
                $('#submitSuccess').hide();
                $('#submitFailure').show();
                $('#submitFailure').alert();
                setTimeout( function()
                {
                    $('#submitFailure').hide();
                }, 2500);

            }
        }
    }
</script>

<style scoped>
    #videocall{
        margin: 60px 3rem;
    }
    #notesParent{
        height:40rem;
        top: 2rem;
        align-self: start;
    }
    .row{
        flex-flow: wrap-reverse;
        flex-direction: row-reverse;
        justify-content: space-around;
    }
    .alert {
        margin-top: -45px;
    }
    .hidden {
        visibility: hidden;
    }

    @media(min-width:992px){
        #notesParent{
            height:42rem;
            top: 0;
        }
    }
    @media(min-width: 1350px){
        #notesParent{
            height: 45rem;
        }
    }

</style>