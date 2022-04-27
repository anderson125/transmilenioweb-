<template>
  <div>

    <div class="my-3 mx-2 row">
      <span for="selectParkings col-md-3 text-center w-100" style="font-weigh:bold">Bici Estación</span>
      <div class="col-md-8">
        <select id="selectParkings" class="form-control"  @change="loadBikers" v-model="selectedParking">
          <option v-for="(parking, key) in parkingsData" :key="key" :value="parking.value">{{parking.text}}</option>
        </select>
      </div>
    </div>

    <!-- DATATABLE -->
    <vue-good-table
      ref="recordsTable"
      :columns="columns"
      :rows="rows"
      :search-options="{ enabled: true }"
      :pagination-options="{ enabled: true }"
      :select-options="{ enabled: true}"
      @on-selected-rows-change="selectionChanged"
      
    >
      <div slot="table-actions">
        <button v-on:click="addData()" :disabled="!form.bikers.length" class="btn btn-primary">
          Enviar Mensaje
        </button>
        <input id="file-upload" class="d-none" type="file" />
      </div>
      <template slot="table-row" slot-scope="props">
        <span v-if="props.column.field === 'active'">
          <div v-if="props.row.active == 1">Activo</div>
          <div v-else-if="props.row.active == 2">Inactivo</div>
          <div v-else>Bloqueado</div>
        </span>
        <span v-else-if="props.column.field === 'auth'">
          <div v-if="props.row.auth == 1">Si</div>
          <div v-else>No</div>
        </span>
        <span v-else-if="props.column.field === 'delete'">
          <button v-on:click="editData(props.row.id)" class="btn btn-warning">
            Editar
          </button>
          <button v-on:click="deleteData(props.row.id)" class="btn btn-danger">
            Eliminar
          </button>
        </span>
        <span v-else>
          {{ props.formattedRow[props.column.field] }}
        </span>
      </template>
    </vue-good-table>
    <!-- MODAL -->
    <b-modal
      hide-footer
      id="modal-biker"
      ref="modal"
      size="xl"
      title="Ciclistas"
      @show="resetModal"
      @hidden="resetModal"
      @ok="handleOk"
    >
      <ValidationObserver ref="observer" v-slot="{ handleSubmit }">
        <form ref="form" @submit.prevent="handleSubmit(dataSubmit)">
         
          <div class="row">
            <div class="form-group col" data-content="Mensaje">
              <label for="name">Mensaje</label>
              <ValidationProvider
                name="mensaje"
                rules="required|min:2|max:100"
                v-slot="{ errors }"
              >
                <input
                  id="name"
                  v-model="form.message"
                  type="text"
                  class="form-control-user form-control"
                  :class="errors[0] ? 'is-invalid' : ''"
                />
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>
          </div>
          <div>
            <label>Ciclistas seleccionados</label>
            <ul>
              <li v-for="(bikerId,kkey) in form.bikers" :key="kkey">
                {{findBiker(bikerId)}}
              </li>
            </ul>
            
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
  export default {
    components: { },
    data() {
      return {
        form: {
          message: "",
          bikers: [],
        },
        parkingsData : [],
        selectedParking : '',
        columns: [
          {
            label: "Código",
            field: "code",
          },
          {
            label: "Nombre",
            field: "name",
          },
          {
            label: "Apellido",
            field: "last_name",
          },
          {
            label: "Tipo Documento",
            field: "type",
          },
          {
            label: "Documento",
            field: "document",
          },
          {
            label: "Fecha de Nacimiento",
            field: "birth",
          },
          {
            label: "Genero",
            field: "gender",
          },
          {
            label: "Telefono",
            field: "phone",
          },
          {
            label: "Correo",
            field: "email",
          },
          {
            label: "Ocupacion",
            field: "job",
          },
          {
            label: "Barrio",
            field: "neighborhood",
          },
          {
            label: "Estrato",
            field: "levels_id",
          },
          {
            label: "Registro",
            field: "register",
          },
          {
            label: "Autorizado",
            field: "auth",
          },
          {
            label: "Acciones",
            field: "delete",
          },
        ],
        rows: [],
        actualRows : []
      };
    },
    methods: {
      findBiker(_id){

        const biker = this.actualRows.find(el => el.id == _id);
        return `${biker.name} ${biker.last_name}`

      },
      addData() {
        this.$bvModal.show("modal-biker");
      },
      handleOk(bvModalEvt) {
        bvModalEvt.preventDefault();
        this.dataSubmit();
      },
      selectionChanged(){
        this.form.bikers = this.$refs['recordsTable'].selectedRows.map(el => el.id );
      },
      dataSubmit() {
        console.log(this.form);
        this.$api
          .post("web/data/biker/massiveRawMsg", {bikers:this.form.bikers.join(','), message : this.form.message}, {
            headers: {},
          })
          .then((res) => {
            if (res.status == 201 || res.status == 200 ) {
              console.log(res);
              this.getData();
              toastr.success("Éxito");
              this.$bvModal.hide("modal-biker");
            }
          });

      },
      resetModal() {        
        // this.form.bikers = [];
        this.form.message = '';        
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
              this.$api.delete("web/data/biker/" + id).then((res) => {
                if (res.status == 200) {
                  this.getData();
                  toastr.success("Dato Eliminado");
                  this.$bvModal.hide("modal-biker");
                }
              });
            }
          });
      },
      loadBikers(){
        this.rows = this.actualRows.filter(el => el.parkings_id == this.selectedParking);
      },
      getData() {
        this.$api.get("web/data/biker").then((res) => {
          this.actualRows = res.data.response.users;

          this.parkingsData = [{text: 'Seleccione una Bici Estación', value: ''}]
          res.data.response.indexes.parkings.forEach((element) => {
            this.parkingsData.push(element);
          });
          console.log(this.parkingsData,'parkings');

        });
      },
    },

    created: function () {
      this.getData();
    }
  };
</script>