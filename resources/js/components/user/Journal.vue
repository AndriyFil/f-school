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
                            @change="getSchoolboys(true)"
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
                                   <v-icon :color="main_color" size="40" @click="getSchoolboys">mdi-file-document-box-search-outline</v-icon>
                                </div>
                            </div>
<!--                            <v-col class="text-right">-->
<!--                                <v-btn color="primary" class="text-white">Повернутись до уроку</v-btn>-->
<!--                            </v-col>-->
                        </v-row>
                    </div>
                    <v-col cols="12" sm="12">
                        <div class="text-right">
                            <v-btn color="#00C853" class="white--text"
                                   to="/journal/lesson"
                                   large>
                                <span class="title">До уроку</span> <v-icon size="20">mdi-bell</v-icon>
                            </v-btn>
                        </div>
                    </v-col>
                    <v-col cols="12" sm="12">
                        <div class="table-responsive">
                            <table v-show = "table_show"  class="journal-table" data-app :elevation="5">
                                <thead>
                                    <tr>
                                        <td>Учень</td>
                                        <td v-for="item in header_rating_table" @click=" showLesson(item) " class="lesson-btn" >{{item}}</td>
                                        <td>За тему</td>
                                        <td>За семестр</td>
                                    </tr>
                                </thead>
                                <tbody>
                                        <tr v-for="(item, sch_id) in ratings_info.body" :key="sch_id">
                                            <td class="schoolboy fixed-side" >
                                                <img :src='item.photo' class='avatar'>
                                                <div class='name'>{{item.name}}</div>
                                            </td>
                                            <td  class="text-center" v-for="rating in item.rating">
                                                <input type='text'
                                                       v-for="(r, record) in rating" v-if="r.rating != ''"
                                                       @change="setRating($event.target.value, r.rating_type_id, sch_id, record)"
                                                       :class="[r.status, { 'input-rating':true }]"
                                                       data-toggle="tooltip" data-placement="top" :title="r.rating_type_name"
                                                       :value="r.rating"
                                                       @input="ratingValue($event.target)">
                                            </td>
                                            <td class="text-center">
                                                <input type='text' class='input-rating' :value='item.rating_theme'>
                                            </td>
                                            <td class="text-center">
                                                <input type='text' class='input-rating' :value='item.rating_semester'>
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
                    </v-col>
                </v-row>

        </div>
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
        <v-snackbar
            v-model="snackbar.status"
            :color="snackbar.color"
            :multi-line="true"
            :timeout="3000"
            :top="true"
        >
            {{ snackbar.text }}
        </v-snackbar>
    </div>
</template>

<script>
        import * as journal from '../../services/journal_service.js'
        import * as auth from "../../services/auth_service";
        import * as http from "../../services/http_service";
        var show_journal_popup = false;
        export default {
            name: "Journal"
            , data: () => ({
                messages: 2
                , rating_popup: false
                , main_color: '#42A5F5'
                , table_progress: false
                , sections: []
                , mda: []
                , snackbar: {
                    color: ''
                    , status: ''
                    , text: ''
                }
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
                , journal_data: {
                    rating: ''
                    , rating_type: ''
                    , teacher: ''
                    , subject: ''
                    , schoolboy: ''
                    , school_id: ''
                    , theme: ''
                    , record: ''
                    , class_number: ''
                }
                , date_url: {}
                , input_rating: ''
                , rating_color: ''
            })
            ,methods: {
                async getSections (subject) {
                    if(this.classe !== '' && this.classe !== undefined) {
                        if (subject !== '') {
                            this.subject = subject
                        }
                        if (this.classe.charAt(0) != '' && (this.subject != '' || this.subject != 0)) {
                            this.sections = await http.request('/api/sections/' + this.subject + '/' + this.classe.charAt(0) + '/' + this.$store.state.profile.school_id)
                            this.url();
                        }
                        this.getSchoolboys (true)
                    }
                },
                async getSchoolboys (table_progress = false) {
                        this.ratings_data.class = this.classe
                        this.ratings_data.subject = this.subject
                        this.ratings_data.school_id = this. $store.state.profile.school_id
                        this.ratings_data.section = this.section
                        this.ratings_data.date = {start: this.formattedStart, end: this.formattedEnd}
                        this.url()
                    console.log(this.ratings_data)
                        if(this.classe !== '' && this.classe !== 'undefined' && this.subject !== 0 && !isNaN(this.subject) && this.section !== 0 && !isNaN(this.section)) {
                            this.table_progress = table_progress;
                            this.table_show = !table_progress
                            let response = await http.request('/api/schoolboys/', 'POST', this.ratings_data)
                            console.log(response.body)
                            this.ratings_info.header = response.header;
                            this.ratings_info.body = response.body;
                            this.header_rating_table = response.dates
                            console.log(this.header_rating_table)
                            this.table_show = true
                            this.table_progress = false;
                        } else {
                            alert ('Всі поля мають бути заповнені')
                        }
                },
                ratingValue (el) {
                        console.log(el.value)
                        el.classList.remove('success-color', 'warning-color', 'danger-color')
                        if(el.value >= 10) {
                            el.classList.add('success-color')
                        } else if(el.value < 10 && el.value >= 7) {
                            el.classList.add('warning-color')
                        } else {
                            el.classList.add('danger-color')
                        }
                }
                , showLesson (date) {
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
                async setRating (rating, rating_type, schoolboy, record) {

                    this.journal_data.rating = rating
                    this.journal_data.rating_type = rating_type
                    this.journal_data.teacher = this.$store.state.profile.id
                    this.journal_data.subject = this.subject
                    this.journal_data.schoolboy = schoolboy
                    this.journal_data.school_id = this.$store.state.profile.school_id
                    this.journal_data.theme = this.section
                    this.journal_data.record = record
                    this.journal_data.class_number = this.classe.charAt(0)
                    console.log(this.journal_data)
                    let response = await http.request('/api/set_rating', 'POST', this.journal_data)
                    if(response.status) {
                        // this.snackbar.status = true
                        // this.snackbar.text = 'Оцінку збережено'
                        // this.snackbar.color = 'success'
                        this.getSchoolboys()
                    } else {
                        this.snackbar.status = true
                        this.snackbar.text = 'Виникла помилка при збереженні'
                        this.snackbar.color = 'danger'
                    }
                }
            },
            beforeCreate: async function() {

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
                    console.log(this.date.start)
                    console.log(this.date.end)
                    // this.date.start = this.formattedStart
                    // this.date.end = this.formattedEnd
                    // console.log(this.date.start)
                    // console.log(this.date.end)
                    console.dir(this.date.start)
                    if(this.$route.query.date_start != '' && this.$route.query.date_start !== undefined && this.$route.query.date_end != '' &&  this.$route.query.date_end !== undefined) {
                        this.date.start = new Date(this.$route.query.date_start)
                        this.date.end = new Date(this.$route.query.date_end)
                    }
                })
                // console.log(this.date.start)
                // console.log(this.date.end)
                this.overlay = true;
                const response = await auth.getProfile()
                this.$store.dispatch('authenticate', response.data)
                this.subjects_classes =  await http.request('/api/subjects_classes/'+this.$store.state.profile.id+'/'+this.$store.state.profile.school_id+'/'+this.$store.state.profile.role)
                // await this.getSections

                console.log(response)
                this.classe = this.$route.query.class
                this.subject.id = +(this.$route.query.subject)
                this.section = +(this.$route.query.section)
                await this.getSections(this.subject.id)
                if (this.classe !== '' && this.classe !== 'undefined' && this.subject !== 0 && !isNaN(this.subject) && this.section !== 0 && !isNaN(this.section) && this.$route.query.date_start != '' && this.$route.query.date_end != '') {
                    this.getSchoolboys(true)
                }
                this.overlay = false;
            }
        }
</script>

<style scoped>

</style>
