<template>

            <div class="content-welcome" data-app>
                <div class="for-users-block">
                    <div class="title-block">
                        Для користування
                    </div>
                    <div class="card-block">
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
                    ]
                }
            },
        }
</script>

<style scoped>

</style>
