<template>
    <div>
        <v-app-bar
            color="#6A76AB"
            shrink-on-scroll
            prominent
            src="img/fon.png"
        >
            <template v-slot:img="{ props }">
                <v-img
                    v-bind="props"
                    gradient="to top right, rgba(253,255,243,.7), rgba(253,255,243,.7)"
                ></v-img>
            </template>

            <v-app-bar-nav-icon></v-app-bar-nav-icon>


            <v-spacer></v-spacer>

            <v-btn icon class="mt-2">
                <v-badge
                    :content="messages"
                    :value="messages"
                    color="green"
                    overlap
                >
                    <v-icon large>mdi-email</v-icon>
                </v-badge>
            </v-btn>

            <v-menu
                bottom
                left
            >
                <template v-slot:activator="{ on }" >
                    <v-btn
                        @click="drawer = true"
                        text
                        large
                        class="ma-2"
                    >
                        <v-avatar size="40" class="mr-2">
                            <v-img src="https://cdn.vuetifyjs.com/images/lists/2.jpg"></v-img>
                        </v-avatar>Філоненко Андрій
                    </v-btn>
                </template>

                <v-list>
                    <v-list-item>
                        <v-list-item-icon>
                            <v-icon>mdi-settings</v-icon>
                        </v-list-item-icon>
                        <v-list-item-title>Налаштування</v-list-item-title>
                    </v-list-item>

                    <v-list-item>
                        <v-list-item-icon>
                            <v-icon>mdi-logout</v-icon>
                        </v-list-item-icon>
                        <v-list-item-title>Вийти</v-list-item-title>
                    </v-list-item>
                </v-list>
            </v-menu>

            <template v-slot:extension>
                <v-tabs
                        centered
                        icons-and-text
                >
                    <v-tab>Головна</v-tab>
                    <v-tab>Журнал<v-icon>mdi-newspaper</v-icon></v-tab>
                </v-tabs>
            </template>
        </v-app-bar>
        <v-navigation-drawer
            v-model="drawer"
            :right="true"
            absolute
            temporary
            style="width: 400px;"
        >
            <v-list
                nav
                dense
            >
                <v-list-item-group
                    active-class="deep-purple--text text--accent-4"
                >
<!--                    <v-list-item>-->
<!--                        <v-list-item-icon>-->
<!--                            <v-icon>mdi-account</v-icon>-->
<!--                        </v-list-item-icon>-->
<!--                        <v-list-item-title>Акаунт</v-list-item-title>-->
<!--                    </v-list-item>-->
                    <v-list-item>
                        <v-list-item-icon>
                            <v-icon>mdi-settings</v-icon>
                        </v-list-item-icon>
                        <v-list-item-title>Налаштування</v-list-item-title>
                    </v-list-item>

                    <v-list-item>
                        <v-list-item-icon>
                            <v-icon>mdi-logout</v-icon>
                        </v-list-item-icon>
                        <v-list-item-title @click="logout()">Вийти</v-list-item-title>
                    </v-list-item>

                </v-list-item-group>
            </v-list>
        </v-navigation-drawer>
        <div class="user-content" style="padding: 30px 40px">
            <v-row>
                <v-col cols="12" sm="3">
                    <v-select
                        outlined
                        :items="classes"
                        label="Класс"
                        :disabled="false"
                        v-model="classe"
                        required
                    ></v-select>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12" sm="3">
                    <v-select
                        outlined
                        :items="classes"
                        label="Предмет"
                        :disabled="false"
                        v-model="classe"
                        required
                    ></v-select>
                </v-col>
            </v-row>
        </div>
    </div>

</template>

<script>
        import * as auth from '../../services/auth_service.js'
        export default {
            data: () => ({
                messages: 2,
                classes: [
                    '9-A',
                    '9-Б',
                    '8-А'
                ],
                show: false,
                drawer: false
                ,classe: ''
            }),
            methods: {
                logout: async function() {
                    auth.logout();
                    location.reload();
                }
            }
        }
</script>

<style scoped>

</style>
