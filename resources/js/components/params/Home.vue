<template>
  <!-- Approach -->
  <div>
    <b-card no-body class="shadow mb-4">
      <b-tabs card>
        <b-tab title="Usuarios" active>
          <div>
            <h6 class="m-0 font-weight-bold text-danger">Parametros</h6>
            <div class="py-2">
              <b-button
                v-b-modal.modal-1
                class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm"
                @click="modalShow = !modalShow"
                ><i class="fas fa-plus fa-sm text-white-50"></i>
                Añadir</b-button
              >
            </div>
            <br />
            <div class="table-responsive">
              <table id="user_table" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th class="singleLine">Nombre</th>
                    <th class="singleLine">
                      N&uacute;mero de identificaci&oacute;n
                    </th>
                    <th class="singleLine">Estado</th>
                    <th class="singleLine">Acciones</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </b-tab>
        <b-tab title="Roles"> </b-tab>
        <b-tab title="Permisos"> </b-tab>
      </b-tabs>
    </b-card>

    <b-modal
      id="modal-1"
      v-model="modalShow"
      hide-footer
      title="Añadir Usuario"
    >
      <b-form @submit="onSubmit" @reset="onReset" v-if="show">
        <b-form-group label="Nombre:" label-for="input-1">
          <b-form-input
            id="input-1"
            v-model="form.name"
            type="text"
            required
            placeholder="Digita tu nombre"
          ></b-form-input>
        </b-form-group>
        <b-form-group label="Numero de Documento:" label-for="input-2">
          <b-form-input
            id="input-2"
            v-model="form.document"
            type="number"
            required
            placeholder="Digita tu documento"
          ></b-form-input>
        </b-form-group>
        <b-form-group label="Contraseña:" label-for="input-3">
          <b-form-input
            id="input-3"
            v-model="form.password"
            type="password"
            required
            placeholder="Digita tu contraseña"
          ></b-form-input>
        </b-form-group>
        <b-button class="das btn-block mt-5" type="submit" variant="danger"
          >Guardar</b-button
        >
      </b-form>
    </b-modal>
  </div>
</template>
<script>
export default {
  components: {},
  data() {
    return {
      modalShow: false,
      form: {
        name: "",
        document: null,
        password: "",
      },
      show: true,
    };
  },
  methods: {
    onSubmit(evt) {
      evt.preventDefault();
      alert(JSON.stringify(this.form));
    },
  },
};
</script>