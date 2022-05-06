<template>
    <div>
        <div class="my-2">
            <ValidationObserver ref="observer" v-slot="{ handleSubmit }">

                <form
                    ref="formPernoctas"
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
                                        v-model="formPernoctas.start"
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
                                        v-model="formPernoctas.end"
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
                    </div>

                    <b-button type="submit" variant="primary">Generar</b-button>
                    <b-button type="reset" variant="danger">Reset</b-button>
                    <b-button v-on:click="exportPernoctas()" class="btn btn-success">Exportar</b-button>
                </form>

            </ValidationObserver>
        </div>

        <div id="tablePernoctas"> <!-- Nombre de la tabla para poder obtener la informacióin y exportarla -->
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
import Datepicker from "vuejs-datepicker";
import { en, es } from "vuejs-datepicker/dist/locale";
import XLSX from "xlsx";
import FileSaver from 'file-saver' //Importante para exportar
export default {
    components: {
        //Para mostrar el calendario
        Datepicker,
    },
    //Información a mostrar en la tabla
    data() {
        return {
            es,
            show: true,
            formPernoctas: {
                start: new Date().toISOString(),
                end: new Date().toISOString(),
            },
            rows: [],
            columns: [
                {
                    label: "Bici Estación",
                    field: "parking_name",
                },
                {
                    label: "Cantidad",
                    field: "count",
                },
            ],
        };
    },
    //Metodos para el proceso de consulta y reinio del formulario
    methods: {
        dataSubmit(event) {
            let date_input;
            let date_output;

            if (typeof this.formPernoctas.start == "object") {
                date_input = this.formPernoctas.start;
                date_input.setDate(date_input.getDate());
                date_input = date_input.toISOString();
                date_input = date_input.split("T")[0];
            } else {
                date_input = this.formPernoctas.start.split("T")[0];
            }

            if (typeof this.formPernoctas.end == "object") {
                date_output = this.formPernoctas.end;
                date_output.setDate(date_output.getDate());
                date_output = date_output.toISOString();
                date_output = date_output.split("T")[0];
            } else {
                date_output = this.formPernoctas.end.split("T")[0];
            }

            let dateStart = new Date(date_input).getTime();
            let dateEnd = new Date(date_output).getTime();

            if (dateStart > dateEnd) {
                return toastr.error("La fecha final no puede ser menor a la fecha inicial")
            }

            this.$api.get(`/web/data/reports/visits/pernoctas?begining_date=${date_input}&end_date=${date_output}`)
                .then( (res) => {
                    if (res.status == 200) {
                        //console.log(res);
                        this.rows = res.data.response.data;
                          if(!this.rows.length){
                              toastr.info('No existen pernoctas para la fecha seleccionada.')
                          } else {
                              this.rows = res.data.response.data;
                              //console.log('pernoctasData', this.rows);
                          }
                    } else {
                         console.warn({res});
                         toastr.success("Error en la petición.");
                    }
                }).finally(function() {
                    let element = document.getElementById("tablePernoctas");
                    let wb = XLSX.utils.table_to_book(element);
                    localStorage.setItem("tablePernoctas", JSON.stringify(wb));
            });
        },
        onReset(event) {
            event.preventDefault();
            // Reset our form values
            this.formPernoctas.start = new Date().toISOString();
            this.formPernoctas.end = new Date().toISOString();
            // Trick to reset/clear native browser form validation state
            this.show = false;
            this.$nextTick(() => {
                this.show = true;
            });
        },
        //Exportar información
        exportPernoctas(){
            let wb =  JSON.parse(localStorage.getItem('tablePernoctas'));
            let wopts = {
                bookType: 'xlsx',
                bookSST: false,
                type: 'binary'
            };
            let wbout = XLSX.write(wb, wopts);
            FileSaver.saveAs(new Blob([this.s2ab(wbout)], {
                type: "application/octet-stream;charset=utf-8"
            }), "Pernoctas.xlsx");
        },
        s2ab(s) {
            if (typeof ArrayBuffer !== 'undefind') {
                var buf = new ArrayBuffer(s.length);
                var view = new Uint8Array(buf);
                for (var i = 0; i != s.length; ++i) view[i] = s.charCodeAt(i) & 0xFF;
                return buf;
            } else {
                var buf = new Array(s.length);
                for (var i = 0; i != s.length; ++i) buf[i] = s.charCodeAt(i) & 0xFF;
                return buf;
            }
        },
    }
}

</script>
