<template>
    <div>
        <div class="user-content diary-bg" style="padding: 30px 40px" data-app>
                <v-overlay :value="overlay">
                    <v-progress-circular indeterminate size="64"></v-progress-circular>
                </v-overlay>

                <v-row>
                    <v-col cols="12" class="text-center" style="font-family: 'Amatic SC', sans-serif !important; font-size: 85px">
                        Журнал
                    </v-col>
                    <v-col cols="12">
                        <v-card elevation="2" width="100%" class="pa-7">
                            <v-row>
                                <v-col cols="12" md="6" lg="3">
                                    <v-select
                                        outlined
                                        :items="classes_prop"
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
                                <v-col cols="12" md="6" lg="3">
                                    <v-select
                                        outlined
                                        :items="subjects_prop"
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
                                <v-col cols="12" md="6" lg="3">
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
                                <v-col cols="12" md="6" lg="3">
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
                    <v-col cols="12" sm="12" v-show="block_btn">
                        <div class="btn-block">
                            <div class="btn-wrap">
                                <v-btn color="warning" class="mr-3"
                                       @click="showControl(new Date())"
                                       :loading="loading_control"
                                       dark
                                       large>
                                    <span class="title">Підсумки + к.р. </span><v-icon size="20" right>mdi-pocket</v-icon>
                                </v-btn>
                            </div>
                            <div class="btn-wrap">
                                <v-btn color="success" class="white--text"
                                       @click="showLesson('')"
                                       :loading="loading_lesson"
                                       large>
                                    <span class="title">До уроку</span><v-icon size="20" right>mdi-bell</v-icon>
                                </v-btn>
                            </div>
                        </div>
                    </v-col>

<!--                    Таблиця журнал-->
                    <v-col cols="12" sm="12">
                        <v-card>
                            <div class="table-responsive">
                                <table v-show = "table_show"  class="journal-table" data-app>
                                    <thead>
                                        <tr>
                                            <td>Учень</td>
                                            <td v-for="item in header_rating_table" @click=" showLesson(item.date) " class="lesson-btn text-center" v-html="">{{item.date_formatted}}</td>
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
                                            <td  class="text-center" v-for="rating in item.rating">
                                                <input type='text'
                                                       v-for="(r, record) in rating" v-if="r.rating != ''"
                                                       @change="setRating($event.target.value, r.rating_type_id, sch_id, record, false, true, false)"
                                                       :class="[r.status, { 'input-rating':true }]"
                                                       data-toggle="tooltip" data-placement="top" :title="r.rating_type_name"
                                                       :value="r.rating"
                                                       @input="ratingValue($event.target)"
                                                       maxlength="2"
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

        <!--                    Таблиця уроку-->
        <v-dialog v-model="popup_lesson"  fullscreen hide-overlay transition="dialog-bottom-transition">
            <v-card class="px-3">
                <div class="lesson diary-bg">
                    <div class="header-wrap">
                        <div class="item">
                            <div class="text-wrap">
                                <div class="_subtitle">
                                    {{ lesson_info.date }}
                                </div>
                                <div class="_subtitle">
                                    {{ lesson_info.class_name }}
                                </div>
                            </div>
                            <div class="close">
                                <v-btn large icon dark @click="getSchoolboys(true)">
                                    <v-icon>mdi-close</v-icon>
                                </v-btn>
                            </div>
                        </div>
                        <div class="text-center _title">
                            {{ lesson_info.subject_name }}. {{ lesson_info.theme_name }}
                        </div>
                    </div>
                    <div class="content-wrap mx-auto" style="max-width: 1400px">
                        <div class=" d-flex flex-wrap justify-space-between">
                            <v-btn color="warning" class="m-1"
                                   @click="showControl(new Date(), true)"
                                   :loading="loading_control"
                                   dark
                                   large>
                                <span class="title">Підсумки + к.р. </span><v-icon size="20" right>mdi-pocket</v-icon>
                            </v-btn>
                            <div class="new-column-block m-1">
                                <div class="option">
                                    <v-select label="Тип оцінки"
                                        :items="lesson_new_rating_types"
                                        item-text="name"
                                        item-value="id"
                                        solo
                                        v-model="new_column"
                                    >

                                    </v-select>
                                </div>
                                <div class="btn-block">

                                    <v-tooltip bottom>
                                        <template v-slot:activator="{ on }">
                                            <v-btn icon v-on="on" @click="addColumnType"><v-icon size="24" color="#43A047">mdi-plus-thick</v-icon></v-btn>
                                        </template>
                                        <span>Добавити колонку</span>
                                    </v-tooltip>
                                </div>
                            </div>
                        </div>

                        <div class="table-block">
                            <v-card>
                                <div class="table-responsive">
                                    <table class="journal-table" data-app>
                                        <thead>
                                        <tr>
                                            <td>Учень</td>
                                            <td v-for="(item, key) in lesson_schoolboy_info_header" v-if="lesson_show_column[`type_${key}`]" :key="key">{{ item.name }}</td>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(item, sch_id) in lesson_schoolboy_info_body" :key="sch_id">
                                            <td class="schoolboy fixed-side" >
                                                <img :src='item.photo' class='avatar'>
                                                <div class='name'>{{item.name}}</div>
                                            </td>
                                            <td  class="text-center" v-for="(r, rating_type) in item.rating" v-if="lesson_show_column[`type_${rating_type}`]">
                                                <input type='text'
                                                       class="input-rating"
                                                       @change="setRating($event.target.value, rating_type, sch_id, r.record, true, false, false)"
                                                       :value="r.rating"
                                                       @input="ratingValue($event.target)"
                                                       maxlength="2"
                                                      >
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
                        </div>

                    </div>
                </div>
            </v-card>
        </v-dialog>

        <!--                    Таблиця підсумок-->
        <v-dialog v-model="popup_control"  fullscreen hide-overlay transition="dialog-bottom-transition">
            <v-card class="px-3">
                <div class="lesson control diary-bg">
                    <div class="header-wrap control">
                        <div class="item">
                            <div class="text-wrap">
                                <div class="_subtitle">
                                    {{ lesson_info.date }}
                                </div>
                                <div class="_subtitle">
                                    {{ lesson_info.class_name }}
                                </div>
                            </div>
                            <div class="close">
                                <v-btn large icon dark @click="getSchoolboys(true, false)">
                                    <v-icon>mdi-close</v-icon>
                                </v-btn>
                            </div>
                        </div>
                        <div class="text-center _title">
                            {{ lesson_info.subject_name }}. {{ lesson_info.theme_name }}
                        </div>
                    </div>
                    <div class="content-wrap mx-auto"  style="max-width: 1000px">
                        <div class="d-flex justify-space-between">
                            <div>
                                <v-switch v-model="semester_ratings_switch" color="#00ACC1" label="Семестрові та річна"></v-switch>
                            </div>
                            <div class="d-flex align-center">
                                <v-btn color="success" class="white--text"
                                       @click="showLesson('')"
                                       :loading="loading_lesson"
                                       large>
                                    <span class="title">До уроку</span><v-icon size="20" right>mdi-bell</v-icon>
                                </v-btn>
                            </div>
                        </div>
                        <div class="table-block control">
                            <v-card>
                                <div class="table-responsive">
                                    <table v-show = "table_control_show" :loading="true" class="journal-table" data-app>
                                        <thead>
                                        <tr>
                                            <td class="control">Учень</td>
                                            <td class="control">Контрольна</td>
                                            <td class="control">За тему</td>
                                            <td class="control" v-if="semester_ratings_switch">Перший семестр</td>
                                            <td class="control" v-if="semester_ratings_switch">Другий семестр</td>
                                            <td class="control" v-if="semester_ratings_switch">Річна</td>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(item, sch_id) in ratings_info.body" :key="sch_id">
                                            <td class="schoolboy fixed-side" >
                                                <img :src='item.photo' class='avatar'>
                                                <div class='name'>{{item.name}}</div>
                                            </td>
                                            <td class="text-center">
                                                <input type='text' class='input-rating teal-color' :value='item.rating_control'
                                                       @change="setRating($event.target.value, '4', sch_id, item.rating_control_record, false, true, true)"
                                                       @input="ratingValue($event.target)"
                                                       maxlength="2">
                                            </td>
                                            <td class="text-center">
                                                <input type='text' class='input-rating teal-color' :value='item.rating_theme'
                                                       @change="setRating($event.target.value, '3', sch_id, item.rating_theme_record, false, true, true)"
                                                       @input="ratingValue($event.target)"
                                                       maxlength="2">
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
                                            <td class="text-center" v-if="semester_ratings_switch">
                                                <input type='text' class='input-rating teal-color' :value='item.rating_first_semester'
                                                       @change="setRating($event.target.value, '6', sch_id, item.rating_first_semester_record, false, true, true)"
                                                       @input="ratingValue($event.target)"
                                                       maxlength="2">
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
                                            <td class="text-center" v-if="semester_ratings_switch">
                                                <input type='text' class='input-rating teal-color' :value='item.rating_second_semester'
                                                       @change="setRating($event.target.value, '7', sch_id, item.rating_second_semester_record, false, true, true)"
                                                       @input="ratingValue($event.target)"
                                                       maxlength="2">
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
                                            <td class="text-center " v-if="semester_ratings_switch">
                                                <input type='text' class='input-rating teal-color' :value='item.rating_year'
                                                       @change="setRating($event.target.value, '8', sch_id, item.rating_year_record, false, true, true)"
                                                       @input="ratingValue($event.target)"
                                                       maxlength="2">
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
                        </div>

                    </div>
                </div>
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
            ,props: ['subjects_prop', 'classes_prop']
            , data: () => ({
                messages: 2
                , rating_popup: false
                , semester_ratings_switch: false
                , main_color: '#42A5F5'
                , table_progress: false
                , block_btn: false
                , sections: []
                , mda: []
                , snackbar: {
                    color: ''
                    , status: ''
                    , text: ''
                }
                , today_dot: [
                    {
                        dot: 'green',
                        dates: [ new Date() ],
                    },
                ]
                , test: ''
                , overlay: true
                , popup_lesson: false
                , popup_summary: false
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
                show: false,
                drawer: false
                , subject: [
                    {
                        id: 0
                        , name: ''
                    }
                ]
                , section: [
                    {
                        id: 0
                        , name: ''
                    }
                ]
                ,classe: ''
                ,subjects_classes: ''
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
                , lesson_data: {
                        class: ''
                        , subject: ''
                        , section: ''
                        , date: ''
                }
                , journal_data: {
                    rating: ''
                    , rating_type: ''
                    , teacher: ''
                    , subject: ''
                    , schoolboy: ''
                    , theme: ''
                    , record: ''
                    , class_number: ''
                    , date: ''
                }
                , lesson_info: {
                    class_name: ''
                    , date: ''
                    , subject_name: ''
                    , theme_name: ''
                    , theme: ''
                    , subject: ''
                    , date_post: ''
                }
                , date_url: {}
                , input_rating: ''
                , rating_color: ''
                , new_column: ''
                , loading_lesson: false
                , loading_control: false
                , popup_control: false
                , lesson_schoolboy_info_body: []
                , lesson_new_rating_types: ''
                , lesson_schoolboy_info_header: []
                , lesson_show_column: []
                , subject_id: 0
                , section_id: 0
                , subject_name: ""
                , section_name: ""
            })
            ,methods: {
                async getSections (subject) {
                    if(this.subject.id != undefined) {
                        this.subject_id = this.subject.id;
                    }
                    if(this.classe !== '' && this.classe !== undefined) {
                        if (subject !== '') {
                            this.subject_id = subject
                        }
                        if ((this.classe != '' || this.classe != null) && (this.subject_id != '' || this.subject_id != 0)) {
                            this.sections = await http.request('/api/sections/' + this.subject_id + '/' + this.classe.charAt(0))
                            this.url();
                        }
                        this.getSchoolboys (true)
                    }
                },
                async getSchoolboys (table_progress = false, close_lesson_bool = false, close_control_bool = false) {
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

                    this.ratings_data.class = this.classe
                    this.ratings_data.subject = this.subject_id
                    this.ratings_data.section = this.section_id
                    this.ratings_data.date = {start: this.formattedStart, end: this.formattedEnd}
                    this.url()
                    this.popup_lesson = close_lesson_bool
                    this.popup_control = close_control_bool

                    if(this.classe !== '' && this.classe !== 'undefined' && this.subject_id !== 0 && !isNaN(this.subject_id) && this.section_id !== 0 && !isNaN(this.section_id)) {
                        this.table_progress = table_progress;
                        this.table_show = !table_progress
                        this.table_control_show = !table_progress

                        let response = await http.request('/api/schoolboys', 'POST', this.ratings_data)

                        this.block_btn = true
                        //------------------------- TO DO DOTS ------------------------//

                        this.ratings_info.header = response.header;
                        this.ratings_info.body = response.body;
                        this.header_rating_table = response.dates

                        this.table_show = true
                        this.table_progress = false;
                    }

                },
                async getControlData (table_progress = false, close_lesson_bool = false) {
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
                        this.ratings_data.class = this.classe
                        this.ratings_data.subject = this.subject_id
                        this.ratings_data.section = this.section_id
                        this.ratings_data.date = {start: this.formattedStart, end: this.formattedEnd}
                        this.url()
                        if(this.classe !== '' && this.classe !== 'undefined' && this.subject_id !== 0 && !isNaN(this.subject_id) && this.section_id !== 0 && !isNaN(this.section_id)) {
                            this.table_progress = table_progress;
                            this.table_control_show = !table_progress

                            let response = await http.request('/api/schoolboys', 'POST', this.ratings_data)

                            //------------------------- TO DO DOTS ------------------------//

                            this.ratings_info.header = response.header;
                            this.ratings_info.body = response.body;
                            this.header_rating_table = response.dates

                            this.table_show = true
                            this.table_control_show = true
                            this.table_progress = false;
                        }

                },
                ratingValue (el) {
                    console.log(el.value)
                    let chek = el.value.match(/(?:^[1][0-2]{0,1}$)|(?:^[2-9]$)/);
                    console.dir(chek)
                    if (!chek || chek == null) {
                        if(el.value > 12) {
                            el.value = '12'
                        } else {
                            el.value = '1'
                        }
                        el.value = el.value.replace(/\D+/g, "");
                    }
                }
                , async showLesson (date) {
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
                    this.lesson_info.class_name = this.classe
                    this.lesson_info.subject_name = this.subject_name
                    this.lesson_info.theme_name = this.section_name
                    this.lesson_info.date = this.formattedDate(date)
                    this.lesson_info.theme = this.section_id
                    this.lesson_info.subject = this.subject_id
                    if(date == '') {
                        date = new Date();
                    }
                    this.lesson_info.date_post = date
                    await this.lessonData()
                }
                , async showControl (date, lesson_popup = false) {
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
                    this.lesson_info.class_name = this.classe
                    this.lesson_info.subject_name = this.subject_name
                    this.lesson_info.theme_name = this.section_name
                    this.lesson_info.date = this.formattedDate(date)
                    this.lesson_info.theme = this.section_id
                    this.lesson_info.subject = this.subject_id
                    if(date == '') {
                        date = new Date();
                    }
                    this.lesson_info.date_post = date
                    this.loading_control = true
                    this.popup_lesson = lesson_popup
                    await this.getSchoolboys(false, lesson_popup)
                    this.loading_control = false
                    this.popup_control = true
                    this.popup_lesson = false
                },
                 async lessonData() {
                     this.loading_lesson = true
                     let response = await http.request('/api/get_lesson', 'POST', this.lesson_info)
                     console.log(response)
                     if(response.status) {
                         this.lesson_schoolboy_info_header = response.header
                         this.lesson_schoolboy_info_body = response.schoolboy_info
                         this.lesson_new_rating_types = response.add_rating_type
                         this.lesson_new_rating_types.forEach((item, index) => {
                             this.$set(this.lesson_show_column, `type_${item.id}`, item.status)
                         })
                         this.lesson_new_rating_types = response.options
                         this.popup_lesson = true
                     }
                     this.loading_lesson = false
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
                    this.setCookie('class', this.classe);
                    this.setCookie('subject', this.subject_id);
                    this.setCookie('section', this.section_id);
                    this.setCookie('date_start', date_start);
                    this.setCookie('date_end', date_end);
                    this.setCookie('sub_name', this.subject_name);
                    this.setCookie('theme_name', this.section_name);
                    this.date_url = {start: this.formattedStart, end: this.formattedEnd}
                    console.dir(this.subject.id)
                    console.dir(this.subject)
                    // history.pushState(null, null, '/journal?class='+this.classe+'&subject='+ `${this.subject.id}`+'&section='+`${this.section.id}`+'&date_start='+date_start +'&date_end='+date_end+'&sub_name='+this.subject.name+'&theme_name='+this.section.name);
                },
                addColumnType () {
                    let column = `type_${this.new_column}`
                    this.lesson_show_column[column] = true
                },
                async setRating (rating, rating_type, schoolboy, record, refresh_lesson, refresh_journal, control_rating) {
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
                    this.journal_data.rating = rating
                    this.journal_data.rating_type = rating_type
                    this.journal_data.subject = this.subject_id
                    this.journal_data.schoolboy = schoolboy
                    this.journal_data.theme = this.section_id
                    this.journal_data.record = record
                    this.journal_data.class_number = this.classe.charAt(0)
                    this.journal_data.date = this.lesson_info.date_post
                    console.log(this.journal_data)
                    let response = await http.request('/api/set_rating', 'POST', this.journal_data)
                    if(response.status) {
                        if(control_rating) {
                            await this.getControlData();
                        } else if(refresh_lesson) {
                            console.log('good')
                        } else {
                            await this.getSchoolboys(true, false);
                        }

                        // this.snackbar.status = true
                        // this.snackbar.text = 'Оцінку збережено'
                        // this.snackbar.color = 'success'

                    } else {
                        this.snackbar.status = true
                        this.snackbar.text = 'Виникла помилка при збереженні'
                        this.snackbar.color = 'error'
                    }
                }, formattedDate(date) {
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
                }, setCookie(name, value, options = {}) {

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
                },
                getCookie(name) {
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
                // this.subjects_classes = await http.request('/api/subjects_classes')
                console.log(this.$route.query.class)

                this.classe = this.getCookie('class')

                if((this.getCookie('subject')) != 'undefined' && !isNaN(this.getCookie('subject'))) {
                    this.subject = +(this.getCookie('subject'))
                    this.subject_id = +(this.getCookie('subject'))
                    this.subject_name = this.getCookie('sub_name')
                }

                // this.subject.id = +(this.$route.query.subject)
                if((this.getCookie('section')) != 'undefined' && !isNaN(this.getCookie('section'))) {
                    this.section.id = +(this.getCookie('section'))
                    this.section_id = +(this.getCookie('section'))
                    this.section_name = this.getCookie('theme_name')
                }

                document.getElementById('select_subject').click();

                this.block_btn = false

                if (this.subject_id !== 'undefined'  && !isNaN(this.subject_id)) {
                    await this.getSections(this.subject_id)
                }
                if (this.classe !== '' && this.classe !== 'undefined' && this.subject_id !== 0 && !isNaN(this.subject_id) && this.section_id !== 0 && !isNaN(this.section_id) && this.getCookie('date_start') != '' && this.getCookie('date_end') != '') {
                    this.getSchoolboys(true)
                }
                this.overlay = false;
            }
        }
</script>

<style scoped>

</style>
