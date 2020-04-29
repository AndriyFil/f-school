<template>
    <div>
        <v-footer
        :padless="padless"
        :absolute = true
    >
        <v-card
            flat
            tile
            class="footer-welcome"
        >
            <v-card-text>
                <v-btn
                    v-for="icon in icons"
                    :key="icon.icon"
                    class="mx-4"
                    icon
                    :href="icon.href"
                    target="_blank"
                >
                    <v-icon size="24px">{{ icon.icon }}</v-icon>
                </v-btn>
            </v-card-text>

            <v-divider dark></v-divider>

            <v-card-text>
                <span>{{ new Date().getFullYear() }} — <strong>Filonenko</strong></span>
            </v-card-text>
        </v-card>
    </v-footer>
    </div>
</template>

<script>
    export default {
        data: () => ({
            icons: [
                {icon: 'mdi-email', href:'mailto:filonekoandriysarmat@gmail.com'}
                , {icon: 'mdi-facebook', href:'https://www.facebook.com/profile.php?id=100012109003789'}
                , {icon: 'mdi-twitter', href:'https://twitter.com/filonenkoAnd'}
                , {icon: 'mdi-telegram', href:"https://t.me/Filonenko_Andrii"}
            ],
            padless: true
            , margin: ''
        }),
    }
</script>

<style scoped>

</style>
