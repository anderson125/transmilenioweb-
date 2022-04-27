<template>
  <div>
    <h3>PERMISOS</h3>
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
        <span>
          {{ props.formattedRow[props.column.field] }}
        </span>
      </template>
    </vue-good-table>
    <!-- MODAL -->
    <b-modal
      hide-footer
      id="modal-visit"
      ref="modal"
      title="Visita"
    >

    </b-modal>
  </div>
</template>

<script>
import toastr from "toastr";
import Swal from "sweetalert2";
import Datepicker from "vuejs-datepicker";
import { en, es } from "vuejs-datepicker/dist/locale";
export default {
  components: {
    Datepicker,
  },
  data() {
    return {      

      columns: [
        {
          label: "Permiso",
          field: "name",
        },
      ],
      rows: [],
    };
  },
  created: function () {
    this.getData();
  },
  methods: {
    getData() {
      this.$api.get("web/data/permissions").then((res) => {
        console.log(res);
        this.rows = res.data.response.data;
      })
      .catch((err) => {
        console.log('error', err);
      })
      ;
    },
  },
};

</script>
