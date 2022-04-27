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
      </div>
      <template slot="table-row" slot-scope="props">
        <span v-if="props.column.field === 'active'">
          <div v-if="props.row.active == 1">Activo</div>
          <div v-else-if="props.row.active == 2">Inactivo</div>
        </span>
        <span v-else-if="props.column.field === 'service'">
          <div v-if="props.row.service == 'web'">Administrador</div>
          <div v-else-if="props.row.service == 'app'">Vigilante</div>
        </span>
        <span v-else-if="props.column.field === 'role_id'">
          <div v-if="props.row.role_id == '1'">Administrador</div>
          <div v-else-if="props.row.role_id == '3'">Consultador</div>
          <div v-else-if="props.row.role_id == '4'">Vigilante</div>
        </span>
        <span v-else-if="props.column.field === 'delete'">
          <button v-on:click="editData(props.row.id,props.row.prerole_id)" class="btn btn-warning">
            Editar
          </button>
          <button v-on:click="deleteData(props.row.id,props.row.prerole_id)" class="btn btn-danger">
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
      id="modal-user-app"
      ref="modal"
      title="Usuario"
      @show="resetModal"
      @hidden="resetModal"
      @ok="handleOk"
    >
      <ValidationObserver ref="observer" v-slot="{ handleSubmit }">
        <form ref="form" @submit.prevent="handleSubmit(dataSubmit)">
          
          <div class="row">
            <div class="form-group col" data-content="Nombre">
              <label for="name">Nombre</label>
              <ValidationProvider
                name="nombre"
                rules="required|min:3|max:50"
                v-slot="{ errors }"
              >
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  class="form-control-user form-control"
                  :class="errors[0] ? 'is-invalid' : ''"
                />
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>
            <div class="form-group col" data-content="Apellido">
              <label for="name">Apellido</label>
              <ValidationProvider
                name="apellido"
                rules="required|min:3|max:50"
                v-slot="{ errors }"
              >
                <input
                  id="name"
                  v-model="form.last_name"
                  type="text"
                  class="form-control-user form-control"
                  :class="errors[0] ? 'is-invalid' : ''"
                />
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>
          </div>

          <div class="form-group">
            <label for="phone">Telefono</label>
            <ValidationProvider
              name="telefono"
              rules="required|numeric|min:7|max:10|regex:\d+"
              v-slot="{ errors }"
            >
              <input
                id="phone"
                v-model="form.phone"
                type="text"
                pattern="\d+"
                class="form-control-user form-control"
                :class="errors[0] ? 'is-invalid' : ''"
              />
              <span class="form-text text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>
          <div class="form-group">
            <label for="document">Documento</label>
            <ValidationProvider
              name="documento"
              rules="required|numeric|min:5|max:20"
              v-slot="{ errors }"
            >
              <input
                id="document"
                v-model="form.document"
                type="number"
                class="form-control-user form-control"
                :class="
                  errors[0] ? 'is-invalid' : '' + form.id ? 'disabled' : ''
                "
              />
              <span class="form-text text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>
          <div class="form-group">
            <label for="email">Correo</label>
            <ValidationProvider
              name="correo"
              rules="required|email|min:6|max:50"
              v-slot="{ errors }"
            >
              <input
                id="email"
                v-model="form.email"
                type="email"
                class="form-control-user form-control"
                :class="errors[0] ? 'is-invalid' : ''"
              />
              <span class="form-text text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>
          <div class="form-group">
            <label for="password">Contraseña</label>
            <ValidationProvider
              name="contraseña"
              rules="min:8|max:16"
              v-slot="{ errors }"
            >
              <input
                id="password"
                v-model="form.password"
                type="password"
                class="form-control-user form-control"
                :class="errors[0] ? 'is-invalid' : ''"
              />
              <span class="form-text text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>
          <div class="row">
            <div class="form-group col" data-content="Estado">
              <label for="active">Estado</label>
              <ValidationProvider
                rules="required"
                name="estado"
                v-slot="{ errors }"
              >
                <select
                  id="active"
                  v-model="form.active"
                  class="form-control-user form-control"
                  :class="errors[0] ? 'is-invalid' : ''"
                >
                  <option :value="null">Seleccione una opcion</option>
                  <option value="1">Activo</option>
                  <option value="2">Inactivo</option>
                </select>
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>
            <div class="form-group col" data-content="Tipo de Usuario">
              <label for="active">Tipo de Usuario</label>
              <ValidationProvider rules="required" name="rol" v-slot="{ errors }">
                <select
                  v-model="form.role_id"
                  class="form-control-user form-control"
                  :class="errors[0] ? 'is-invalid' : ''"
                >
                  <option :value="null">Seleccione una opcion</option>
                  <option value="1">Administrador</option>
                  <option value="3">Consultor</option>
                  <option value="4">Vigilante</option>
                </select>
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>
          </div>

          <div v-if="form.role_id == 4">
            <div class="form-group col" data-content="Cicloparqueadero">
              <label for="active">Bici Estación</label>
              <ValidationProvider
                rules="required"
                name="cicloparqueadero"
                v-slot="{ errors }"
              >
                <b-form-select
                  :options="parkingData"
                  v-model="form.parkings_id"
                  class="form-control-user form-control"
                  :class="errors[0] ? 'is-invalid' : ''"
                >
                </b-form-select>
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
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
export default {
  data() {
    return {
      form: {
        id: "",
        name: "",
        last_name: "",
        email: "",
        phone: "",
        document: "",
        password: "",
        active: null,
        parkings_id: null,
        role_id: null,
        prerole_id: null,
      },
      columns: [
        {
          label: "Nombre",
          field: "name",
        },
        {
          label: "Apellido",
          field: "last_name",
        },
        {
          label: "CC",
          field: "document",
        },
        {
          label: "Correo",
          field: "email",
        },
        {
          label: "Telefono",
          field: "phone",
        },
        {
          label: "Estado",
          field: "active",
        },
        {
          label: "Rol",
          field: "role_id",
        },
        // { label: "Tipo Usuario", field: "service", }, SE COMENTA A RAZÓN DE QUE ESTO ES SÓLO ADMINISTRADORES
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
      this.$bvModal.show("modal-user-app");
    },
    handleOk(bvModalEvt) {
      bvModalEvt.preventDefault();
      this.dataSubmit();
    },
    dataSubmit() {

      console.log(this.form)
      let userRole = "";
      

      if (this.form.id) {
        switch(this.form.prerole_id){
          case '1':case 1: userRole = 'user'; break;
          case '3':case 3: userRole = 'querier'; break;
          case '4':case 4: userRole = 'vigilant'; break;
        }
        this.$api.put(`web/${userRole}/` + this.form.id, this.form).then((res) => {
          if (res.status == 200) {
            this.getData();
            toastr.success("Usuario Actualizado");
            this.$bvModal.hide("modal-user-app");
          }
        });
      } else {
        switch(this.form.role_id){
          case '1':case 1: userRole = 'user'; break;
          case '3':case 3: userRole = 'querier'; break;
          case '4':case 4: userRole = 'vigilant'; break;
        }
        this.$api.post(`web/${userRole}`, this.form).then((res) => {
          if (res.status == 201) {
            this.getData();
            toastr.success("Usuario Guardado");
            this.$bvModal.hide("modal-user-app");
          }
        });
      }
    },
    resetModal() {
      toastr.clear();
      this.form.id = "";
      this.form.name = "";
      this.form.last_name = "";
      this.form.email = "";
      this.form.phone = "";
      this.form.document = "";
      this.form.password = "";
      this.form.active = null;
    },
    deleteData(id,prerole_id) {
      
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
            let userRole = "";
            switch(prerole_id){
              case '1':case 1: userRole = 'user'; break;
              case '3':case 3: userRole = 'querier'; break;
              case '4':case 4: userRole = 'vigilant'; break;
              default: userRole = prerole_id;break;
            }
            this.$api.delete(`web/${userRole}/` + id).then((res) => {
              if (res.status == 200) {
                this.getData();
                toastr.success("Usuario Eliminado");
                this.$bvModal.hide("modal-user-app");
              }
            });
          }
        });
    },
    editData(id,prerole_id) {
      let userRole = "";
      switch(prerole_id){
        case '1':case 1: userRole = 'user'; break;
        case '3':case 3: userRole = 'querier'; break;
        case '4':case 4: userRole = 'vigilant'; break;
        default: userRole = prerole_id;break;
      }
      this.$api.get(`web/${userRole}/` + id).then((res) => {
        if (res.status == 200) {
          this.form = res.data.response.user;
        }
      });
      this.$bvModal.show("modal-user-app");
    },
    async getData() {
      let users = [];
      await this.$api.get(`web/user`).then((res) => {
        if (res.status == 200) {
          users = users.concat(res.data.response.users);
        }
      });
      await this.$api.get("web/vigilant").then((res) => {
        if (res.status == 200) {
          users = users.concat(res.data.response.users);
        }
      });
      await this.$api.get("web/querier").then((res) => {
        if (res.status == 200) {
          users = users.concat(res.data.response.users);
        }
      });
      await this.$api.get("web/data/parking").then((res) => {
        if (res.status == 200) {
          this.parkingData = res.data.response.parkings.map((el)=>{ return {value : el.id, text : el.name} });
        }
      });
      this.rows = users;
      
    },
  },

  created: function () {
    this.getData();
  },
};
</script>
