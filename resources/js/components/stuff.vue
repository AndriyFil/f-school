<template>
    <div>
            <v-dialog v-model="rating_popup" max-width="500px">
                <v-card>
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col class="col-auto">
                                    <v-avatar
                                        class="profile"
                                        color="grey"
                                        size="120"
                                        tile
                                    >
                                        <v-img :src="schoolboy_info.photo"></v-img>
                                    </v-avatar>
                                </v-col>
                                <v-col class="col">
                                    <div>
                                        <h3 style="font-size: 22px;">{{schoolboy_info.name}}</h3>
                                    </div>
                                    <div>
                                        <v-text-field v-model="rating_info.rating" label="Кількість балів"></v-text-field>
                                    </div>
                                    <div>
                                        <v-autocomplete
                                            :items="rating_types"
                                            item-text="name"
                                            item-value="id"
                                            label="Тип оцінки"
                                            v-model="rating_info.rating_type"
                                            :rules="cityRules"
                                            required
                                        ></v-autocomplete>
                                    </div>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-card-text>

                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="blue darken-1" text @click="close()">Cancel</v-btn>
                        <v-btn color="blue darken-1" text @click="save()">Save</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
    </div>
</template>

<script>
        export default {
                name: "stuff"
        }
</script>

<style scoped>

</style>
