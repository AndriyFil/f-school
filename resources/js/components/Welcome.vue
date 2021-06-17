<template>
    <div class="main" data-app>
        <welcome-header></welcome-header>
        <div class="content-footer-block">
            <welcome-content></welcome-content>
            <welcome-footer></welcome-footer>
        </div>
    </div>
</template>

<script>
        import Footer from "./welcome/Footer";
        import Header from "./welcome/Header";
        import Content from "./welcome/Content";
        export default {
                name: "Welcome"
                , components: {
                       'welcome-footer': Footer
                        , 'welcome-header': Header
                        , 'welcome-content': Content
                }
        }
</script>

<style scoped>

</style>
