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
          <div v-else>Inactivo</div>
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
      id="modal-status"
      ref="modal"
      title="Estado de Visita"
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
            <label for="active">Estado</label>
            <ValidationProvider
              rules="required"
              name="Estado"
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
        active: null,
      },
      columns: [
        {
          label: "Nombre",
          field: "name",
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
      this.$bvModal.show("modal-status");
    },
    handleOk(bvModalEvt) {
      bvModalEvt.preventDefault();
      this.dataSubmit();
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
      this.form.code = "";
      this.form.name = "";
      this.form.active = null;
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
            this.$api.delete("web/status/visit/" + id).then((res) => {
              if (res.status == 200) {
                this.getData();
                toastr.success("Dato Eliminado");
                this.$bvModal.hide("modal-status");
              }
            });
          }
        });
    },
    editData(id) {
      this.$api.get("web/status/visit/" + id + "/edit").then((res) => {
        if (res.status == 200) {
          console.log(res);
          this.form = res.data;
        }
      });
      this.$bvModal.show("modal-status");
    },
    getData() {
      this.$api.get("web/status/visit").then((res) => {
        if (res.status == 200) {
          this.rows = res.data;
        }
      });
    },
  },

  created: function () {
    this.getData();
  },
};
</script>
