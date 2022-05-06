<template>
  <div>
    <!-- DATATABLE -->
    <vue-good-table
      :columns="columns"
      :rows="rows"
      :search-options="{ enabled: true }"
      :pagination-options="{ enabled: true }"
      :line-numbers="true"
    >
      <div slot="table-actions">
        <button v-on:click="addData()" class="btn btn-primary">
          A&ntilde;adir
        </button>
        <button v-on:click="exportInventory()" class="btn btn-success">
          Exportar
        </button>
      </div>
      <template slot="table-row" slot-scope="props">
        <span v-if="props.column.field === 'active'">
          <div v-if="props.row.active == 1">Activo</div>
          <div v-else>Finalizado</div>
        </span>
        <span v-else-if="props.column.field === 'delete'">
          <button v-on:click="editData(props.row.id)" class="btn btn-info text-light">
            Detalle
          </button>
          <button v-if="props.row.active == 1" v-on:click="closeInventory(props.row.id)" class="btn btn-warning text-dark">
            Finalizar Inventario
          </button>
          <!-- <button v-on:click="deleteData(props.row.id)" class="btn btn-danger">
            Eliminar
          </button> -->
        </span>
        <span v-else>
          {{ props.formattedRow[props.column.field] }}
        </span>
      </template>
    </vue-good-table>
    <!-- MODAL -->
    <b-modal
      hide-footer
      id="modal-inventary"
      ref="modal"
      size="lg"
      title="Inventario"
      @show="resetModal"
      @hidden="resetModal"
      @ok="handleOk"
    >
      <ValidationObserver ref="observer" v-slot="{ handleSubmit }">
        <form ref="form" @submit.prevent="handleSubmit(dataSubmit)">


          <div class="row">
            <div class="form-group col" data-content="Cicloparqueadero">
              <label for="name">Bici Estación</label>
              <ValidationProvider
                name="cicloparqueadero"
                rules="required"
                v-slot="{ errors }"
              >
                <b-form-select
                  :options="parkingData"
                  :disabled="form.active == 0 || form.id != ''"
                  v-model="form.parkings_id"
                  class="form-control-user form-control"
                  :class="errors[0] ? 'is-invalid' : ''"
                >
                </b-form-select>
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>
            <div class="form-group col" data-content="Fecha de Inventario">
              <label for="name">Fecha de Inventario</label>
              <ValidationProvider
                name="fecha de inventario"
                rules="required"
                v-slot="{ errors }"
              >
                <!-- <b-form-datepicker
                  v-model="form.date"
                  class="form-control-user form-control"
                  :disabled="form.active == 0"
                  :class="errors[0] ? 'is-invalid' : ''"
                ></b-form-datepicker> -->

                 <datepicker
                  :bootstrap-styling="true"
                  :language="es"
                  :calendar-button="true"
                  calendar-button-icon="fa fa-calendar"
                  format="yyyy MMM dd"
                  :disabled="form.active == 0"
                  :full-month-name="true"
                  v-model="form.date"
                  :input-class="
                    errors[0]
                      ? 'form-control-user form-control is-invalid'
                      : 'form-control-user form-control'
                  "
                />

                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>

            <div class="form-group col" data-content="Hora de Entrada">
               <label for="name">Hora de Entrada</label>
                 <ValidationProvider
                     name="hora de entrada"
                     rules="required"
                     v-slot="{ errors }"
                 >
                 <b-form-input
                     v-model="form.time_input"
                     class="form-control-user form-control"
                     :class="errors[0] ? 'is-invalid' : ''"
                     @blur="checkIfTimeWorks()"
                     :readonly="!!form.id"
                     type="time"
                 ></b-form-input>
                 <span class="form-text text-danger">{{ errors[0] }}</span>
                 </ValidationProvider>
            </div>
            <!-- <div class="form-group col" data-content="Estado">
              <label for="active">Estado</label>
              <ValidationProvider
                rules="required"
                name="estado"
                v-slot="{ errors }"
              >
                <select
                  id="active"
                  v-model="form.active"
                  :disabled="form.active == 0"
                  class="form-control-user form-control"
                  :class="errors[0] ? 'is-invalid' : ''"
                >
                  <option :value="null">Seleccione una opcion</option>
                  <option value="1">Activo</option>
                  <option value="0">Finalizado</option>
                </select>
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div> -->
          </div>

          <div class="p-3 shadow-sm" v-if="form.id">
            <div :data-toggle="false" class="text-center py-2" aria-expanded="true" href="#inventoryBicies">Bicicletas Registradas en el Inventario ({{form.bicies.length}})</div>
            <div id="inventoryBicies" class="row collapse fade show" aria-expanded="true">
              <div v-for="(bicy,index) of form.bicies" :key="index" class="btn btn-secondary m-2" >
                {{bicy.code}}
              </div>
            </div>

            <div class="form-group" v-if="form.active == 1">
             <label>Registrar Bicicletas</label>

              <ValidationProvider
                name="bicicletas"
                rules="required"
                v-slot="{ errors }"
              >
                <v-select multiple v-model="form.inputBicies" :options="biciesData" :reduce="bicy => bicy.code" label="code"/>

                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>

            </div>
            <div class="my-3" v-else>
              <table class="table table-sm">
                <tbody>
                  <tr>
                    <th>Total de bicicletas registradas</th>
                    <td>{{form.report.totalRegistered}}</td>
                  </tr>
                  <tr>
                    <th>Bicicletas registradas no activas en el sistema</th>
                    <td>
                      <div v-for="(bicy,index) of form.report.nonActiveButRegistered" :key="index" class="btn btn-secondary m-2" >
                        {{bicy.code}}
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <th>Bicicletas activas en el sistema pero no registradas</th>
                    <td>
                      <div v-for="(bicy,index) of form.report.activeButNotRegistered" :key="index" class="btn btn-secondary m-2" >
                        {{bicy.code}}
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div>
            <button class="btn btn-primary btn-block my-3" type="submit">
              Guardar
            </button>
          </div>
        </form>
      </ValidationObserver>
    </b-modal>
  </div>
</template>

<script>
import toastr from "toastr";
import Swal from "sweetalert2";
import 'vue-select/dist/vue-select.css';
import Datepicker from "vuejs-datepicker";
import { en, es } from "vuejs-datepicker/dist/locale";
import XLSX from "xlsx";

export default {
  components: {
    Datepicker,
  },
  data() {
    return {
      es: es,
      parkingData: [{ value: null, text: "Selecciona una opcion" }],
      selected: [],
      biciesData : [],
      biciesRawData : [],
      form: {
        id: "",
        date: new Date().toLocaleDateString("en-CA"),
        parkings_id: null,
        bicies : null,
        inputBicies : null,
        active: null,
        report : null
      },
      columns: [
        {
          label: "Bici Estación",
          field: "parking",
        },
        {
          label: "Fecha de Inventario",
          field: "date",
        },
        {
          label: "Hora de inventario",
          field: "date",
        },
        {
          label: "Estado",
          field: "active",
        },
        {
          label: "Acciones",
          field: "delete",
        },
      ],
      rows: [],
    };
  },
  methods: {
      addData() {
          this.resetModal();
          this.$bvModal.show("modal-inventary");
      },
      handleOk(bvModalEvt) {
          bvModalEvt.preventDefault();
          this.dataSubmit();
      },
      closeInventory(id) {
          this.$api
              .put("web/data/inventory/" + id, {})
              .then((res) => {
                  if (res.status == 200) {
                      this.getData();
                      toastr.success("Inventario Finalizado");
                      this.$bvModal.hide("modal-inventary");
                  }
              });
      },
      async dataSubmit() {

          if (this.form.id) {

              if (!this.form.inputBicies.length) {
                  toastr.error('No ha seleccionado ninguna bicicleta');
                  return;
              }

              // const bicies_code = typeof this.form.inputBicies == 'object' ? Object.values(this.form.inputBicies).join(',') : this.form.inputBicies;
              const bicies_code = typeof this.form.inputBicies == 'object' ? Object.values(this.form.inputBicies).join(',') : this.form.inputBicies;
              let errors = {};

              console.log({bicies_code});

              await this.$api.post("web/data/inventoryBicy", {inventories_id: this.form.id, bicies_code})
                  .then((res) => {
                      console.log(res);
                      errors = res.data.response.errors;
                  });

              console.log({errors});
              if (!Object.keys(errors).length) {
                  toastr.success("Bicicletas registradas");
                  this.$bvModal.hide("modal-inventary");
                  this.getData();
              } else {
                  this.$bvModal.hide("modal-inventary");
                  setTimeout(() => {
                      toastr.error(`Algunos datos no han conseguido ser registrados ${Object.keys(errors).join(', ')}, por favor verificar la información existente.`);
                  }, 1000);
              }

          } else {

              const tempForm = this.form;
              if (typeof tempForm.date == 'object') {
                  let date_input = tempForm.date;
                  date_input.setDate(date_input.getDate()).toISOString().split('T')[0];
                  tempForm.date = date_input;
              } else {
                  tempForm.date = tempForm.date.split(' ')[0];
              }

              this.$api.post("web/data/inventory", this.form).then((res) => {
                  if (res.status == 201) {
                      this.getData();
                      toastr.success("Dato Guardado");
                      this.$bvModal.hide("modal-inventary");
                  }
              });

          }
      },
      resetModal() {
          toastr.clear();
          var dateCurrent = new Date();
          this.form.id = "";
          // this.form.date = new Date().toLocaleDateString("en-CA");
          this.form.date = dateCurrent.toISOString().split('T')[0] + ' 00:00';
          this.form.active = 1;
          this.form.parkings_id = null;
      },
      deleteData(id) {
          const swalWithBootstrapButtons = Swal.mixin({
              customClass: {
                  confirmButton: "btn btn-success",
                  cancelButton: "btn btn-danger",
              },
              buttonsStyling: false,
          });

          swalWithBootstrapButtons
              .fire({
                  title: "Estas Seguro De Eliminar Este Dato?",
                  text: "Esta opcion no se puede reversar",
                  icon: "warning",
                  showCancelButton: false,
                  confirmButtonText: "Eliminar",
                  timer: 5000,
                  timerProgressBar: true,
              })
              .then((result) => {
                  if (result.isConfirmed) {
                      this.$api.delete("web/data/inventory/" + id).then((res) => {
                          if (res.status == 200) {
                              this.getData();
                              toastr.success("Dato Eliminado");
                              this.$bvModal.hide("modal-inventary");
                          }
                      });
                  }
              });
      },
      editData(id) {
          this.resetModal();
          this.$api.get("web/data/inventory/" + id).then((res) => {
              if (res.status == 200) {
                  let data = res.data.response.data;
                  data.report = res.data.response.report;
                  data.date = new Date(`${data.date} 00:00`);
                  this.form = data;
                  this.biciesData = this.biciesRawData.filter(el => {
                      let filtered = !this.form.bicies.find(_el => _el.code == el.code);
                      return !!filtered
                  });

              }
          });
          this.$bvModal.show("modal-inventary");
      },
      getData() {
          this.parkingData = [{value: null, text: "Selecciona una opcion"}];
          this.$api.get("web/data/inventory").then((res) => {
              this.rows = res.data.response.data;
              res.data.response.indexes.parkings.forEach((element) => {
                  this.parkingData.push(element);
              });
          });

          this.$api.get("web/data/bicy").then((res) => {
              this.biciesRawData = res.data.response.bicies;
          });

          this.$api.get("web/data/inventory").then((res) => {
              this.rows = res.data.response.inventories.map(el=>{
                  el.nicely_entry = `${el.date_input} ${el.time_input}`;
                  el.nicely_exit = el.duration == 0  ? "" :  `${el.date_output} ${el.time_output}`; return el;
              });
              res.data.response.indexes.status.forEach((element) => {
                  this.statusData.push(element);
              });
              res.data.response.indexes.parking.forEach((element) => {
                  this.parkingData.push(element);
              });
          }).finally(function() {
              let element = document.getElementById("tableInventory")
              let wb = XLSX.utils.table_to_book(element)
              localStorage.setItem("tableInventory", JSON.stringify(wb))
          });
      },
      exportInventory() {
          let wb = JSON.parse(localStorage.getItem('tableInventory'))
          let wopts = {
              bookType: 'xlsx',
              bookSST: false,
              type: 'binary'
          }
          let wbout = XLSX.write(wb, wopts);
          FileSaver.saveAs(new Blob([this.s2ab(wbout)], {
              type: "application/octet-stream;charset=utf-8"
          }), "Inventories.xlsx");
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
  },
  created: function () {
    this.getData();
  },
};
</script>
