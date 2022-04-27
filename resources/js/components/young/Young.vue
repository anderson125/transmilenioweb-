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
          <div v-if="props.row.active === 1">Activo</div>
          <div v-else>Inactivo</div>
        </span>
        <span v-else-if="props.column.field === 'delete'">
          <button v-on:click="editData(props.row.id)" class="btn btn-info text-light">
            Detalle
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
      id="modal-young"
      ref="modal"
      title="Autorizacion"
      @show="resetModal"
      @hidden="resetModal"
      @ok="handleOk"
    >
      <ValidationObserver ref="observer" v-slot="{ handleSubmit }">
        <form ref="form" @submit.prevent="handleSubmit(dataSubmit)">
          <div class="form-group" data-content="Documento Ciclista">
            <label for="bikersData">Ciclista</label>
              <ValidationProvider
                name="documento"
                rules="required"
                v-slot="{ errors }"
              >
                <b-form-input name="documento" @input="checkBiker()" list="my-biker-list-id" v-model="form.biker_young" :disabled="editing"></b-form-input>
                <datalist id="my-biker-list-id" >
                  <option v-for="(biker,index) in editing ? allBikersData : bikersData " :key="index" :value="biker.document" >{{ biker.text }}</option>
                </datalist>
                
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider> 

              <div class="row mb-3">
                <div class="form-group col">
                  <label>Nombres</label>
                  <input disabled class="form-control-user form-control" v-model="selected.biker.name"/></div>
              </div>

          </div>
          <div class="form-group" data-content="Representante">
            <label for="representative">Representante</label>

              <ValidationProvider name="documentoAcudiente" rules="required" v-slot="{ errors }" >
                <div class="row">
                  <div class="col-10">
                    <b-form-input name="documentoAcudiente" @input="checkParent()" list="my-parents-list-id" v-model="form.parents_id" :disabled="editing"></b-form-input>
                    <datalist id="my-parents-list-id">
                      <option v-for="(parent,index) in parentData" :key="index" :value="parent.value">{{ parent.text }}</option>
                    </datalist>                    
                    <span class="form-text text-danger">{{ errors[0] }}</span>
                  </div>
                  <div class="btn btn-info btn-sm text-light fa fa-envelope col-2" v-show="(form.parents_id && !errors.length) && !editing" @click="sendMessage()"></div>
                </div>
              </ValidationProvider> 
              <div class="row">
                <div class="form-group col">
                  <label>Nombre</label>
                  <input disabled class="form-control-user form-control" v-model="selected.parent.name"/></div>
                <div class="form-group col">
                  <label>Teléfono</label>
                  <input disabled class="form-control-user form-control" v-model="selected.parent.phone"/></div>
              </div>

          </div>

          <div v-show="!editing" class="form-group col" data-content="Confirmacion SMS">
            <label for="name">Confirmación SMS</label>
            <ValidationProvider
              name="confirmacion sms"
              rules=""
              v-slot="{ errors }"
            >
              <input
                v-model="form.confirmation"
                type="text"
                class="form-control-user form-control"
                :class="errors[0] ? 'is-invalid' : ''"
              />
              <span class="form-text text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>
         
          <div v-show="!editing">
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
      parentData: [{ value: null, id:null, text:"Selecciona una opción" }],
      bikersData : [{value: null, id:null, text: "Selecciona una opción"}],
      allBikersData : [],
      form: {
        id: "",
        biker_young: "",
        parents_id: null,
        users_id: "",
      },
      columns: [
        {
          label: "Menor",
          field: "full_young_tag",
        },
        {
          label: "Representante",
          field: "full_parent_tag",
        },
        // {
        //   label: "Parentesco",
        //   field: "parent",
        // },
        {
          label: "Acciones",
          field: "delete",
        },
      ],
      rows: [],
      editing : false,

      selected : {
        biker : {
          name : "",
          id : null,
          document : ""
        },
        parent : {
          name : "",
          id : null,
          phone : ""
        }
      }
      
    };
  },
  methods: {
    addData() {
      this.editing = false;
      this.resetModal();
      this.$bvModal.show("modal-young");
    },
    handleOk(bvModalEvt) {
      bvModalEvt.preventDefault();
      this.dataSubmit();
    },
    dataSubmit() {
      console.log(this.form);
      if (this.form.id) {
        toastr.error('No es posible actualizar registros.'); return;
        this.$api
          .put("web/data/young/" + this.form.id, this.form)
          .then((res) => {
            if (res.status == 200) {
              this.getData();
              toastr.success("Dato Actualizado");
              this.$bvModal.hide("modal-young");
            }
          });
      } else {
        this.$api.post("web/data/young", {parent : this.selected.parent.id, young : this.selected.biker.id, confirmation : this.form.confirmation}).then((res) => {
          if (res.status == 201) {
            this.getData();
            toastr.success("Dato Guardado");
            this.$bvModal.hide("modal-young");
          }
        });
      }
    },
    resetModal() {
      toastr.clear();
      this.form.id = "";
      this.form.biker_young = "";
      this.form.parents_id = null;
      this.form.users_id = "";

      this.selected.biker.name = "";
      this.selected.biker.document = "";
      this.selected.parent.name = "";
      this.selected.parent.phone = "";
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
            this.$api.delete("web/data/young/" + id).then((res) => {
              if (res.status == 200) {
                this.getData();
                toastr.success("Dato Eliminado");
                this.$bvModal.hide("modal-young");
              }
            });
          }
        });
    },
    editData(id) {
      this.editing = true;
      this.$api.get("web/data/young/" + id + "/edit").then((res) => {
        if (res.status == 200) {
          this.form = res.data.response.youngs;
          this.checkParent(this.form.parents_id);
          this.checkBiker(this.form.biker_young);
        }
      });
      this.$bvModal.show("modal-young");
    },
    getData() {
      this.$api.get("web/data/young").then((res) => {
        if(res.status == 200){
          this.rows = res.data.response.data.map(el =>{
            el.full_young_tag = `${el.young_document} - ${el.young_name} ${el.young_last_name}`
            el.full_parent_tag = `${el.parent_document} - ${el.parent_name}`
            console.log({el});
            return el;
          });
          this.parentData = this.parentData.concat(res.data.response.indexes.parents);
        }
      });

      this.$api.get("web/data/biker").then((res) => {
        if(res.status == 200){
          let filtered = res.data.response.users.filter((el)=> el.auth == "2" ).map(el=>{ 
            return { value: el.document, text :`${el.name} ${el.last_name}`, 
              id: el.id, document : el.document, name :`${el.name} ${el.last_name}` 
            } 
          });
          this.bikersData = this.bikersData.concat(filtered);
          this.allBikersData = res.data.response.users;
        }
      });
    },    
    sendMessage(){

      if(!this.parentData.filter(el => el.id == this.selected.parent.id ).length || !this.selected.parent.id){
        toastr.error("El identificador del acudiente no se ha recibido correctamente.");       
        return;   
      }

      this.$api.post("web/data/biker/parentVerificationCode/" + this.selected.parent.id, {biker_id : this.selected.biker.id }).then((res) => {
        if(res.status == 200){
          toastr.success("Código de verificación enviado");
        }else{
          toastr.error("Error inesperado");          
        }
      });
    },
    checkBiker(specified = false){
      let biker;
      if(specified){
        biker = this.allBikersData.filter(el => el.document == specified);
      }else{
        biker = this.bikersData.filter(el => el.document == this.form.biker_young);
      }
      if(!biker.length){
        this.form.biker_young = this.selected.biker.name = this.selected.biker.document =  ""; return;
      } 
      this.selected.biker.name = (specified) ? `${biker[0].name} ${biker[0].last_name}` : biker[0].name;
      this.selected.biker.id = biker[0].id;
    },
    checkParent(specified = false){
      let parent;
      if(specified){
        parent = this.parentData.filter(el => el.document == specified);
      }else{
        parent = this.parentData.filter(el => el.document == this.form.parents_id);
      }
      if(!parent.length){
        this.form.parents_id = this.selected.parent.name = this.selected.parent.phone =  ""; return;
      }
      this.selected.parent.name = parent[0].text;
      this.selected.parent.phone = parent[0].phone;
      this.selected.parent.id = parent[0].id;
    },
  },

  created: function () {
    this.getData();
  },
};
</script>
