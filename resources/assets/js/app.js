
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');


/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('example-component', require('./components/ExampleComponent.vue'));
Vue.component('left-menu', require('./components/LeftMenu/LeftMenu.vue'));
//Vue.component('right-menu', require('./components/RightMenu/RightMenu.vue'));

//Vue.component('gallery', require('./components/Gallery/Gallery'));
Vue.component('dash', require('./components/Dashboard/dashboard.vue'));
Vue.component('email', require('./components/Email/Email.vue'));
//Vue.component('gallery', require('./components/Gallery/Gallery.vue'));
Vue.component('videocall', require('./components/VideoCall/videoCall.vue'));
Vue.component('library', require('./components/Library/library.vue'));
Vue.component('chatbot', require('./components/Chatbot/Chatbot.vue'));
Vue.component('slackbot', require('./components/Chatbot/Slackbot.vue'));
Vue.component('classes', require('./components/Classes/Classes.vue'));
Vue.component('editor', require('./components/Library/articleEditor.vue'));
Vue.component('navigation', require('./components/NavigationLink/navigation.vue'));
Vue.component('v-select', require('vue-select'));

import store from './vuex/store';
import { mapActions, mapGetters } from 'vuex';

const app = new Vue({
    el: '#app',
    store,
    data: function () {
        return {
            user: {}
        }
    },
    methods: mapActions([
        'SET_TASKS',
        'SET_USER',
        'SET_SIMULATION',
        'SET_NEW_EMAILS',
        'SET_NEW_ARTIFACTS'
    ]),
    computed: {
        ...mapGetters([
            'GET_TASKS'
        ])
    },
    mounted() {

        // When the program loads we are going to get the user object and all tasks.  See store.js
        axios.get('/user').then( response => {
            this.SET_TASKS();
            this.SET_USER();
            this.SET_SIMULATION();
            this.SET_NEW_EMAILS();
            //this.SET_NEW_ARTIFACTS();
        }).catch(error => {
            console.log( error);
        })

    }

});
