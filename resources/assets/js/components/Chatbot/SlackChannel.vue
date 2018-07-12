<template>
    <li class="clearfix">
        <img :src="channel.img_small" alt="avatar" />
        <div class="about">
            <div class="name">{{ channel.name }}</div>
            <div class="status">
                {{ channel.role }}
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
        margin-top: 7px;
        border-radius: 50%;
        border: 2px solid $blue;
    }
    .name {

    }
</style>
