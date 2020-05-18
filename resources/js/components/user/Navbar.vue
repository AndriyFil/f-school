<template>
    <div data-app>
        <v-app-bar
            color="#42A5F5"
            src=''
        >
            <v-app-bar-nav-icon color="white" @click="drawer_menu = true"></v-app-bar-nav-icon>
            <v-spacer></v-spacer>

            <v-btn icon class="" color="white">
                <v-badge
                    :content="messages"
                    :value="messages"
                    color="green"
                    overlap
                >
                    <v-icon>mdi-email</v-icon>
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
                        color="white"
                    >
                        <v-avatar size="32" class="mr-2">
                            <v-img :src="user.photo"></v-img>
                        </v-avatar>{{ user.name }}
                    </v-btn>
                </template>

                <v-list large>
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
                    <v-list-item>
                        <v-list-item-icon>
                            <v-icon>mdi-settings</v-icon>
                        </v-list-item-icon>
                        <v-list-item-title>Налаштування</v-list-item-title>
                    </v-list-item>

                    <v-list-item @click="logout()">
                        <v-list-item-icon>
                            <v-icon>mdi-logout</v-icon>
                        </v-list-item-icon>
                        <v-list-item-title>Вийти</v-list-item-title>
                    </v-list-item>

                </v-list-item-group>
            </v-list>
        </v-navigation-drawer>
        <v-navigation-drawer
            v-model="drawer_menu"
            :left="true"
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
                    <v-list-item to="/">
                        <v-list-item-icon>
                            <v-icon>mdi-home</v-icon>
                        </v-list-item-icon>
                        <v-list-item-title>На головна</v-list-item-title>
                    </v-list-item>

                    <v-list-item to="/journal">
                        <v-list-item-icon>
                            <v-icon>mdi-notebook-outline</v-icon>
                        </v-list-item-icon>
                        <v-list-item-title>Журнал</v-list-item-title>
                    </v-list-item>

                    <v-list-item to="/my_class">
                        <v-list-item-icon>
                            <v-icon>mdi-school-outline</v-icon>
                        </v-list-item-icon>
                        <v-list-item-title>Мій клас</v-list-item-title>
                    </v-list-item>

                </v-list-item-group>
            </v-list>
        </v-navigation-drawer>
    </div>
</template>

<script>
        import * as auth from '../../services/auth_service.js'
        export default {
            data: () => ({
                messages: 2,
                show: false,
                drawer: false
                , drawer_menu: false
                , user:
                    {
                        photo: ""
                        , name: ""
                    }

            }),
            methods: {
                logout: async function() {
                    auth.logout();
                    location.replace('/');
                }
            }
            ,beforeCreate: async function() {
                try {
                    const response = await auth.getProfile()
                    this.$store.dispatch('authenticate', response.data)
                    this.user.photo = this.$store.state.profile.photo
                    this.user.name = this.$store.state.profile.lastname + ' ' + this.$store.state.profile.firstname.charAt(0).toUpperCase() + '.' + this.$store.state.profile.middlename.charAt(0).toUpperCase() + '.';
                } catch (e) {
                    alert(e.message)
                }
            }
        }
</script>

<style scoped>

</style>
