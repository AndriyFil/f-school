<template>
<main>
<!--    <user-navbar></user-navbar>-->
    <router-view></router-view>
<!--    <parent-tpl></parent-tpl>-->
</main>
</template>

<script>
        import Navbar from "./user/Navbar";
        import * as auth from '../services/auth_service.js'
        import Diary from "./user/Diary";
        export default {
                name: "App"
                , components: {
                    'user-navbar': Navbar
                    , 'parent-tpl': Diary
                }
        }
</script>

<style scoped>

</style>
