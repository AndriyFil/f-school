<template>
<main>
    <user-navbar></user-navbar>
    <router-view></router-view>
</main>
</template>

<script>
        import Navbar from "./user/Navbar";
        import * as auth from '../services/auth_service.js'
        export default {
                name: "App"
                , components: {
                    'user-navbar': Navbar
                }
        }
</script>

<style scoped>

</style>
