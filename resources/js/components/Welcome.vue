<template>
    <div>
        <welcome-header></welcome-header>
        <welcome-content></welcome-content>
        <welcome-footer></welcome-footer>
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
