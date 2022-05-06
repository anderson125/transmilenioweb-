<template>
    <div id="ServiceSupport" class="my-2">
        <div>

        </div>

        <div>
            <vue-good-table
                :columns="columns"
                :rows="rows"
                :search-options="{ enabled: true }"
                :pagination-options="{ enabled: true }"
                :line-numbers="true"
            >
                <div slot="table-actions"></div>
                <template slot="table-row" slot-scope="props">
              <span>
                {{ props.formattedRow[props.column.field] }}
              </span>
                </template>
            </vue-good-table>
        </div>

    </div>



</template>

<script>

import toastr from "toastr";
import { en, es } from "vuejs-datepicker/dist/locale";
export default {

    data() {
        return {
            es,
            show: true,
            rows: [],
            columns: [
                {
                    label: "Parqueadero",
                    field: "parking_name",
                },
                {
                    label: "Titulo",
                    field: "title",
                },
                {
                    label: "Descripción",
                    field: "description",
                },
                {
                    label: "Estado",
                    field: "status",
                },
                {
                    label: "Acciones",
                    field: "status",
                },
            ],
        };
    },
    methods :{
        getData() {
            this.$api.get(`web/data/servicesupport`).then((res) => {
                this.rows = res.data.response.data;
                console.log(this.rows);
            });
        }

    },
    created: function () {
        this.getData();
    },

}
</script>
