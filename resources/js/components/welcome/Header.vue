<template>
    <div>
        <div class="header">
            <div class="header-items">
                <div class="block-title">
                    <div class="main-title">My School</div>
                    <div class="subtitle">сервіс, що призначений для загальноосвітніх навчальних закладів, щоб покращити якість навчання!</div>
                </div>
                <div class="block-btn">
                        <v-btn outlined large  rounded @click="register_popup = true">Зареєструвати школу</v-btn>
                    <v-btn outlined large rounded @click="login_popup = true">Увійти</v-btn>
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
                                            <v-btn color="blue darken-1" outlined rounded @click="register()">Зареєструвати</v-btn>
                                        </v-col>
                                    </v-row>
                                </v-container>

                            </v-card-text>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="blue darken-1" text @click="register_popup = false">Закрити</v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-form>
                </v-dialog>
                <v-dialog v-model="login_popup" max-width="400px">
                    <v-form ref="form1" v-model="valid" lazy-validation>
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
                                                <v-text-field label="Email" v-model="login_data.email_login" :rules="emailRules" required></v-text-field>
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
                                            <div>
                                                <v-checkbox
                                                    v-model="login_data.remember"
                                                    name="remember"
                                                    label="Запам'ятати"
                                                ></v-checkbox>
                                            </div>
                                            <v-col cols="12 text-sm-center">
                                                <v-btn color="blue darken-1" outlined rounded @click="login()">Увійти</v-btn>
                                            </v-col>
                                        </v-col>
                                    </v-row>
                                </v-container>
                            </v-card-text>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="blue darken-1" text @click="login_popup = false">Закрити</v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-form>
                </v-dialog>
            </v-row>
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

            show_pass: false,
            notification: false,

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
                email_login: '',
                password: '',
                remember: '',
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
                    await auth.register(this.register_data)
                } catch (error) {
                    switch (error.response.status) {
                        case 422:
                            this.errors = error.response.data.errors;
                            console.dir( this.errors)
                            break;
                        case 500:
                            alert(error.response.data.message)
                            break;
                        default:
                            alert('Some error')
                    }
                }
            },
            async login () {
                try {
                    const response = await auth.login(this.login_data)
                    location.reload();
                } catch (error) {
                    switch (error.response.status) {
                        case 422:
                            this.errors = error.response.data.errors;
                            console.dir( this.errors)
                            break;
                        case 500:
                            alert(error.response.data.message)
                            break;
                        default:
                            alert('Some error')
                    }
                }
            }
            // , async schoolRegistration () {
            //     this.cities = await request('/api/get_cities/'+this.region)
            // }
            // , async register() {
            //     this.validate ()
            //     let status = await request('/api/register/', 'POST', {
            //         school_name:this.school_name
            //         , email:this.email
            //         , firstname:this.firstname
            //         , lastname:this.lastname
            //         , middlename:this.middlename
            //         , city:this.city
            //         , region:this.region
            //         , phone_number:this.phone_number
            //     })
            //     if (status) {
            //         this.register_popup = false;
            //         this.login_popup = true;
            //         this.email_login = this.email;
            //         this.notification = true;
            //     } else {
            //         alert("Помилка реєстрації")
            //     }
            // }
            // , async login() {
            //     //this.validate ()
            //     let status = await request('/api/login/', 'POST', {
            //         email:this.email_login
            //         , password:this.password
            //     })
            //     console.dir(status)
            //     // if (status.error) {
            //     //     this.status_login = false
            //     // }
            // }
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
