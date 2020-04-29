<template>
<main>
    <user-navbar></user-navbar>
</main>
</template>

<script>
        import Navbar from "./user/Navbar";
        export default {
                name: "App"
                , components: {
                    'user-navbar': Navbar
                }
        }
</script>

<style scoped>

</style>
