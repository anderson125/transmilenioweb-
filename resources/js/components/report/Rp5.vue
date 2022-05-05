<template>
  <div>
    <div class="my-2">
      <ValidationObserver ref="observer" v-slot="{ handleSubmit }">
        <form ref="form" @submit.prevent="handleSubmit(dataSubmit)" @reset="onReset" v-if="show">
          <b-button type="submit" variant="primary">Generar</b-button>
          <!-- <b-button type="reset" variant="danger">Reset</b-button> -->
        </form>
      </ValidationObserver>
    </div>

    <div>
      <vue-good-table
        :columns="columns"
        :rows="rows"
        :search-options="{ enabled: true }"
        :pagination-options="{ enabled: true }"
        :line-numbers="true"
      >
        <div slot="table-actions">
          <!-- <button v-on:click="addData()" class="btn btn-primary">
            A&ntilde;adir
          </button> -->
        </div>
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
import Swal from "sweetalert2";
import 'vue-select/dist/vue-select.css';
export default {
  data() {
    return {
        show: true,
        form : {
            start: new Date().toISOString(),
            end: new Date().toISOString(),
            biker_document: null,
        },
        rows : [],
        parkingsData : [],
        columns : [

            {
            label : "Bici Estación",
            field : "parking_name",
            },
            {
            label : "Cédula",
            field : "biker_document",
            },
            {
            label : "Visita No.",
            field : "visit_num",
            },
            {
            label : "Fecha ingreso",
            field : "date_input",
            },
            {
            label : "Dias en abandono",
            field : "duration",
            }
        ]
    };
  },
  methods: {
    dataSubmit(event) {
      this.$api.get(`/web/data/reports/visits/abandonedBicies`).then((res) => {
          if (res.status == 200) {
            this.rows = res.data.response.data;
            if(!this.rows.length){
                toastr.info('No existen registros actualmente.')
            }
          }else{
            console.warn({res});
            toastr.success("Error en la petición.");
          }
      });
    },

    noLess(ymd, date){
      const p1 = date.toISOString(),
        _date = p1.split('T')[0];
      return _date < this.form.start;
    },
    onReset(event) {
      event.preventDefault();
      // Reset our form values
        this.form.start = new Date().toISOString();
        this.form.end = new Date().toISOString();
      // Trick to reset/clear native browser form validation state
      this.show = false;
      this.$nextTick(() => {
        this.show = true;
      });

    }
  }
};
</script>
