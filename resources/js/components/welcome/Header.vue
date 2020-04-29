<template>
    <div>
    <v-parallax
        dark
        src="https://cdn.vuetifyjs.com/images/backgrounds/vbanner.jpg"
    >
        <v-row
            align="center"
            justify="center"
        >
            <v-col class="text-center" cols="12">
                <h1 class="display-1 font-weight-thin mb-4">Vuetify.js</h1>
                <h4 class="subheading">Build your application today!</h4>
            </v-col>
        </v-row>
    </v-parallax>

            <v-footer
                v-bind="localAttrs"
                :padless="padless"
            >
                <v-card
                    flat
                    tile
                    width="100%"
                    class="red lighten-1 text-center"
                >
                    <v-card-text>
                        <v-btn
                            v-for="icon in icons"
                            :key="icon"
                            class="mx-4"
                            icon
                        >
                            <v-icon size="24px">{{ icon }}</v-icon>
                        </v-btn>
                    </v-card-text>

                    <v-divider></v-divider>

                    <v-card-text class="white--text">
                        {{ new Date().getFullYear() }} — <strong>Vuetify</strong>
                    </v-card-text>
                </v-card>
            </v-footer>
    </div>
</template>

<script>
    export default {
        mounted() {
            console.log('Component mounted.')
        }
    }
</script>
