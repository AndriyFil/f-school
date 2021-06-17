<template>
    <div data-app>
        <v-app-bar
            color="#42A5F5"
            src=''
        >
            <v-app-bar-nav-icon v-if="user.role == 'teacher'" color="white" @click="drawer_menu = true"></v-app-bar-nav-icon>
            <v-spacer></v-spacer>

<!--            <v-btn icon class="" color="white">-->
<!--                <v-badge-->
<!--                    :content="messages"-->
<!--                    :value="messages"-->
<!--                    color="green"-->
<!--                    overlap-->
<!--                >-->
<!--                    <v-icon>mdi-email</v-icon>-->
<!--                </v-badge>-->
<!--            </v-btn>-->
            <v-menu offset-y>
                <template v-slot:activator="{ on }">
                    <v-btn
                        @click="drawer = true"
                        text
                        large
                        class="ma-2"
                        color="white"
                        v-on="on"
                    >
                        <v-avatar size="32" class="mr-2">
                            <v-img :src="user_data.photo"></v-img>
                        </v-avatar>{{ user_data.lastname + ' ' + user_data.firstname.charAt(0).toUpperCase() + '.' + user_data.middlename.charAt(0).toUpperCase() + '.'}}
                    </v-btn>
                </template>

                <v-list>
<!--                    <v-list-item link>-->
<!--                        <v-list-item-icon>-->
<!--                            <v-icon>mdi-settings</v-icon>-->
<!--                        </v-list-item-icon>-->
<!--                        <v-list-item-title>Налаштування</v-list-item-title>-->
<!--                    </v-list-item>-->
                    <form id="logout-form" action='logout' method="POST">
                        <input type="hidden" name="_token" :value="token">
                    </form>
                    <v-list-item @click =" logout " link>
                        <v-list-item-icon>
                            <v-icon>mdi-logout</v-icon>
                        </v-list-item-icon>
                        <v-list-item-title>Вийти</v-list-item-title>
                    </v-list-item>
                </v-list>
            </v-menu>
        </v-app-bar>
        <v-row v-if="user_data.role === 'teacher' && this.$route.path == '/' ">
              <v-col cols="12">
                    <v-card>
                        <div class="start-page">
                            <div class="item" @click="clearLocalStorage('/journal')">
                                <v-hover v-slot:default="{ hover }">
                                    <v-card>
                                        <v-img src="/img/journal2.jpg" max-width="500px" max-height="520">
                                            <v-expand-transition>
                                                <div
                                                    v-if="hover"
                                                    class="d-flex  grey darken-2 v-card--reveal display-3 white--text"
                                                    style="height: 100%;"
                                                >
                                                    Перейти
                                                </div>
                                            </v-expand-transition>
                                        </v-img>
                                        <div class="title-link">
                                            Журнал
                                        </div>
                                    </v-card>
                                </v-hover>
                            </div>
                            <div class="item"  @click="clearLocalStorage('/my_class')">
                                <v-hover v-slot:default="{ hover }">
                                    <v-card>
                                        <v-img src="/img/myclass2.jpg" max-width="500px" max-height="520">
                                            <v-expand-transition>
                                                <div
                                                    v-if="hover"
                                                    class="d-flex  grey darken-2 v-card--reveal display-3 white--text"
                                                    style="height: 100%;"
                                                >
                                                    Перейти
                                                </div>
                                            </v-expand-transition>
                                        </v-img>
                                        <div class="title-link">
                                            Мій клас
                                        </div>
                                    </v-card>
                                </v-hover>
                            </div>
                        </div>
                    </v-card>
              </v-col>
        </v-row>
        <v-row v-if="user_data.role === 'admin'">
            <v-col colls="12 text-center">
                <div class="text-center" style="font-family: 'Amatic SC', sans-serif; font-size: 50px;">Адміністрація ще не готова :( <br>
                    Спробуйте демо-версію вчителя: <br>
                    filonenkoandriysarmat@gmail.com - логін <br>
                    NKv6PBVS - пароль <br>
                    Спробуйте демо-версію батьків: <br>
                    777@gg.com - логін <br>
                    Bhu9kvGP - пароль <br>
                </div>
            </v-col>
        </v-row>
<!--        <v-navigation-drawer-->
<!--            v-model="drawer"-->
<!--            :right="true"-->
<!--            absolute-->
<!--            temporary-->
<!--            style="width: 400px;"-->
<!--        >-->
<!--            <v-list-->
<!--                nav-->
<!--                dense-->
<!--            >-->
<!--                <v-list-item-group-->
<!--                    active-class="deep-purple&#45;&#45;text text&#45;&#45;accent-4"-->
<!--                >-->
<!--                    <v-list-item>-->
<!--                        <v-list-item-icon>-->
<!--                            <v-icon>mdi-settings</v-icon>-->
<!--                        </v-list-item-icon>-->
<!--                        <v-list-item-title>Налаштування</v-list-item-title>-->
<!--                    </v-list-item>-->

<!--                    <v-list-item @click="logout()">-->
<!--                        <v-list-item-icon>-->
<!--                            <v-icon>mdi-logout</v-icon>-->
<!--                        </v-list-item-icon>-->
<!--                        <v-list-item-title>Вийти</v-list-item-title>-->
<!--                    </v-list-item>-->

<!--                </v-list-item-group>-->
<!--            </v-list>-->
<!--        </v-navigation-drawer>-->
        <v-navigation-drawer
            v-model="drawer_menu"
            :left="true"
            absolute
            temporary
            style="width: 400px;"
            v-if="user_data.role === 'teacher'"
        >
            <v-list
                nav
                dense
            >
                <v-list-item-group
                    active-class="deep-purple--text text--accent-4"
                >
                    <v-list-item @click="clearLocalStorage('/')">
                        <v-list-item-icon>
                            <v-icon>mdi-home</v-icon>
                        </v-list-item-icon>
                        <v-list-item-title>На головна</v-list-item-title>
                    </v-list-item>

                    <v-list-item v-if="user_data.role === 'teacher'" @click="clearLocalStorage('/journal')">
                        <v-list-item-icon>
                            <v-icon>mdi-notebook-outline</v-icon>
                        </v-list-item-icon>
                        <v-list-item-title>Журнал</v-list-item-title>
                    </v-list-item>

                    <v-list-item @click="clearLocalStorage('/my_class')" v-if="user_data.role === 'teacher'" >
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
        import routes from "../../router.js"
        import VueRouter from 'vue-router';
        export default {
            props: ['user_data'],
            data: () => ({
                messages: 2,
                show: false,
                drawer: false
                , drawer_menu: false
                , token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                , user:
                    {
                        photo: ""
                        , name: ""
                        , role: ""
                    }

            }),
            methods: {
                logout: async function() {
                    document.getElementById('logout-form').submit()
                }
                , clearLocalStorage(route) {
                    this.deleteCookie('class');
                    this.deleteCookie('subject');
                    this.deleteCookie('section');
                    this.deleteCookie('date_start');
                    this.deleteCookie('date_end');
                    this.deleteCookie('sub_name');
                    this.deleteCookie('theme_name');
                    this.$router.push(route)

                }
                , setCookie(name, value, options = {}) {

                    options = {
                        path: '/',
                        // при необходимости добавьте другие значения по умолчанию
                        ...options
                    };

                    if (options.expires instanceof Date) {
                        options.expires = options.expires.toUTCString();
                    }

                    let updatedCookie = encodeURIComponent(name) + "=" + encodeURIComponent(value);

                    for (let optionKey in options) {
                        updatedCookie += "; " + optionKey;
                        let optionValue = options[optionKey];
                        if (optionValue !== true) {
                            updatedCookie += "=" + optionValue;
                        }
                    }

                    document.cookie = updatedCookie;
                }
                , deleteCookie(name) {
                    document.cookie = name+'=; Max-Age=-99999999;';
                }
            }
            , mounted () {
                console.log(this.user_data)
                this.user.photo = this.user_data.photo
                this.user.role = this.user_data.role
                this.user.name = this.user_data.lastname + ' ' + this.user_data.firstname.charAt(0).toUpperCase() + '.' + this.user_data.middlename.charAt(0).toUpperCase() + '.';
                console.log(this.$route.path)
            },
            beforeRouteEnter (to, from, next) {
                this.clearLocalStorage()
            },

        }
</script>

<style scoped>

</style>
