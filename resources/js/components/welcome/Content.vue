<template>

            <div class="content-welcome" data-app>
                <div class="for-users-block">
                    <div class="title-block">
                        Для користування
                    </div>

<!--                    <v-sheet-->
<!--                        class="mx-auto d-flex justify-center"-->
<!--                        max-width="1300"-->
<!--                    >-->

<!--                        <v-slide-group-->
<!--                            v-model="model"-->
<!--                            class="pa-4"-->
<!--                            center-active-->
<!--                            show-arrows-->
<!--                        >-->
<!--                            <v-slide-item-->
<!--                                reverse-transition="fade-transition"-->
<!--                                transition="fade-transition"-->
<!--                                v-for="card in cards" :key="card.title"-->
<!--                            >-->
                                <div class="card-block" style="flex-wrap: wrap">
                                <div class="card" v-for="card in cards" :key="card.title">
                                    <div class="image-block">
                                        <img :src="card.img" :alt="card.img">
                                    </div>
                                    <div class="text-wrap">
                                        <div class="card-title">
                                            {{  card.title }}
                                        </div>
                                        <div class="card-text">
                                            {{  card.text }}
                                        </div>
                                    </div>
                                </div>
                                </div>
<!--                            </v-slide-item>-->
<!--                        </v-slide-group>-->

<!--                    </v-sheet>-->
                </div>
            </div>

</template>

<script>
        export default {
            data () {
                return {
                    cards: [
                        {img: "../img/schoolboy.svg", title: 'Учням', text: "Слідкувати за своєю успішністю стане простіше!"},
                        {img: "../img/parents.svg", title: 'Батькам', text: "Слідкувати за успішністю дитини стане простіше!"},
                        {img: "../img/teacher.svg", title: 'Вчителям', text: "Допомагає позбавитись від рутинної роботи і більше зосередитись на навчанні!"},
                    ],
                    model: null
                }
            },
        }
</script>

<style scoped>

</style>
