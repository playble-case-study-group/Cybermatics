<template>
    <div id="question">
        <div class="counterDisplay col-sm-12" v-if="this.count > 0"><!---->
            <br><br>
            <p v-if="this.warning">
                You will have {{ this.activeQuestion.recording_duration }} seconds to respond.
                Recording will start in : <span class="counter">{{ this.count }}</span>
            </p>
            <p v-else>
                Time Remaining: <span class="counter">{{ this.count }}</span><br><br>
                <button @click="endResponseEarly" class="btn btn-light">End Recording Early</button>
            </p>
        </div>
        <div class="unaskedQuestions questionList" v-if="showButtons">
            <p>Question Bank</p>
            <hr>
            <button type="button"
                    v-for="question in questions"
                    class= "active btn btn-success btn-lg button"
                    v-if="question.question && !disabledQuestions.includes(question.question_id)"
                    :key="question.id"
                    @click="submitQuestion(question)">
                <b>{{ question.question }}</b>
                <i class="material-icons recording" v-if="question.record_after">fiber_manual_record</i>
            </button>
        </div>
        <div class="askedQuestions questionList" v-if="showButtons">
            <p>History</p>
            <hr>
            <button type="button"
                    v-for="question in questions"
                    class= "visited btn btn-success btn-lg button"
                    v-if="question.question && disabledQuestions.includes(question.question_id)"
                    :key="question.id"
                    @click="submitQuestion(question)">
                <b>{{ question.question }}</b>
                <i class="material-icons recording" v-if="question.record_after">fiber_manual_record</i>
            </button>
        </div>
    </div>
</template>

<script>
    import { mapGetter, mapActions } from 'vuex'

    export default {


        data() {
            return{
                count: 0,
                showButtons: true,
                activeQuestion: {}
            }
        },
        watch: {
            countdown: function(){
                this.count = this.countdown;
                this.startCount();
                this.showButtons = false;

            },
        },
        props: {
            questions: Array,
            countdown: Number,
            warning: Boolean,
            disabledQuestions: Array
        },
        methods: {
            submitQuestion: function (question) {
                this.activeQuestion = question;
                this.$emit('question', question)
                this.warning = false;
                this.disabledQuestions.push(question.question_id);
                this.$forceUpdate();
                axios.post(
                    '/clickedQuestion',
                    {
                        id: question.question_id
                    }
                ).then(res => console.log(res)
                ).catch( error => console.log(error));
            },
            startCount: function() {

                let appScope = this;
                let timer = setInterval(function () {
                    if (appScope.count > 0) {
                        appScope.count -= 1;
                    }
                    else {
                        if(appScope.warning == true) {
                            appScope.$emit('finishedWarning');
                        } else {
                            appScope.$emit('finishedCountdown')
                            appScope.showButtons = true;
                        }
                        clearInterval(timer);
                    }
                }, 1000);
            },
            endResponseEarly: function() {
                this.count = 0;
                this.$emit('finishedCountdown');
            }
        },

    }
</script>


<style scoped>
    .button {
        margin: 1rem 0;
        height: auto;
        width: 100%;
        width: -moz-available;/* WebKit-based browsers will ignore this. */
        width: -webkit-fill-available;/* Mozilla-based browsers will ignore this. */
        width: fill-available;
    }
    .visited {
        opacity: 0.65;
    }
    .active {

    }
    .recording{
        color: #ff4d4d;
        float: right;
        font-size: 12px;
        margin-top: 3px;
    }
    .counterDisplay {
        font-size: 16px;
        top: -30px;
        text-align: center;
    }
    .counter {
        padding-left: 40px;
        color: #dc3545;

    }
    .questionList {
        width: 50%;
        padding: 15px 25px 0;
    }
    @media(min-width: 992px){
        .button {
            margin: 1rem 0;
            white-space: normal;
        }
    }
    @media(min-width: 1400px){
        .button {
            margin: 1rem 0rem;
        }
    }
</style>