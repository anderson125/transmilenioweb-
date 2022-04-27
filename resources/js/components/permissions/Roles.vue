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
        <span v-if="props.column.field === 'delete'">
          <button v-on:click="editData(props.row.id, props.row)" class="btn btn-warning">
            Editar
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
      id="modal-status"
      ref="modal"
      title="Editar Rol"
      @show="resetModal"
      @hidden="resetModal"
      @ok="handleOk"
    >
      <ValidationObserver ref="observer" v-slot="{ handleSubmit }">
        <form ref="form" @submit.prevent="handleSubmit(dataSubmit)">
          <div class="form-group">
            <label for="name">Nombre</label>
            <ValidationProvider
              name="nombre"
              rules="required|min:6|max:100"
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

          <div class="form-group">
            <label for="active">Permisos</label>
            <ValidationProvider
              name="Permisos"
              v-slot="{ errors }"
            >
              <ul>

                <li v-for="permission in getPermissions()" :key="permission.id">
                  <input type="checkbox" :id="'permissionCheck' + permission.id" 
                    :value="permission.id" 
                    v-model="form.permissions[permission.id]" 
                  >
                  <label :for="'permissionCheck' + permission.id">{{permission.name}}</label>
                </li>
              </ul>
              
              <span class="form-text text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>
          <div>
            <button class="btn btn-primary btn-block my-3" type="submit">
              Guardar
            </button>
          </div>
        </form>
      </ValidationObserver>
    </b-modal>


    <div class ="shadow m-4">
      <ValidationObserver ref="observer" v-slot="{ handleSubmit }">
        <form ref="form" @submit.prevent="handleSubmit(dataSubmitB)">
          <div class="form-group">
            <label for="permission">Permiso ID</label>
            <ValidationProvider
              name="permiso"
              rules="required"
              v-slot="{ errors }"
            >
              <input
                id="permission"
                v-model="formB.permission"
                type="text"
                class="form-control-user form-control"
                :class="errors[0] ? 'is-invalid' : ''"
              />
              <span class="form-text text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>
          <div class="form-group">
            <label for="role">Rol ID</label>
            <ValidationProvider
              name="rol"
              rules="required"
              v-slot="{ errors }"
            >
              <input
                id="role"
                v-model="formB.role"
                type="text"
                class="form-control-user form-control"
                :class="errors[0] ? 'is-invalid' : ''"
              />
              <span class="form-text text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>
          <div>
            <button class="btn btn-primary btn-block my-3" type="submit">
              Guardar
            </button>
          </div>
        </form>
      </ValidationObserver>
    </div>


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
        permissions : [],
        active: null,
      },
      formB:{
        permission : null,
        role : null,
      },
      columns: [
        {
          label: "Nombre",
          field: "name",
        },
        {
          label: "Acciones",
          field: "delete",
        },
      ],
      rows: [],
      permissions: [],
      bool : true
    };
  },
  methods: {
    getPermissions(){
      return this.permissions;
    },
    addData() {
      this.$bvModal.show("modal-status");
    },
    handleOk(bvModalEvt) {
      bvModalEvt.preventDefault();
      this.dataSubmit();
    },
    dataSubmitB(){
      const data = { permission_id : [this.formB.permission] }
      this.$api.post('web/data/roles/authRoleTo/' + this.formB.role, data ).then( res =>{
        if(res.status == 200 || res.status == 201 ){
          console.log(res.data.response);
        }
      })
    },
    dataSubmit() {
      if (this.form.id) {
        this.$api.put("web/status/visit/" + this.form.id, this.form).then((res) => {
          if (res.status == 200) {
            this.getData();
            toastr.success("Dato Actualizado");
            this.$bvModal.hide("modal-status");
          }
        });
      } else {
        this.$api.post("web/status/visit", this.form).then((res) => {
          if (res.status == 201) {
            this.getData();
            toastr.success("Dato Guardado");
            this.$bvModal.hide("modal-status");
          }
        });
      }
    },
    resetModal() {
      toastr.clear();
      this.form.id = "";
      this.form.name = "";
      this.form.permissions = {};
      this.form.active = null;
    },
    editData(id, eRecord) {
      
      const record = JSON.parse(JSON.stringify(eRecord));

      const eform  = {
        id : record.id,
        name : record.name,
        permissions : {},
      }
      for(let permission of record.permissions){
          eform.permissions[permission.id] = true;
      }
      this.form = eform ;


      console.log({form : this.form, record,eform, name : record.name});

      this.$bvModal.show("modal-status");
    },
    getData() {
      this.$api.get("web/data/roles").then((res) => {
        if (res.status == 200) {
          this.rows = res.data.response.data;
        }
      });

      this.$api.get("web/data/permissions").then((res) => {
        console.log(res);
        this.permissions = res.data.response.data;
      });

    },
  },

  created: function () {
    this.getData();
  },
};
</script>
