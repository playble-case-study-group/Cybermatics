<template>
    <div id="record-message">
        <img v-if="!this.recording" class="video-placeholder" src="/img/videocall/video-recording-placeholder.jpg">

        <video v-else id="record_video" poster="/img/videocall/video-recording-placeholder.jpg" autoplay muted="muted">
            <source src="/video/record.mp4" type="video/mp4">
        </video>

        <div class="modal" id="saveModal" tabindex="-1" role="dialog" aria-labelledby="saveModal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Send Message?</h5>
                    </div>
                    <div class="modal-body">
                        <p>Would you like to send your recorded message?</p>
                    </div>
                    <div class="modal-footer">
                        <button
                                @click="saveVideoMessage(blob,href)"
                                type="button"
                                class="videoOptions btn btn-primary">Send Message</button>
                        <button
                                @click="clearVideoMessage"
                                type="button"
                                class="videoOptions btn btn-secondary"
                                data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapActions } from 'vuex'

    export default {

        props: {
            recording: Boolean,
            clickedCharacter: Number
        },
        data() {
            return {
                cancel: null,
                blob: null,
                href: null
            }
        },
        watch:{
            recording: function(){
                this.cancel = this.recording;

                if(this.recording){
                    this.leaveMessage();
                }
            },
            clickedCharacter: function() {
                this.$forceUpdate();
            }
        },
        methods: {
            leaveMessage: function(){
                this.blob = null;
                this.href = null;

                //set that we want both audio and video
                const constraints = {
                    video: true,
                    audio: true
                };

                //start recording
                navigator.mediaDevices.getUserMedia(constraints)
                    .then(this.handleSuccess.bind(this))
                    .catch(this.handleFailure);
            },
            handleFailure: function(error){
                //if they don't have browser support, try a lower compatibility function or fail
                console.error('Reeeejected2!', error);

            },
            handleSuccess: function(stream) {
                const video = document.querySelector('video');

                const recordedChunks = [];
                let audioStream = stream.getTracks()[0];
                let videoStream = stream.getTracks()[1];

                //initialize and display recording stream
                const mediaRecorder = new MediaRecorder(stream);
                video.srcObject = stream;

                //start recording when video is loaded
                video.addEventListener('loadeddata', function () {
                    mediaRecorder.start(3000);
                })

                //set local variable to set correct scope
                let appScope = this;

                //set video to timeout if recording goes over 5 mins
                let timeout = 5 * 60 * 1000;
                setTimeout(function () {
                    appScope.cancel = false;
                }, timeout)

                //save data as it becomes available. Stop recording if stop button has been triggered
                mediaRecorder.addEventListener('dataavailable', function (e) {
                    if (e.data.size > 0) {
                        recordedChunks.push(e.data);
                    }
                    if (appScope.cancel == false && mediaRecorder.state != 'inactive') {
                        mediaRecorder.stop();
                    }
                });

                //when recording stops, save the video object and stop displaying video stream
                mediaRecorder.addEventListener('stop', function () {
                    audioStream.stop();
                    videoStream.stop();

                    //save the recorded data to a blob, and give it a url
                    const blob = new Blob(recordedChunks, {type: 'video/webm'});
                    var href = URL.createObjectURL(new Blob(recordedChunks), {type: 'video/webm'});
                    appScope.blob = blob;
                    appScope.href = href;
                    $('#saveModal').show();
                })
            },
            saveVideoMessage: function(blob, href){
                $('#saveModal').hide();
                let appScope = this;
                //append all needed information into a form
                let data = new FormData();
                data.append('user', this.$store.state.user.user_id);
                data.append('character', this.clickedCharacter);

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
                            .then(r => {
                                appScope.$emit('saveSuccess');
                            })
                            .catch(e => {
                                appScope.$emit('saveFailure');
                            });
                    };
                })
                this.$emit('showContacts');
            },
            clearVideoMessage: function() {
                $('#saveModal').hide();
                this.blob = null;
                this.href = null;
            }
        }
    }

</script>

<style scoped>
    #record-message {
        height: 100%;
    }
    #record_video{
        height: 100%;
        width: 100%;
    }
    .video-placeholder {
        height: 100%;
        width: 100%;
    }


</style>