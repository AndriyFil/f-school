<template>
    <div>
        <div class="user-content diary-bg" style="padding: 30px 40px;" data-app>
            <v-overlay :value="overlay">
                <v-progress-circular indeterminate size="64"></v-progress-circular>
            </v-overlay>
            <v-col cols="12">
                <div class="text-center" style="font-family: 'Amatic SC', sans-serif !important; font-size: 85px">Щоденник</div>
            </v-col>
            <v-card class="pa-6">
                <v-app-bar
                    color="#00ACC1"
                    src=''
                >
                    <div v-show="filter_show">
                        <div class="text-center" style="color: white; font-family: 'Noto Sans', sans-serif !important; font-size: 40px; font-weight: 500">
                            {{ myclass.class }}
                        </div>
                    </div>
                    <v-spacer></v-spacer>
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
                                    <v-img :src="schoolboy_data.photo"></v-img>
                                </v-avatar>{{ schoolboy_data.name }}
                            </v-btn>
                        </template>
                        <v-list>
                            <v-list-item
                                v-for="(sch, i) in schoolboys "
                                :key="i"
                                v-if="sch.schboy_id != selected_id"
                                @click="getFilters(sch.schboy_class_number, sch.schboy_class_letter, sch.schboy_id, sch.schboy_lastname, sch.schboy_firstname, sch.schboy_middlename, sch.schboy_photo)"
                                :disabled="filter_disable"
                            >
                                <v-list-item-icon>
                                    <v-avatar>
                                        <img
                                            :src="sch.schboy_photo"
                                            :alt="sch.schboy_lastname + ' ' + sch.schboy_firstname"
                                        >
                                    </v-avatar>
                                </v-list-item-icon>
                                <v-list-item-content>
                                    <v-list-item-title>{{sch.schboy_lastname}} {{ sch.schboy_firstname }} {{ sch.schboy_middlename }}</v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                        </v-list>
                    </v-menu>
                </v-app-bar>
                <v-row>
                    <v-col cols="12">
                        <v-row>
                            <v-col v-show="filter_show" md="3" sm="12">
                                <v-select
                                    outlined
                                    :items="types"
                                    item-text="name"
                                    item-value="id"
                                    label="Тип оцінки"
                                    v-model="type"
                                    required
                                    return-object
                                    @change="getDiary(false, true)"
                                ></v-select>
                            </v-col>
                            <v-col md="3" sm="12" class="diary-filters" v-show="filter_show">
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
                                        @input="getDiary(false, true)"
                                    ></v-date-picker>
                                </div>
                            </v-col>
                            <div class="selected-vb-class" style="position: absolute; left: -999999px" >
                                <v-select
                                    :menu-props="{closeOnClick: false, closeOnContentClick: false, disableKeys: true, openOnClick: false, maxHeight: 0}"
                                    outlined
                                    id="select_subject"
                                ></v-select>
                            </div>
                            <div class="col-12" v-show="filter_load" >
                                <div class="text-center">
                                    <v-progress-circular
                                        :size="60"
                                        :width="5"
                                        :color="main_color"
                                        indeterminate
                                        v-show="filter_load"
                                    ></v-progress-circular>
                                </div>
                            </div>
                        </v-row>
                    </v-col>

                    <!--                    Таблиця журнал-->
                    <v-col cols="12" sm="12">
                        <v-card>
                        <div class="table-responsive">
                            <table v-show = "table_show"  class="journal-table" data-app>
                                <thead>
                                <tr>
                                    <td>Предмет</td>
                                    <td v-for="item in header_rating_table" class="lesson-btn text-center" v-html="">{{item.date_formatted}}</td>
                                    <td>Перший семестр</td>
                                    <td>Другий семестр</td>
                                    <td>Річна</td>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(item, sub_id) in ratings_info.body" :key="sub_id">
                                    <td  class="title">
                                        <div class="title">
                                        {{item.name}}
                                        </div>
                                    </td>
                                    <td  class="text-center" v-for="rating in item.rating">
                                        <input type='text'
                                               v-if="r.rating != ''"
                                               v-for="(r, record) in rating"
                                               :class="[r.status, { 'input-rating':true }]"
                                               data-toggle="tooltip" data-placement="top" :title="r.rating_type_name"
                                               :value="r.rating"
                                               disabled="disabled"

                                        >
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
                        </v-card>
                        <div class="text-center">
                            <v-progress-circular
                                :size="60"
                                :width="5"
                                :color="main_color"
                                indeterminate
                                v-show="table_progress"
                            ></v-progress-circular>
                        </div>
                    </v-col>
                </v-row>
            </v-card>
        </div>
    </div>
</template>

<script>
    import * as http from "../../services/http_service";

    export default {
        name: "Diary"
        , props: ['schoolboys']
        , data: () => ({

            main_color: '#42A5F5'
            , table_progress: false
            , data_show: true
            , type: '-1'
            , today_dot: [
                {
                    dot: 'green',
                    dates: [ new Date() ],
                },
            ]
            , overlay: false
            , date: {
                start: new Date(),
                end: new Date(),
            },
            types: []
            ,myclass: {class: ''}
            ,header_rating_table: {}
            ,show_subjects: false
            ,table_show: false
            ,schoolboy_id: ''
            , ratings_info: {
                header: ''
                , body: ''
            }
            , ratings_data: {
                class: ''
                , type_rating: ''
                , schoolboy_id: ''
                , date: {}
            }
            ,filter_show: false
            ,filter_load: false
            ,filter_disable: false
            , schoolboy_data: {
                name: ''
                , id: ''
                , class_number: ''
                , class_letter: ''
                , photo: ''
                , lastname: ''
                , firstname: ''
            }
            , selected_id: ''
        })
        ,methods: {
            async getDiary (table_progress = false, overlay = false) {
                this.ratings_data.class = this.myclass.class
                this.ratings_data.type_rating = this.type
                this.ratings_data.schoolboy_id = this.schoolboy_id
                this.ratings_data.date = {start: this.formattedStart, end: this.formattedEnd}

                if(this.schoolboy_id !== '' && this.schoolboy_id !== 'undefined') {
                    this.table_progress = table_progress;
                    this.overlay = overlay;
                    this.table_show = !table_progress

                    let response = await http.request('/api/diary', 'POST', this.ratings_data)

                    //------------------------- TO DO DOTS ------------------------//
                    if(response.header == null) {
                        this.data_show = false
                    }
                    this.overlay = false
                    this.ratings_info.header = response.header;
                    this.ratings_info.body = response.body;
                    this.header_rating_table = response.dates
                    this.table_show = true
                    this.table_progress = false;
                }
            }

            , async getFilters(class_number, class_letter, id, lastname, firstname, middlename, photo) {
                let data = {
                    class: class_number
                };
                // localStorage.removeItem('class');
                // localStorage.removeItem('subject');
                // localStorage.removeItem('section');
                // localStorage.removeItem('date_start');
                // localStorage.removeItem('date_end');
                // localStorage.removeItem('sub_name');
                // localStorage.removeItem('theme_name');

                this.schoolboy_data.class_letter = class_letter
                this.schoolboy_data.name = lastname + ' ' + firstname
                this.schoolboy_data.lastname = firstname
                this.schoolboy_data.firstname = lastname
                this.schoolboy_data.class_number = class_number
                this.schoolboy_data.id = id
                this.schoolboy_data.photo = photo
                this.selected_id = id
                this.filter_show = false
                this.table_show = false
                this.filter_load = true
                this.filter_disable = true
                this.schoolboy_id = id
                this.url()
                const response = await http.request('/api/filters', 'POST', data)
                this.filter_load = false

                this.filter_disable = false
                this.filter_show = true
                this.types = response.types;
                this.types.unshift({id: '-1', name: 'Всі'})
                this.myclass.class = class_number+'-'+class_letter
                this.test = class_number+'-'+class_letter
                 this.getDiary(true)
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
            , url() {
                localStorage.setItem('lastname', this.schoolboy_data.lastname)
                localStorage.setItem('firstname', this.schoolboy_data.firstname)
                localStorage.setItem('middlename', this.schoolboy_data.middlename)
                localStorage.setItem('photo', this.schoolboy_data.photo)
                localStorage.setItem('class_number', this.schoolboy_data.class_number)
                localStorage.setItem('class_letter', this.schoolboy_data.class_letter)
                localStorage.setItem('id', this.schoolboy_data.id)
            }
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
            document.getElementById('select_subject').click();
            this.schoolboy_data.class_letter = this.schoolboys[0].schboy_class_letter
            this.schoolboy_data.name = this.schoolboys[0].schboy_lastname + ' ' + this.schoolboys[0].schboy_firstname
            this.schoolboy_data.class_number = this.schoolboys[0].schboy_class_number
            this.schoolboy_data.id = this.schoolboys[0].schboy_id
            this.schoolboy_data.photo = this.schoolboys[0].schboy_photo
            this.schoolboy_data.lastname = this.schoolboys[0].schboy_lastname
            this.schoolboy_data.firstname = this.schoolboys[0].schboy_firstname
            this.schoolboy_data.photo = this.schoolboys[0].schboy_photo
            this.selected_id = this.schoolboys[0].schboy_id

            let lastname = this.schoolboys[0].schboy_lastname;
            let firstname = this.schoolboys[0].schboy_firstname;
            console.log(this.schoolboys[0])
            if(localStorage.getItem('lastname') != '' && localStorage.getItem('lastname') != undefined) {
                this.schoolboy_data.name = localStorage.getItem('lastname')
                this.schoolboy_data.lastname = localStorage.getItem('lastname')
            }
            if(localStorage.getItem('firstname') != '' && localStorage.getItem('firstname') != undefined) {
                this.schoolboy_data.firstname = localStorage.getItem('firstname')
                this.schoolboy_data.name += ' ' + localStorage.getItem('firstname');
            }

            if(localStorage.getItem('photo') != '' && localStorage.getItem('photo') != undefined) {
                this.schoolboy_data.photo = localStorage.getItem('photo')
            }
            if(localStorage.getItem('class_number') != '' && localStorage.getItem('class_number') != undefined) {
                this.schoolboy_data.class_number = localStorage.getItem('class_number')
            }
            if(localStorage.getItem('class_letter') != '' && localStorage.getItem('class_letter') != undefined) {
                this.schoolboy_data.class_letter = localStorage.getItem('class_letter')
            }
            if(localStorage.getItem('id') != '' && localStorage.getItem('id') != undefined) {
                this.schoolboy_data.id = localStorage.getItem('id')
                this.selected_id = localStorage.getItem('id')
            }

            if(this.schoolboy_data.class_letter != '' && this.schoolboy_data.class_letter != undefined
               && this.schoolboy_data.class_number != '' && this.schoolboy_data.class_number != undefined
               && this.schoolboy_data.id != '' && this.schoolboy_data.id != undefined
            ) {
                this.overlay = true
                await this.getFilters(this.schoolboy_data.class_number, this.schoolboy_data.class_letter, this.schoolboy_data.id, this.schoolboy_data.lastname, this.schoolboy_data.firstname, '', this.schoolboy_data.photo)
                this.overlay = false
            }
        }
    }
</script>

<style scoped>

</style>
