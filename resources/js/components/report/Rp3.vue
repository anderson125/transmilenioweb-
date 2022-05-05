<template>
  <div>
    <div class="my-2">
      <ValidationObserver ref="observer" v-slot="{ handleSubmit }">
        <form
          ref="form"
          @submit.prevent="handleSubmit(dataSubmit)"
          @reset="onReset"
          v-if="show"
        >
          <div class="row">
            <div class="form-group col">
              <b-form-group label="Fecha Incial" label-for="start-input">
                <ValidationProvider
                  name="Fecha Inicial"
                  rules="required"
                  v-slot="{ errors }"
                >
                  <datepicker
                    ref="startInput"
                    id="start-input"
                    :bootstrap-styling="true"
                    :language="es"
                    :calendar-button="true"
                    calendar-button-icon="fa fa-calendar"
                    :disabledDates="{ from: new Date() }"
                    format="yyyy MMM dd"
                    :full-month-name="true"
                    required
                    v-model="form.start"
                    :input-class="
                      errors[0]
                        ? 'form-control-user form-control is-invalid'
                        : 'form-control-user form-control'
                    "
                  />
                  <span class="form-text text-danger">{{ errors[0] }}</span>
                </ValidationProvider>
              </b-form-group>
            </div>

            <div class="form-group col">
              <b-form-group label="Fecha Final" label-for="end-input">
                <ValidationProvider
                  name="Fecha Final"
                  rules="required"
                  v-slot="{ errors }"
                >
                  <datepicker
                    ref="endtInput"
                    id="end-input"
                    :bootstrap-styling="true"
                    :language="es"
                    :calendar-button="true"
                    calendar-button-icon="fa fa-calendar"
                    :disabledDates="{ from: new Date() }"
                    format="yyyy MMM dd"
                    :full-month-name="true"
                    required
                    v-model="form.end"
                    :input-class="
                      errors[0]
                        ? 'form-control-user form-control is-invalid'
                        : 'form-control-user form-control'
                    "
                  />
                  <span class="form-text text-danger">{{ errors[0] }}</span>
                </ValidationProvider>
              </b-form-group>
            </div>
            <div class="form-group col">
              <b-form-group label="Cédula" label-for="biker_document-input">
                <ValidationProvider
                  name="documento de ciclistas"
                  v-slot="{ errors }"
                >
                  <v-select
                    multiple
                    v-model="form.biker_document"
                    :options="bikersData"
                    :reduce="(biker) => biker.document"
                    label="fullName"
                  />

                  <span class="form-text text-danger">{{ errors[0] }}</span>
                </ValidationProvider>
              </b-form-group>
            </div>
          </div>

          <b-button type="submit" variant="primary">Generar</b-button>
          <b-button type="reset" variant="danger">Reset</b-button>
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
import "vue-select/dist/vue-select.css";
import Datepicker from "vuejs-datepicker";
import { en, es } from "vuejs-datepicker/dist/locale";
export default {
  components: {
    Datepicker,
  },
  data() {
    return {
      show: true,
      es,
      form: {
        start: new Date().toISOString(),
          end: new Date().toISOString(),
        biker_document: null,
      },
      rows: [],
      bikersData: [],
      columns: [
        {
          label: "Cédula",
          field: "biker_document",
        },
        {
          label: "Bici Estación",
          field: "parking",
        },
        {
          label: "Visita No.",
          field: "visit_num",
        },
        {
          label: "Fecha de Ingreso",
          field: "date_input",
        },
        {
          label: "Hora de Ingreso",
          field: "time_input",
        },
        {
          label: "Fecha de Salida",
          field: "date_output",
        },
        {
          label: "Hora de Salida",
          field: "time_output",
        },
        {
          label: "Tiempo Visita",
          field: "duration",
        },
      ],
    };
  },
  methods: {
    dataSubmit(event) {
      let date_input;
      if (typeof this.form.start == "object") {
        date_input = this.form.start;
        date_input.setDate(date_input.getDate());
        date_input = date_input.toISOString();
        date_input = date_input.split("T")[0];
      } else {
        date_input = this.form.start.split("T")[0];
      }

      let date_output;
      if (typeof this.form.end == "object") {
        date_output = this.form.end;
        date_output.setDate(date_output.getDate());
        date_output = date_output.toISOString();
        date_output = date_output.split("T")[0];
      } else {
        date_output = this.form.end.split("T")[0];
      }

      let dateStart = new Date(date_input).getTime();
      let dateEnd = new Date(date_output).getTime();

      if (dateStart > dateEnd) {
        return toastr.error(
          "La fecha final no puede ser menor a la fecha inicial"
        );
      }

      const bikerDocument = this.form.biker_document
        ? `&biker_document=${this.form.biker_document}`
        : "";
      this.$api
        .get(
          `/web/data/reports/visits/detailedBikerByMonths?begining_date=${date_input}&end_date=${date_output}${bikerDocument}`
        )
        .then((res) => {
          if (res.status == 200) {
            this.rows = res.data.response.data;
          } else {
            console.warn({ res });
            toastr.success("Error en la petición.");
          }
        });
    },
    onlyFirstDay(date) {
      const day = date.getDate();
      return day > 1;
    },
    onlyLastDay(date) {
      let ymd, toISO;
      if (typeof date == "object") {
        ymd = date;
        ymd.setDate(ymd.getDate());
        toISO = ymd = ymd.toISOString();
        ymd = ymd.split("T")[0];
      } else {
        ymd = date;
      }

      const res = ymd.match(/(\d{4})-(\d{2})/),
        year = res[1],
        month = parseInt(res[2]),
        lastDay = new Date(year, month, 0).getDate(),
        day = date.getDate();
      if (this.form.end == null && day == lastDay) {
        this.form.end = toISO;
      }
      return day != lastDay;
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
    },
    getData() {
      this.$api.get("web/data/biker").then((res) => {
        if (res.status == 200) {
          this.bikersData = res.data.response.users.map((el) => {
            el.fullName = `${el.document} - ${el.name} ${el.last_name}`;
            return el;
          });
        }
      });
    },
  },
  created: function () {
    this.getData();
  },
};
</script>
