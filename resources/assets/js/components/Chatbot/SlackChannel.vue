<template>
    <li class="clearfix">
        <img :src="channel.img_small" alt="avatar" />
        <div class="about">
            <div class="name">{{ channel.name }}</div>
            <div class="status">
                <i class="fa fa-circle online"></i> {{ channel.status }}
            </div>
        </div>
    </li>
</template>

<script>
    export default {

        mounted() {
//            console.log('Component mounted.')
        },
        props: ['channel']

    }
</script>

<style scoped lang="scss">
    $green: #86BB71;
    $blue: #94C2ED;

    img {
        border-radius: 50%;
        border: 2px solid $blue;
    }
</style>
