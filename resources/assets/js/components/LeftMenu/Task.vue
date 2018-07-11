<template>
    <li>
        <label>
            <span class="checkmark unchecked" v-if="!task.complete" v-on:click="TOGGLE_TASK(task.task_id)"></span>
            <span class="checkmark checked" v-if="task.complete" v-on:click="TOGGLE_TASK(task.task_id)"></span>
            <span class="navigation" v-on:click="navigateToComponent(task)">{{ task.title }}</span>
            <div class="task-description">
                {{ task.description }}
            </div>
        </label>
    </li>
</template>

<script>
    import { mapGetter, mapActions } from 'vuex'

    export default {
        props: ['task', 'complete'],
        mounted() {
        },
        data: function () {
            return {
                taskComplete: this.complete
            }
        },
        methods: {
            ... mapActions([
                'TOGGLE_TASK'
            ]),
            navigateToComponent: function(task){
                window.location.href = task.task_type;
            }
        }

    }
</script>

<style scoped lang="scss">
    @import "../../../sass/_variables.scss";

    li {
        margin-top: 10px;
        margin-bottom: 12px;
    }

    label {
        display: block;
        position: relative;
        padding-left: 28px;
        margin-top: 11px;
        margin-bottom: 12px;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    /* Hide the browser's default checkbox */
    label input {
        display: none;
    }

    /* Create a custom checkbox */
    .checkmark {
        position: absolute;
        cursor: pointer;
        top: 4px;
        left: 0;
        height: 16px;
        width: 16px;
        background-color: #eee;
        border: 1px solid $sim-heading;
    }

    .navigation {
        cursor: pointer;
        font-family: "Raleway", sans-serif;
    }
    .task-description {
        font-size: 14px;
        color: grey;
    }

    /* On mouse-over, add a grey background color */
    .checkmark:hover {
        background-color: #ccc;
    }

    /* When the checkbox is checked, add a blue background */
    .checked {
        background-color: #A7BBC2;
        background: url("/img/icon-check.svg") no-repeat center center;
    }

    /* Create the checkmark/indicator (hidden when not checked) */
    .checkmark:after {
        content: "";
        position: absolute;
        display: none;
    }

    /* Show the checkmark when checked */
    .checked ~ .checkmark:after {
        display: block;
    }

    /* Style the checkmark/indicator */
    /*label .checkmark:after {*/
        /*left: 9px;*/
        /*top: 5px;*/
        /*width: 5px;*/
        /*height: 10px;*/
        /*border: solid white;*/
        /*border-width: 0 3px 3px 0;*/
        /*-webkit-transform: rotate(45deg);*/
        /*-ms-transform: rotate(45deg);*/
        /*transform: rotate(45deg);*/
    /*}*/
</style>