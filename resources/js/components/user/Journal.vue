<template>
    <div>
        <div class="user-content" style="padding: 30px 40px" data-app>
                <v-overlay :value="overlay">
                    <v-progress-circular indeterminate size="64"></v-progress-circular>
                </v-overlay>
                <v-row>
                    <v-col cols="12" sm="3">
                        <v-select
                            outlined
                            :items="subjects_classes.classes"
                            item-text="class"
                            item-value="class"
                            label="Класс"
                            :value="classe"
                            v-model="classe"
                            name="class"
                            required
                            @change="getSections('')"
                        ></v-select>
                    </v-col>
                    <v-col cols="12" sm="3">
                        <v-select
                            outlined
                            :items="subjects_classes.subjects"
                            item-text="name"
                            item-value="id"
                            label="Предмет"
                            v-model="subject"
                            name="subject"
                            required
                            @change="getSections('')"
                        ></v-select>
                    </v-col>
                    <div class="col">
                        <v-autocomplete
                            :items="sections"
                            item-text="name"
                            item-value="id"
                            label="Розділ"
                            name="section"
                            :disabled="false"
                            v-model="section"
                            required

                        ></v-autocomplete>
                    </div>
                        <div class="col-auto">
                        <v-row class="justify-center">
<!--                            <v-col>-->
<!--                                <v-btn color="primary" class="text-white">Попередній урок</v-btn>-->
<!--                            </v-col>-->
                            <div class="col-auto" style="width: 300px">
                                <div class="d-flex flex-nowrap" >
                                    <v-date-picker

                                       type="date"
                                       :locale="{ id: 'uk-UA', firstDayOfWeek: 2, masks: { weekdays: 'WW', data: 'YYYY-MM-DD' } }"
                                        mode='range'
                                        v-model='date'
                                    ></v-date-picker>
                                    <v-btn color="primary" @click="getSchoolboys"><v-icon>mdi-file-document-box-search-outline</v-icon></v-btn>
                                </div>
                            </div>
<!--                            <v-col class="text-right">-->
<!--                                <v-btn color="primary" class="text-white">Повернутись до уроку</v-btn>-->
<!--                            </v-col>-->
                        </v-row>
                    </div>

                    <v-col cols="12" sm="12">
                        <table v-show = "table_show"  class="journal-table" data-app>
                            <thead>
                                <tr>
                                    <td v-for="item in header_rating_table" @click=" showLesson(item) ">{{item}}</td>
                                </tr>
                            </thead>
                            <tbody>
                                    <tr v-for="(item, sch_id) in ratings_info.body" :key="sch_id">
                                        <td class="schoolboy">
                                            <img :src='item.photo' class='avatar'>
                                            <div class='name'>{{item.name}}</div>
                                        </td>
                                        <td v-for="rating in item.rating">
                                            <input type='text' v-for="(r, record) in rating" v-if="r.rating != ''" @click="popup_journal = true" :class="[r.status, { 'input-rating':true }]" :value='r.rating'>
                                        </td>
                                        <td>
                                            <input type='text' class='input-rating' :value='item.rating_theme'>
                                        </td>
                                        <td>
                                            <input type='text' class='input-rating' :value='item.rating_semester'>
                                        </td>
                                    </tr>
                            </tbody>
                        </table>
                    </v-col>
                </v-row>

        </div>
        <input type="text" name="name" :old="'name'">
        <v-dialog v-model="popup_journal" fullscreen hide-overlay transition="dialog-bottom-transition">
            <v-card>
                <v-toolbar dark color="primary">
<!--                    <v-toolbar-title>{{subject_name}}. {{theme_name}}. {{classe}} {{ date_lesson }}</v-toolbar-title>-->
                    <v-spacer></v-spacer>
                    <v-btn icon dark @click="popup_journal = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-toolbar>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
        import * as journal from '../../services/journal_service.js'
        import * as auth from "../../services/auth_service";
        import * as http from "../../services/http_service";
        var show_journal_popup = false;
        export default {
                name: "Journal"
            , props: ['old']
            , data: () => ({
                        messages: 2,
                        rating_popup: false
                        , rating_types: [
                            {
                                id: 1
                                , name: 'Домашнє завдання'
                            }
                            , {
                                id: 2
                                , name: 'Усна відповідь'
                            }
                            , {
                                id: 3
                                , name: 'Письмова робота'
                            }
                            , {
                                id: 4
                                , name: 'Контрольна робота'
                            }
                        ]
                        , sections: []
                        , test: ''

                        , school_id: ''
                        , overlay: true
                        , popup_journal: false
                        , date: {
                            start: new Date(),
                            end: new Date(),
                        }
                        , cityRules: [
                            v => !!v || "Тип обов'язковий",
                        ],
                        schoolboy_info: {
                            photo: ""
                            , name: ""
                        }
                        , index: 0
                        , rating_info: {
                            rating: '',
                            rating_type: 0,
                        },
                        classes: [],
                        subjects: [],
                        headers: [
                            { text: 'Фото' },
                            { text: 'Ім\'я' },
                            { text: 'Оцінка (остання)' },
                            { text: 'Оцінка за тему' },
                            { text: 'Оцінка за семестр' },
                        ],
                        show: false,
                        drawer: false
                        , subject: {
                            id: 0
                        }
                        , section: 0
                        ,classe: ''
                        ,subjects_classes: ''
                        ,header_rating_table: {}
                        ,show_subjects: false
                        ,table_show: false
                        , ratings_info: {
                            header: ''
                            , body: ''
                        }
                        , ratings_data: {
                                class: ''
                                , subject: ''
                                , school_id: ''
                                , section: ''
                                , date: {}
                        }
                        , lesson_data: {
                                class: ''
                                , subject: ''
                                , school_id: ''
                                , section: ''
                                , date: ''
                        }
                        , date_url: {}
                }),
                methods: {
                    async getSections (subject) {
                        if(this.classe != '') {
                            if (subject != '') {
                                this.subject = subject
                            }
                            if (this.classe.charAt(0) != '' && (this.subject != '' || this.subject != 0)) {
                                this.sections = await http.request('/api/sections/' + this.subject + '/' + this.classe.charAt(0) + '/' + this.$store.state.profile.school_id)
                                this.url();
                            }
                        }
                    },
                    async getSchoolboys () {
                            this.ratings_data.class = this.classe
                            this.ratings_data.subject = this.subject
                            this.ratings_data.school_id = this. $store.state.profile.school_id
                            this.ratings_data.section = this.section
                            this.ratings_data.date = {start: this.formattedStart, end: this.formattedEnd}
                            this.url()
                        console.log(this.ratings_data)
                            if(this.classe !== '' && this.classe !== 'undefined' && this.subject !== 0 && !isNaN(this.subject) && this.section !== 0 && !isNaN(this.section)) {
                                this.overlay = true;
                                let response = await http.request('/api/schoolboys/', 'POST', this.ratings_data)
                                console.log(response.body)
                                this.ratings_info.header = response.header;
                                this.ratings_info.body = response.body;
                                this.header_rating_table = ['Учень']
                                let dates = response.dates
                                this.header_rating_table = this.header_rating_table.concat(dates)
                                this.header_rating_table.push('За тему')
                                this.header_rating_table.push('За семестр')
                                console.log(this.header_rating_table)
                                this.table_show = true
                                this.overlay = false;
                            } else {
                                alert ('Всі поля мають бути заповнені')
                            }
                    },
                    showLesson (date) {
                        this.lesson_data.class = this.classe
                        this.lesson_data.subject = this.subject
                        this.lesson_data.school_id = this. $store.state.profile.school_id
                        this.lesson_data.section = this.section
                        this.lesson_data.date = this.section
                        let response = http.request('/api/lesson/', 'POST', this.lesson_data)
                        this.popup_journal = true
                    },
                    async getSubjects () {
                        this.show_subjects = true
                    },
                    url () {

                        let date_start = this.formattedStart
                        let date_end = this.formattedEnd
                        this.date_url = {start: this.formattedStart, end: this.formattedEnd}
                        history.pushState(null, null, '/journal?class='+this.classe+'&subject='+this.subject+'&section='+this.section+'&date_start='+date_start +'&date_end='+date_end);
                    },

                },
                beforeCreate: async function() {
                    const response = await auth.getProfile()
                    this.$store.dispatch('authenticate', response.data)
                    this.subjects_classes =  await http.request('/api/subjects_classes/'+this.$store.state.profile.id+'/'+this.$store.state.profile.school_id+'/'+this.$store.state.profile.role)
                    // await this.getSections
                    this.overlay = false;
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
                        this.date.start = new Date(this.$route.query.date_start)
                        this.date.end = new Date(this.$route.query.date_end)
                    })
                    const response = await auth.getProfile()
                    this.classe = this.$route.query.class
                    this.subject.id = +(this.$route.query.subject)
                    this.section = +(this.$route.query.section)
                    this.getSections(this.subject.id)
                    if (this.classe !== '' && this.classe !== 'undefined' && this.subject !== 0 && !isNaN(this.subject) && this.section !== 0 && !isNaN(this.section)) {
                        this.getSchoolboys()
                    }
                }
        }
</script>

<style scoped>

</style>
