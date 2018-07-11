<template>
    <div id="user-progress">
        <h1>Progress</h1>
        <ul>
            <li v-for="day in TASKS_BY_DAY">
                <img :src="'/img/days/day-' + day[0].day + '.svg'" alt="day" class="day">
                <span v-for="task in day"
                      v-if="task.complete"
                      class="task">
                    <img src="/img/complete.svg" alt="complete">
                </span>
                <span v-for="task in day"
                      v-if="!task.complete"
                      class="task">
                    <img src="/img/incomplete.svg" alt="incomplete">
                </span>
            </li>
        </ul>
        <hr>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'

    export default {
        mounted() {

        },
        computed: {
            ...mapGetters([
                'TASKS_BY_DAY'
            ])
        }
    }
</script>

<style scoped>
    ul {
        list-style: none;
        padding-left: 0;
    }

    li {
        margin-top: 10px;
        margin-bottom: 12px;

    }

    .task, .day {
        margin-right: 10px;
    }

</style>