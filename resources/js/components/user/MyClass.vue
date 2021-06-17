<template>
    <div>
        <div class="user-content diary-bg" style="padding: 30px 40px" data-app>
            <v-overlay :value="overlay">
                <v-progress-circular indeterminate size="64"></v-progress-circular>
            </v-overlay>
            <v-row>
                <v-col cols="12" class="text-center" style="font-family: 'Amatic SC', sans-serif !important; font-size: 85px">
                    Мій клас
                </v-col>
                <v-col cols="12">
                    <v-card elevation="2" width="100%" class="pa-7">
                        <v-row>
                            <v-col cols="12" class="my-4">
                                <div class="text-center" style="color: #42A5F5; box-shadow: 0 0 2px; padding: 15px; font-family: 'Noto Sans', sans-serif !important; font-size: 40px; font-weight: 500">
                                    {{ myclass.class }}
                                </div>
                            </v-col>
                            <v-col cols="12" md="6" lg="4">
                                <v-select
                                    outlined
                                    :items="subjects_class"
                                    item-text="name"
                                    item-value="id"
                                    label="Предмет"
                                    v-model="subject"
                                    name="subject"
                                    required
                                    return-object
                                    @change="getSections('')"
                                ></v-select>
                            </v-col>
                            <v-col cols="12" md="6" lg="3" class="selected-vb-class" style="position: absolute; left: -999999px" >
                                <v-select
                                    :menu-props="{closeOnClick: false, closeOnContentClick: false, disableKeys: true, openOnClick: false, maxHeight: 0}"
                                    outlined
                                    id="select_subject"
                                ></v-select>
                            </v-col>
                            <v-col cols="12" md="6" lg="4">
                                <v-select
                                    :items="sections"
                                    item-text="name"
                                    item-value="id"
                                    label="Розділ"
                                    name="section"
                                    :disabled="false"
                                    v-model="section"
                                    required
                                    return-object
                                    @change="getSchoolboys(true)"
                                ></v-select>
                            </v-col>
                            <v-col cols="12" md="6" lg="4">
                                <v-row class="justify-center">
                                    <!--                            <v-col>-->
                                    <!--                                <v-btn color="primary" class="text-white">Попередній урок</v-btn>-->
                                    <!--                            </v-col>-->
                                    <div class="col" >
                                        <div class="d-flex justify-center w-100" >
                                            <v-date-picker
                                                style="width: 100%;"
                                                type="date"
                                                :locale="{ id: 'uk-UA', firstDayOfWeek: 2, masks: { weekdays: 'WW', data: 'YYYY-MM-DD' } }"
                                                mode='range'
                                                :max-date="new Date()"
                                                v-model='date'
                                                :attributes="today_dot"
                                                class="mr-2"
                                                @input="getSections('')"
                                            ></v-date-picker>
                                        </div>
                                    </div>
                                    <!--                            <v-col class="text-right">-->
                                    <!--                                <v-btn color="primary" class="text-white">Повернутись до уроку</v-btn>-->
                                    <!--                            </v-col>-->
                                </v-row>
                            </v-col>
                        </v-row>
                    </v-card>
                </v-col>
                <!--                    Таблиця журнал-->
                <v-col cols="12" sm="12">
                    <v-card>
                        <div class="table-responsive">
                            <table v-show = "table_show"  class="journal-table" data-app>
                                <thead>
                                <tr>
                                    <td>Учень</td>
                                    <td v-for="item in header_rating_table" v-if="data_show" class="lesson-btn text-center" v-html="">{{item.date_formatted}}</td>
                                    <td>За тему</td>
                                    <td>Перший семестр</td>
                                    <td>Другий семестр</td>
                                    <td>Річна</td>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(item, sch_id) in ratings_info.body" :key="sch_id">
                                    <td class="schoolboy fixed-side" >
                                        <img :src='item.photo' class='avatar'>
                                        <div class='name'>{{item.name}}</div>
                                    </td>
                                    <td  class="text-center" v-if="data_show" v-for="rating in item.rating">
                                        <input type='text'
                                               v-for="(r, record) in rating" v-if="r.rating != ''"
                                               :class="[r.status, { 'input-rating':true }]"
                                               data-toggle="tooltip" data-placement="top" :title="r.rating_type_name"
                                               :value="r.rating"
                                               disabled="disabled"
                                        >
                                    </td>
                                    <td class="text-center">
                                        <div class="title">
                                            {{ item.rating_theme }}
                                        </div>
                                        <div class="icons">
                                            <v-icon size="15" color="success" title="Поставлено вчителем" v-if="item.rating_theme_record > 0">mdi-pocket</v-icon>
                                            <v-menu
                                                :close-on-content-click="false"
                                                :nudge-width="200"
                                                offset-x
                                                v-if="item.rating_theme_description != null"
                                            >
                                                <template v-slot:activator="{ on }">
                                                    <v-icon size="15" v-on="on"  color="blue">mdi-information</v-icon>
                                                </template>
                                                <v-card>
                                                    <v-list>
                                                        <v-list-item>
                                                            <v-list-item-title class="rating-description" v-html="item.rating_theme_description"></v-list-item-title>
                                                        </v-list-item>
                                                    </v-list>
                                                </v-card>
                                            </v-menu>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="title">
                                            {{ item.rating_first_semester }}
                                        </div>
                                        <div class="icons">
                                            <v-icon size="15" color="success" title="Поставлено вчителем" v-if="item.rating_first_semester_record > 0">mdi-pocket</v-icon>
                                            <v-menu
                                                :close-on-content-click="false"
                                                :nudge-width="200"
                                                offset-x
                                                v-if="item.rating_first_semester_description != null"
                                            >
                                                <template v-slot:activator="{ on }">
                                                    <v-icon size="15" v-on="on"  color="blue">mdi-information</v-icon>
                                                </template>
                                                <v-card>
                                                    <v-list>
                                                        <v-list-item>
                                                            <v-list-item-title class="rating-description" v-html="item.rating_first_semester_description"></v-list-item-title>
                                                        </v-list-item>
                                                    </v-list>
                                                </v-card>
                                            </v-menu>

                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="title">
                                            {{ item.rating_second_semester }}
                                        </div>
                                        <div class="icons">
                                            <v-icon size="15" color="success" title="Поставлено вчителем" v-if="item.rating_second_semester_record > 0">mdi-pocket</v-icon>
                                            <v-menu
                                                :close-on-content-click="false"
                                                :nudge-width="200"
                                                offset-x
                                                v-if="item.rating_second_semester_description != null"
                                            >
                                                <template v-slot:activator="{ on }">
                                                    <v-icon size="15" v-on="on"  color="blue">mdi-information</v-icon>
                                                </template>
                                                <v-card>
                                                    <v-list>
                                                        <v-list-item>
                                                            <v-list-item-title class="rating-description" v-html="item.rating_second_semester_description"></v-list-item-title>
                                                        </v-list-item>
                                                    </v-list>
                                                </v-card>
                                            </v-menu>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="title">
                                            {{ item.rating_year }}
                                        </div>
                                        <div class="icons">
                                            <v-icon size="15" color="success" title="Поставлено вчителем" v-if="item.rating_year_record > 0">mdi-pocket</v-icon>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center">
                            <v-progress-circular
                                :size="60"
                                :width="5"
                                :color="main_color"
                                indeterminate
                                v-show="table_progress"
                            ></v-progress-circular>
                        </div>
                    </v-card>
                </v-col>
            </v-row>
        </div>
    </div>
</template>

<script>
        import * as http from "../../services/http_service";

        export default {
            name: "MyClass"
            , props: ['subjects_prop', 'myclass', 'subjects_class']
            , data: () => ({
                messages: 2
                , rating_popup: false
                , semester_ratings_switch: false
                , main_color: '#42A5F5'
                , table_progress: false
                , data_show: true
                , subject_id: 0
                , section_id: 0
                , subject_name: ""
                , section_name: ""
                , sections: []
                , today_dot: [
                    {
                        dot: 'green',
                        dates: [ new Date() ],
                    },
                ]
                , overlay: true
                , date: {
                    start: new Date(),
                    end: new Date(),
                },
                subject: []
                , section: []
                ,classe: ''
                ,header_rating_table: {}
                ,show_subjects: false
                ,table_show: false
                ,table_control_show: false
                , ratings_info: {
                    header: ''
                    , body: ''
                }
                , ratings_data: {
                    class: ''
                    , subject: ''
                    , section: ''
                    , date: {}
                }
            })
            ,methods: {
                async getSections (subject) {
                    if(this.subject.id != undefined) {
                        this.subject_id = this.subject.id;
                    }

                    if(this.myclass.class !== '' && this.myclass.class !== undefined) {
                        if (subject !== '') {
                            this.subject_id = subject
                        }
                        if ((this.myclass.class != '' || this.myclass.class != null) && (this.subject_id != '' || this.subject_id != 0)) {
                            this.sections = await http.request('/api/sections/' + this.subject_id + '/' + this.myclass.class.charAt(0))
                            this.url();
                        }
                        this.getSchoolboys (true)
                    }
                },
                async getSchoolboys (table_progress = false) {
                    this.ratings_data.class = this.myclass.class
                    if(this.subject.id != undefined) {
                        this.subject_id = this.subject.id;
                        //this.subject_name = this.subject.name;
                    }
                    if(this.section.id != undefined) {
                        this.section_id = this.section.id;
                        //this.section_name = this.section.name;
                    }
                    this.ratings_data.subject = this.subject_id
                    this.ratings_data.section = this.section.id
                    this.ratings_data.date = {start: this.formattedStart, end: this.formattedEnd}
                    this.url()

                    if(this.myclass.class !== '' && this.myclass.class !== 'undefined' && this.subject_id !== 0 && !isNaN(this.subject_id) && this.section_id !== 0 && !isNaN(this.section_id)) {
                        this.table_progress = table_progress;
                        this.table_show = !table_progress

                        let response = await http.request('/api/journal', 'POST', this.ratings_data)

                        //------------------------- TO DO DOTS ------------------------//
                        console.log(response.dates.length)
                        if(response.dates.length == '0') {
                            this.data_show = false
                        } else {
                            this.data_show = true
                        }
                        this.ratings_info.header = response.header;
                        this.ratings_info.body = response.body;
                        this.header_rating_table = response.dates

                        this.table_show = true
                        this.table_progress = false;
                    }

                }
                , url () {

                    let date_start = this.formattedStart
                    let date_end = this.formattedEnd
                    if(this.subject.id != undefined) {
                        this.subject_id = this.subject.id;
                    }
                    if(this.subject.name != undefined) {
                        this.subject_name = this.subject.name;
                    }
                    if(this.section.id != undefined) {
                        this.section_id = this.section.id;
                    }
                    if(this.section.name != undefined) {
                        this.section_name = this.section.name;
                    }
                    console.dir(this.section.name)
                    console.dir(this.section_name)
                    this.setCookie('class', this.myclass.class);
                    this.setCookie('subject', this.subject_id);
                    this.setCookie('section', this.section_id);
                    this.setCookie('date_start', date_start);
                    this.setCookie('date_end', date_end);
                    this.setCookie('sub_name', this.subject_name);
                    this.setCookie('theme_name', this.section_name);
                    this.date_url = {start: this.formattedStart, end: this.formattedEnd}
                    // history.pushState(null, null, '/journal?class='+this.myclass.class+'&subject='+ `${this.subject_id}`+'&section='+`${this.section.id}`+'&date_start='+date_start +'&date_end='+date_end+'&sub_name='+this.subject.name+'&theme_name='+this.section.name);
                },
                formattedDate(date) {
                    let d = new Date();
                    let month_ua = [
                        'Січня'
                        , 'Лютого'
                        , 'Березня'
                        , 'Квітня'
                        , 'Травня'
                        , 'Червня'
                        , 'Липня'
                        , 'Серпня'
                        , 'Вересня'
                        , 'Жовтня'
                        , 'Лисопада'
                        , 'Грудня'
                    ]
                    if(date != '') {
                        d = new Date(date);
                    }
                    let month = d.getMonth()
                    let day = d.getDate()
                    let year = d.getFullYear()

                    return day + ' ' + month_ua[month] + ' ' + year + 'р.'
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
                , getCookie(name) {
                    let matches = document.cookie.match(new RegExp(
                        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
                    ));
                    return matches ? decodeURIComponent(matches[1]) : undefined;
                },
            },
            computed: {
                formattedStart: function(){
                    let parsed = new Date(Date.parse(this.date.start));
                    let year = parsed.getFullYear();
                    let month = ('0' + (parsed.getMonth()+1)).slice(-2); //force leading zero for month
                    let day = ('0' + parsed.getDate()).slice(-2); //force leading zero for day
                    return `${year}-${month}-${day}`;
                },
                formattedEnd: function(){
                    let parsed = new Date(Date.parse(this.date.end));
                    let year = parsed.getFullYear();
                    let month = ('0' + (parsed.getMonth()+1)).slice(-2); //force leading zero for month
                    let day = ('0' + parsed.getDate()).slice(-2); //force leading zero for day
                    return `${year}-${month}-${day}`;
                }

            },
            mounted: async function () {
                this.$nextTick(function () {
                    if(this.getCookie('date_start') != '' && this.getCookie('date_start') !== undefined && this.getCookie('date_end') != '' &&  this.getCookie('date_end') !== undefined) {
                        this.date.start = new Date(this.getCookie('date_start'))
                        this.date.end = new Date(this.getCookie('date_end'))
                    }
                })
                this.overlay = true;
                if((this.getCookie('subject')) != 'undefined' && !isNaN(this.getCookie('subject'))) {
                    this.subject = +(this.getCookie('subject'))
                    this.subject_id = +(this.getCookie('subject'))
                    this.subject_name = this.getCookie('sub_name')
                }

                // this.subject.id = +(this.$route.query.subject)
                console.log(this.getCookie('theme_name'))
                if((this.getCookie('section')) != 'undefined' && !isNaN(this.getCookie('section'))) {
                    this.section.id = +(this.getCookie('section'))
                    this.section_id = +(this.getCookie('section'))
                    this.section_name = this.getCookie('theme_name')
                }
                console.log(this.section_name)
                document.getElementById('select_subject').click();
                if (this.subject_id !== 'undefined'  && !isNaN(this.subject_id)) {
                    console.table( this.subject_id )
                    await this.getSections(this.subject_id)
                }
                if (this.myclass.class !== '' && this.myclass.class !== 'undefined' && this.subject_id !== 0 && !isNaN(this.subject_id) && this.section !== 0 && !isNaN(this.subject_id) && this.getCookie('date_start') != '' && this.getCookie('date_end') != '') {
                    this.getSchoolboys(true)
                }
                this.overlay = false;
            }
        }
</script>

<style scoped>

</style>
