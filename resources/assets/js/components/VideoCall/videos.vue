<template>
    <div id="video" class="col-sm-12">
        <div class="video-container">
            <!--video element to show standard videocalls, hidden when recording component is shown-->
            <video v-if="!videoMessageInterface" id="call_video" poster="/img/videocall/video-placeholder.jpg">
                <source :src="currentVideo.video_url" type="video/mp4">
            </video>

            <video v-if="!videoMessageInterface" id="personal_video" poster="/img/videocall/video-placeholder.jpg" autoplay>
                <source src="" type="video/mp4">
            </video>

            <!--video recording component, hidden until click on inactive character-->
            <video-message
                    v-if="videoMessageInterface"
                    @showContacts="revertToContactsPage"
                    @saveSuccess="saveSuccess"
                    @saveFailure="saveFailure"
                    :recording="recording"
                    :clickedCharacter="clickedCharacter">

            </video-message>
        </div>

        <!--toolbar buttons-->
        <div id="controlBar">
            <!-- blank icon to balance toolbar-->
            <a href="#" @click="revertToContactsPage">
                <i id="phonebook" class="material-icons">people</i>
            </a>

            <a href="#" v-if="recording" @click="startStopRecording">
                <i class="material-icons recording">stop</i>
            </a>
            <a href="#" v-else-if="videoMessageInterface || leaveResponse" @click="startStopRecording">
                <i class="material-icons recording">fiber_manual_record</i>
            </a>
            <a href="#" v-else @click="revertToContactsPage">
                <i id="call" class="material-icons">call_end</i>
            </a>
            <canvas id="visualizer"></canvas>
        </div>

        <!--show character questions-->
        <character-questions id="characterQuestions"
                             :countdown="this.timerDuration"
                             :warning="this.warning"
                             :questions="this.currentQuestions"
                             :disabledQuestions="this.disabledQuestions"
                             @question="askQuestion"
                             @finishedCountdown="finishedCountdown"
                             @finishedWarning="finishedWarning">

        </character-questions>

    </div>
</template>

<script>
    import { mapActions } from 'vuex'
    import question from './question.vue'
    import videoMessage from './record_message.vue'
    import VideoMessage from "./record_message";

    export default {
        props: {
            calls: Array,
            characters: Array,
            questions: Array,
            disabledQuestions: Array,
            clickedCharacter: Number
        },
        data: function () {
            return {
                videoEl: {},
                currentQuestion: {},
                currentQuestions: [],
                currentVideo: {},
                videoMessageInterface: false,
                recording: false,
                leaveResponse: false,
                studentResponded: false,
                warningTime: 5,
                timerDuration: 0,
                warning: false,
                endMessage: false
            }
        },
        components: {
            'character-questions': question,
            'video-message': videoMessage
        },

        mounted() {
            this.startSelfVideo();
            this.startAudio();
            this.playIntroVideo();
        },
        updated() {
            if(this.videoMessageInterface == false){
                this.videoEl = document.getElementById('call_video');
            }
        },
        watch: {
            currentVideo: function () {
                if(!this.videoMessageInterface) {
                    document.getElementById('call_video').src = this.currentVideo.video_url;
                    document.getElementById('call_video').load();
                }
            },
            currentQuestion: function () {
                //if statement needed to avoid a change when currentQuestion changes to null

                if(!this.videoMessageInterface && !this.currentVideo.video_message && this.currentQuestion) {
                    document.getElementById('call_video').currentTime = (parseInt(this.currentQuestion.start_time) + 0.51);
                    document.getElementById('call_video').play();
                    let appScope = this;
                    let paused = false;
                    document.getElementById('call_video').addEventListener("timeupdate", function () {
                        if (document.getElementById('call_video').currentTime >= appScope.currentQuestion.end_time && !paused) {
                            paused = true;
                            document.getElementById('call_video').pause();

                            if (appScope.currentQuestion.record_after) {
                                appScope.studentVideoResponse();

                            }
                        }
                    });
                } else if(this.currentVideo.video_message) {
                    let appScope = this;
                    document.getElementById('call_video').play();
                    document.getElementById('call_video').onended = function(e) {
                        appScope.revertToContactsPage();
                    };
                } else {
                    document.getElementById('call_video').play();

                }
            },
            videoMessageInterface: function(){
                if(this.videoMessageInterface == false){
                    this.startSelfVideo();
                }
            },
        },
        computed: {
            activeContacts: function() {
                return this.calls.filter((character) => {
                    if(character.day === this.$store.state.user.current_day){
                        return character.character_id;
                    }
                }).map((character) => {
                    return character.character_id;
                })
            }
        },
        methods: {
            studentVideoResponse: function () {
                this.timerDuration = this.warningTime;
                this.warning = true;
            },
            finishedWarning: function() {
                this.warning = false;
                this.answerQuestion();
            },
            finishedCountdown: function() {
                this.endMessage = true;
            },
            callNextQuestion: function() {
                let appScope = this;
                this.currentQuestion = this.currentQuestions.find(function(question){
                    if(question.question_id == appScope.currentQuestion.next_question){
                        return question;
                    }
                });
                this.startSelfVideo();
            },
            playIntroVideo: function() {
                let video = document.getElementById('call_video');
                video.src = '/video/Ringing.mp4';
                video.play();

                let appScope = this;
                video.onended = function(e) {
                    appScope.loadCallVideo(appScope.clickedCharacter);
                };

            },
            loadCallVideo: function (person_id) {
                //this.clickedCharacter = person_id;
                //check if the contact clicked on is active
                let activeCall = this.calls.find((call) => {
                    if (call.character_id === person_id) {
                        return call;
                    }
                })

                if (activeCall) {
                    //if active, return the questions associated with them
                    this.videoMessageInterface = false;
                    this.currentQuestions = this.questions.filter((question) => {
                        if (question.call_id == activeCall.video_id) {
                            return question;
                        }
                    })

                    this.currentVideo = activeCall;
                    this.currentQuestion = this.currentQuestions.find((question) => {
                        if (question.first_question) {
                            return question;
                        }
                    })
                } else {
                    //if not active, leave a message
                    this.leaveMessage();
                }
            },
            leaveMessage: function () {
                this.videoMessageInterface = true;
                this.currentQuestions = [];
                this.currentQuestion = {};
                this.currentVideo = {};
            },
            revertToContactsPage: function () {
                if(!this.videoMessageInterface) {
                    this.videoEl.pause();
                }
                this.$emit('showContacts');
            },
            askQuestion: function (question) {
                this.studentResponded = false;
                //sends to a watch function
                this.currentQuestion = question;
            },
            startStopRecording: function () {
                //used when leaving a video message
                if(!this.leaveResponse) {
                    this.recording = !this.recording;
                }
            },

            //these functions handle all video objects and actions
            answerQuestion: function(){
                console.log('answer question fired');
                //set that we want both audio and video
                const constraints = {
                    video: true,
                    audio: true
                };

                //start recording
                navigator.mediaDevices.getUserMedia(constraints)
                    .then(this.handleMessage.bind(this))
                    .catch(this.handleFailure);
            },
            startSelfVideo: function () {
                //set that we want both audio and video
                const constraints = {
                    audio: true,
                    video: true
                };
                //start recording
                navigator.mediaDevices.getUserMedia(constraints)
                    .then(this.handleSuccess.bind(this))
                    .catch(this.handleFailure);
            },
            handleFailure: function (error) {
                //if they don't have browser support, try a lower compatibility function or fail
                console.error(error);
            },
            handleMessage: function (stream) {
                console.log('handle message called');
                this.timerDuration = this.currentQuestion.recording_duration;
                const video = document.getElementById('personal_video');

                const recordedChunks = [];
                let audioStream = stream.getTracks()[0];
                let videoStream = stream.getTracks()[1];

                //set local variable to set correct scope
                let appScope = this;

                //initialize and display recording stream
                const messageMediaRecorder = new MediaRecorder(stream);
                video.srcObject = stream;

                //start recording when video is loaded
                console.log('start recorded video')
                messageMediaRecorder.start(1000);

                //mutes personal recording to avoid reverb and audio looping
                video.muted = 'true';

                //save data as it becomes available. Stop recording if stop button has been triggered
                messageMediaRecorder.addEventListener('dataavailable', function (e) {
                    if (e.data.size > 0) {
                        recordedChunks.push(e.data);
                    }
                    if(appScope.endMessage && messageMediaRecorder.state == 'recording'){
                        console.log('stop recorded video')
                        messageMediaRecorder.stop();
                    }
                });

                //when recording stops, save the video object and stop displaying video stream
                messageMediaRecorder.addEventListener('stop', function () {
                    audioStream.stop();
                    videoStream.stop();

                    appScope.studentResponded = true;
                    appScope.endMessage = false;

                    const blob = new Blob(recordedChunks, {type: 'video/webm'});
                    var href = URL.createObjectURL(new Blob(recordedChunks), {type: 'video/webm'});

                    //call nect question when response is done.
                    appScope.callNextQuestion();
                    appScope.saveVideoMessage(blob, href);



                })

            },
            handleSuccess: function (stream) {
                const video = document.getElementById('personal_video');

                let audioStream = stream.getTracks()[0];
                let videoStream = stream.getTracks()[1];

                //initialize and display recording stream
                const mediaRecorder = new MediaRecorder(stream);
                video.srcObject = stream;

                //start recording when video is loaded
                console.log('started non-recorded video')
                mediaRecorder.start(3000);
                video.muted = 'true';


                //set local variable to set correct scope
                let appScope = this;
                //save data as it becomes available. Stop recording if stop button has been triggered
                mediaRecorder.addEventListener('dataavailable', function (e) {
                    if (appScope.warning == true && mediaRecorder.state != 'inactive') {
                        console.log('stopped non-recorded video')
                        mediaRecorder.stop();
                    }
                });

                //when recording stops, save the video object and stop displaying video stream
                mediaRecorder.addEventListener('stop', function () {
                    audioStream.stop();
                    videoStream.stop();


                })
            },
            saveVideoMessage: function(blob, href){
                //append all needed information into a form
                let appScope = this;
                let data = new FormData();
                data.append('user', this.$store.state.user.user_id);
                data.append('character', this.clickedCharacter);
                data.append('question', this.currentQuestion.question_id);

                //fetch the data saved into the blob
                axios
                    .get(
                        href,
                        { responseType: 'blob'}
                    ).then(function (response) {
                    //read in data from saved blob url
                    var reader = new FileReader();
                    reader.readAsDataURL(response.data, {type: 'application/octet-stream'});
                    reader.onloadend = function () {
                        var base64data = reader.result;
                        //append blob data to the form
                        data.append('blob', base64data);
                        //submit form with all needed data
                        axios
                            .post(
                                "/saveFile",
                                data
                            )
                            .then(r => { appScope.saveSuccess(); })
                            .catch(e => { appScope.saveFailure(); });
                    };
                })
            },
            saveSuccess: function(){
                this.$emit('alertSuccess');
            },
            saveFailure: function() {
                this.$emit('alertFailure');
            },

            //these functions handle the audio display
            startAudio: function() {
                //start collecting audio stream
                navigator.mediaDevices.getUserMedia({ audio: true }).then(stream => {
                    // store streaming data chunks in array
                    const chunks = [];

                    //set paramaters of the Audio Stream
                    var audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    var analyser = audioCtx.createAnalyser();
                    analyser.minDecibels = -90;
                    analyser.maxDecibels = -10;
                    analyser.smoothingTimeConstant = 0.85;
                    analyser.fftSize = 256;
                    //get canvas element that will display the animation
                    let canvasCtx = document.getElementById('visualizer');
                    canvasCtx = canvasCtx.getContext("2d");

                    // create media recorder instance to initialize recording
                    const recorder = new MediaRecorder(stream);

                    //connect the audio stream to the incoming audio
                    let source = audioCtx.createMediaStreamSource(stream);
                    source.connect(analyser);

                    //call animation function
                    this.visualize(analyser, canvasCtx);

                    // function to be called when data is received
                    recorder.ondataavailable = e => {
                        // add stream data to chunks
                        chunks.push(e.data);
                    };
                    // start recording with 1 second time between receiving 'ondataavailable' events
                    recorder.start(300);

                }).catch(console.error);
            },
            visualize: function (analyser, canvasCtx) {
                //set the dimensions of animation
                const WIDTH = 400;
                const HEIGHT = 150;

                //convert data to the correct format
                analyser.fftSize = 256;
                let bufferLengthAlt = analyser.frequencyBinCount;
                let dataArrayAlt = new Uint8Array(bufferLengthAlt);

                //clear the canvas to prepare for animation
                canvasCtx.clearRect(0, 0, WIDTH, HEIGHT);

                //set recursive function to continuously draw to canvas
                let drawAlt = function () {
                    let drawVisual = requestAnimationFrame(drawAlt);

                    analyser.getByteFrequencyData(dataArrayAlt);

                    //set canvas background color and add animation
                    canvasCtx.fillStyle = '#4a4a4a';

                    canvasCtx.fillRect(0, 0, WIDTH, HEIGHT);

                    //define the width of audio bar display
                    let barWidth = (WIDTH / bufferLengthAlt) * 1.25;
                    let barHeight;
                    let x = 0;

                    //iterate through audio levels and add bars to canvas
                    for (let i = 0; i < bufferLengthAlt; i++) {
                        barHeight = dataArrayAlt[i];

                        canvasCtx.fillStyle = 'rgb(255, 255, 255)';
                        canvasCtx.fillRect(x, HEIGHT - barHeight / 2, barWidth, barHeight / 2);

                        x += barWidth + 1;
                    }
                };

                drawAlt();
            }
        }
    }

</script>

<style scoped lang="scss">
    @import "../../../sass/_variables.scss";

    a {
        color: white;
    }
    img {
        align-self: center;
    }
    canvas {
        height: 30px;
        width: 40px;
    }
    .video-container {
        position: relative;
        background-color: #000;
        height: 44%;
    }
    #controlBar{
        display: flex;
        justify-content: space-between;
        padding: 0px 10px;
        background-color: $sim-heading;
        height: 40px;
        font-size: 25px;
        margin-top: 0px;
    }
    .recording{
        color: red;
    }
    #call_video{
        height: 100%;
        width: 100%;
    }
    .contact-inner{
        padding: 10px;
        display: flex;
        flex-flow: row;
        cursor: pointer;
    }
    .contact-inner:nth-child(even) {
        border-top: solid 1px;
        border-bottom: solid 1px;
        border-color: #d9dcde;
    }
    .characterInfo{
        margin-left: 2rem;
        width: 58%;
    }
    .characterActive{
        color: #57AB58;
        align-self:center;
    }
    .activeIcon{
        font-size: 14px;
    }
    #characterQuestions{
        height: 21rem;
        overflow-y: scroll;
        background-color: white;
        display: flex;
        flex-flow: inherit;
        justify-content: space-between;
    }
    .dropdown-menu{
        min-width: 220px;
    }
    .dropdown-toggle::after {
        display:none
    }
    #personal_video{
        position: absolute;
        right: 0px;
        bottom: -6px;
        height: 125px;
        width: 150px;
    }

</style>