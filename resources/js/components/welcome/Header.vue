<template>
    <div>
        <div class="header">
            <v-progress-linear
                :active="active_progress"
                :indeterminate="true"
                absolute
                color="white"
            ></v-progress-linear>
            <div class="header-items">
                <div class="item">
                    <div class="block-title">
                        <div class="main-title">Моя Школа</div>
                        <div class="block-btn">
                            <button class="login" @click="login_popup = true">Увійти</button>
                            <div class="register">
                                Не зареєстровані в системі? <a @click="register_popup = true">Реєстрація</a>
                            </div>
                        </div>
                    </div>

<!--                    <div class="subtitle">сервіс, що призначений для загальноосвітніх навчальних закладів, щоб покращити якість навчання!</div>-->
                </div>

            </div>
            <v-row justify="center" data-app>
                <v-dialog  v-model="register_popup" max-width="600px">
                    <v-form ref="form" v-model="valid" lazy-validation>
                        <v-card>
                            <v-card-title>
                                <span class="headline">Реєстрація школи</span>
                            </v-card-title>
                            <v-card-text>
                                <v-container>
                                    <v-row>
                                        <v-col cols="12">
                                            <v-text-field label="Назва школи*" v-model="register_data.school_name" :rules="schoolNameRules" required></v-text-field>
                                        </v-col>
                                        <v-col cols="12" sm="6">
                                                <v-autocomplete
                                                    :items="regions"
                                                    item-text="reg_name"
                                                    item-value="reg_id"
                                                    label="Область*"
                                                    :disabled="false"
                                                    :rules="regionRules"
                                                    v-model="register_data.region"
                                                    required
                                                    @input="getCities"
                                                ></v-autocomplete>
                                        </v-col>
                                        <v-col cols="12" sm="6">
                                            <v-autocomplete
                                                :items="cities"
                                                item-text="cit_name"
                                                item-value="cit_id"
                                                label="Населений пункт*"
                                                v-model="register_data.city"
                                                :rules="cityRules"
                                                required
                                            ></v-autocomplete>
                                        </v-col>
                                        <v-col cols="12" class="text-sm-center headline"> Введіть інформацію про представника </v-col>
                                        <v-col cols="12" sm="6" md="4">
                                            <v-text-field label="Ім'я*" v-model="register_data.firstname" :rules="firstnameRules" required></v-text-field>
                                        </v-col>
                                        <v-col cols="12" sm="6" md="4">
                                            <v-text-field label="Прізвище*" v-model="register_data.lastname" :rules="lastnameRules" required></v-text-field>
                                        </v-col>
                                        <v-col cols="12" sm="6" md="4">
                                            <v-text-field label="По-батькові" v-model="register_data.middlename"></v-text-field>
                                        </v-col>
                                        <v-col cols="12">
                                            <v-text-field label="Телефон" placeholder="+380681111111" v-model="register_data.phone_number"></v-text-field>
                                        </v-col>
                                        <v-col cols="12">
                                            <v-text-field label="Email*" v-model="register_data.email" :rules="emailRules" required></v-text-field>
                                        </v-col>
                                        <v-col cols="12">
                                            <small>*обов'язкові поля для заповнення</small>
                                        </v-col>
                                        <v-col cols="12 text-sm-center">
                                            <v-btn color="blue darken-1" dark @click="register()" :loading="active_progress">Зареєструвати</v-btn>
                                        </v-col>
                                    </v-row>
                                </v-container>
                            </v-card-text>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="blue" text @click="register_popup = false">Закрити</v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-form>
                </v-dialog>
                <v-dialog v-model="login_popup" max-width="400px">
                    <v-form ref="form_login" @submit="login()" v-model="valid" method="post" action="login" lazy-validation>
                        <input type="hidden" name="_token" :value="login_data._token">
                        <v-card>
                            <v-card-title>
                                <span class="headline">Вхід</span>
                            </v-card-title>
                            <v-card-text>
                                <v-container>
                                    <v-row>
                                        <v-col cols="12">
                                            <div v-show="notification">
                                                <h4>Пароль було відправлено на електронну пошту {{ register_data.email }}.</h4>
                                            </div>
                                            <div>
                                                <v-text-field label="Email" name="email" v-model="login_data.email" :rules="emailRules" required></v-text-field>
                                            </div>
                                            <div>
                                                <v-text-field
                                                    v-model="login_data.password"
                                                    :append-icon="show_pass ? 'mdi-eye' : 'mdi-eye-off'"
                                                    :type="show_pass ? 'text' : 'password'"
                                                    name="password"
                                                    label="Пароль"
                                                    :rules="[rules.required]"
                                                    @click:append="show_pass = !show_pass"
                                                ></v-text-field>
                                            </div>
<!--                                            <div>-->
<!--                                                <v-checkbox-->
<!--                                                    v-model="login_data.remember"-->
<!--                                                    name="remember"-->
<!--                                                    label="Запам'ятати"-->
<!--                                                ></v-checkbox>-->
<!--                                            </div>-->
                                            <v-col cols="12 text-sm-center">
                                                <v-btn color="blue" type="submit" dark :loading="active_progress">Увійти</v-btn>
                                            </v-col>
                                        </v-col>
                                    </v-row>
                                </v-container>
                            </v-card-text>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="blue darken-1"
                                       text
                                       @click="login_popup = false"
                                       >Закрити
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-form>
                </v-dialog>
            </v-row>
            <v-snackbar
                v-model="snackbar"
                color="success"
                top
                :timeout="0"
            >
                <v-icon dark class="mr-3">mdi-email-check</v-icon> Пароль відправлено на пошту: <u> {{ register_data.email }}</u>
                <v-btn
                    dark
                    text
                    @click="snackbar = false"
                >
                    Close
                </v-btn>
            </v-snackbar>
            <v-snackbar
                v-model="snackbar_error"
                color="error"
                top
                :timeout="0"
            >
                <v-icon dark class="mr-3">mdi-alert-rhombus</v-icon> Помилка реєстрації
                <v-btn
                    dark
                    text
                    @click="snackbar_error = false"
                >
                    Close
                </v-btn>
            </v-snackbar>
        </div>
    </div>
</template>

<script>
    import * as auth from '../../services/auth_service.js'
    export default {
        data: () => ({
            register_popup: false,
            login_popup: false,
            valid: true,
            active_progress: false,
            snackbar: false,
            show_pass: false,
            notification: false,
            snackbar_error: false,
            schoolNameRules: [
                v => !!v || "Назва школи обов'язкова",
            ],

            firstnameRules: [
                v => !!v || "Ім'я обов'язкове",
            ],

            lastnameRules: [
                v => !!v || "Прізвище обов'язкове",
            ],
            regionRules: [
                v => !!v || "Область обов'язкова",
            ],
            cityRules: [
                v => !!v || "Місто обов'язкове",
            ],
            emailRules: [
                v => !!v || "E-mail обов'язковий",
                v => /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'E-mail повинен бути дійсними'
            ],
            cities: []
            , regions: []
            , rules: {
                required: v => !!v || 'Обов\'язковий.',
                emailMatch: () => !this.status_login || ('Пароль і логін не співпадають'),
            }
            , status_login:true
            , register_data: {
                email: '',
                phone_number: '',
                city: '',
                school_name: '',
                firstname: '',
                lastname: '',
                middlename: '',
                region: '',
            }
            , login_data: {
                email: '',
                password: '',
                remember: '',
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
            , errors: {}
        }),
        methods: {
            validate () {
                this.$refs.form.validate()
            },
            submit () {
                this.$refs.form.submit()
            },
            async getCities () {
                this.cities = await request('/api/get_cities/'+this.register_data.region)
            },
            async register () {
                this.validate()
                try {
                    this.active_progress = true;
                    const response = await request('/api/auth/register', 'POST', this.register_data)
                    this.active_progress = false;
                    if(response.status_code == 201) {
                        this.register_popup = false;
                        this.login_popup = true;
                        this.snackbar = true;
                        this.login_data.email = this.register_data.email;
                    } else {
                        this.snackbar_error = true
                    }

                } catch (error) {
                    this.active_progress = false;
                }
            },
            login () {
                this.$refs.form_login.validate()
                this.active_progress = true
                setTimeout(() => {
                    this.active_progress = false
                }, 1000)
            }
        },
        async mounted() {
            this.regions = await request('/api/get_regions')
        }
    }
    async function request(url, method = 'GET', data = null) {
        try {
            const headers = {}
            let body

            if (data) {
                headers['Content-Type'] = "application/json"
                body = JSON.stringify(data)
            }

            const response = await fetch (url, {
                method
                , headers
                , body
            })
            return response.json()
        } catch (e) {
            console.warn('Error: ', e.message);
        }
    }
</script>
