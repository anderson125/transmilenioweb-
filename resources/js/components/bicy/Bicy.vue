<template>
  <div id="tableBicies">
    <!-- DATATABLE -->
    <vue-good-table
      :columns="columns"
      :rows="rows"
      :search-options="{ enabled: true, }"
      :pagination-options="{ enabled: true }"
      :row-style-class="rowStyleClassFn"
      :sort-options="{
        enabled: true,
        initialSortBy: {field: '_id', type: 'asc'}
      }"
      
    >
      <div slot="table-actions">
        <button v-on:click="addData()" class="btn btn-primary">
          A&ntilde;adir
        </button>
        <label for="file-upload" class="btn btn-success my-auto">
          Importar
        </label>
        <button v-on:click="exportBicies()" class="btn btn-primary">
          Exportar
        </button>
        <input id="file-upload" class="d-none" type="file" />
      </div>
      <template slot="table-row" slot-scope="props">
        <span v-if="props.column.field === 'active'">
          <div v-if="props.row.active == 1">Activo</div>
          <div v-else-if="props.row.active == 2">Inactivo</div>
          <div v-else>Bloqueado</div>
        </span>
        <span v-else-if="props.column.field === 'url_image_back'">
          <a :href=" props.formattedRow[props.column.field] " target="_blank" rel="noopener noreferrer">
          <img :src=" props.formattedRow[props.column.field] " min-width="70" height="70" />
          </a>
        </span>
        <span v-else-if="props.column.field === 'url_image_front'">
          <a :href=" props.formattedRow[props.column.field] " target="_blank" rel="noopener noreferrer">
          <img :src=" props.formattedRow[props.column.field] " min-width="70" height="70" />
          </a>
        </span>
        <span v-else-if="props.column.field === 'url_image_side'">
          <a :href=" props.formattedRow[props.column.field] " target="_blank" rel="noopener noreferrer">
          <img :src=" props.formattedRow[props.column.field] " min-width="70" height="70" />
          </a>
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
      id="modal-bicy"
      ref="modal"
      size="lg"
      title="Bicicletas"
      @show="resetModal"
      @hidden="resetModal"
      @ok="handleOk"
    >
      <ValidationObserver ref="observer" v-slot="{ handleSubmit }">
        <form ref="form" @submit.prevent="handleSubmit(dataSubmit)">
          
          
          <div class="form-group" data-content="Cicloparqueadero">
            <label for="name">Bici Estación</label>
            <ValidationProvider
              name="cicloparqueadero"
              rules="required"
              v-slot="{ errors }"
            >
              <b-form-select
                :options="parkingData"
                v-model="form.parkings_id"
                class="form-control-user form-control"
                @change="bringBikeConsecutive"
                :class="errors[0] ? 'is-invalid' : ''"
              >
              </b-form-select>
              <span class="form-text text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
          </div>
          
          <div class="row">

            <div class="form-group col" data-content="Documento Ciclista">
              <label for="bikersData">Documento Ciclista</label>
             
              <ValidationProvider
                name="documento"
                rules="required|min:5|max:20|numeric"
                v-slot="{ errors }"
              >
                <b-form-input name="documento" list="my-list-id" v-model="form.document"></b-form-input>
                <datalist id="my-list-id">
                  <option v-for="(biker,value) in bikersData" :key="value" :value="biker.value">{{ biker.text }}</option>
                </datalist>
                
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider> 

            </div>

            <div class="form-group col" data-content="Codigo">
              <label for="name">Codigo</label>
              <ValidationProvider
                name="codigo"
                rules="required|min:1|max:20"
                v-slot="{ errors }"
              >
                <input
                  v-model="form.code"
                  type="text"
                  class="form-control-user form-control"
                  :class="errors[0] ? 'is-invalid' : ''"
                />
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>

            <div class="form-group col" data-content="Serial">
            <label for="serial">Serial</label>
            <ValidationProvider
              name="serial"
              rules="max:200|min:2"
              v-slot="{ errors }"
            >
              <b-form-input
                v-model="form.serial"
                id="serial"
                class="form-control-user form-control p-0 m-0"
                style="height:35px"
                :class="errors[0] ? 'is-invalid' : ''"
              ></b-form-input>
              <span class="form-text text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
            </div>

          </div>

          <div class="row">

            <div class="form-group col" data-content="Marca">
            <label for="name">Marca</label>
            <ValidationProvider
              name="brand"
              rules="required"
              v-slot="{ errors }"
            >
              <b-form-input
                v-model="form.brand"
                class="form-control-user form-control"
                :class="errors[0] ? 'is-invalid' : ''"
              >
              </b-form-input>
              <span class="form-text text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
            </div>
            <div class="form-group col" data-content="Color">
              <label for="name">Color</label>
              <ValidationProvider
                name="color"
                rules="required"
                v-slot="{ errors }"
              >
                <b-form-input
                  v-model="form.color"
                  class="form-control-user form-control"
                  :class="errors[0] ? 'is-invalid' : ''"
                >
                </b-form-input>
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>
            <div class="form-group col" data-content="Llanta">
              <label for="name">Llanta</label>
              <ValidationProvider
                name="llanta"
                rules="required"
                v-slot="{ errors }"
              >
                <b-form-input
                  v-model="form.tires"
                  class="form-control-user form-control"
                  :class="errors[0] ? 'is-invalid' : ''"
                >
                </b-form-input>
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>

          </div>

          <div class="row">
            <div class="form-group col">
              <label for="name">Tipo</label>
              <ValidationProvider
                name="tipo"
                rules="required"
                v-slot="{ errors }"
              >
                <b-form-select
                  :options="typeData"
                  v-model="form.type_bicies_id"
                  class="form-control-user form-control"
                  :class="errors[0] ? 'is-invalid' : ''"
                >
                </b-form-select>
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>

            <div class="form-group col" data-content="Rasgos">
            <label for="name">Rasgos</label>
            <ValidationProvider
              name="caracteristicas"
              rules="max:200|min:5"
              v-slot="{ errors }"
            >
              <b-form-textarea
                v-model="form.description"
                class="form-control-user form-control p-0 m-0"
                style="height:35px"
                :class="errors[0] ? 'is-invalid' : ''"
                rows="1"
                max-rows="6"
              ></b-form-textarea>
              <span class="form-text text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
            </div>

            <div class="form-group col">
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
            
          </div>

          <div class="row" data-content="photo">
            <div class="form-group col" data-content="Fotografia col">
              <label for="file-default">Fotografía (Frontal)</label>
                <ValidationProvider
                  rules="image"
                  name="imagenes"
                  v-slot="{ errors }"
                >
                  <b-form-file
                    browse-text="Buscar"
                    id="file-default"
                    @change="addFile($event, 'front')"
                    v-model="form.image_front"
                    type="file"
                    class="form-control-user form-control mb-4"
                    :class="errors[0] ? 'is-invalid' : ''"
                  ></b-form-file>
                  <span class="form-text text-danger">{{ errors[0] }}</span>
                </ValidationProvider>
            </div>

            <div v-for="group in groupedFrontImages" class="row col" v-bind:key="group.id" >
                <div v-for="(img,index) in previewFrontImage" :key="index" class="col-4">
                  <div class="artist-collection-photo">
                    <button
                      class="close"
                      @click="deleteImage(index, 'front')"
                      type="button"
                    >
                      ×
                    </button>
                    <a data-target="#photo-fields-5-0" data-toggle="modal">
                      <img class="img-thumbnail col" :src="img" alt="" />
                    </a>
                  </div>
                </div>
              </div>
          </div>

          <div class="row" data-content="photo">
            <div class="form-group col" data-content="Fotografia col">
              <label for="file-default-back">Fotografía ( Atrás )</label>
                <ValidationProvider
                  rules="image"
                  name="imagenes"
                  v-slot="{ errors }"
                >
                  <b-form-file
                    browse-text="Buscar"
                    id="file-default-back"
                    @change="addFile($event, 'back')"
                    v-model="form.image_back"
                    type="file"
                    class="form-control-user form-control mb-4"
                    :class="errors[0] ? 'is-invalid' : ''"
                  ></b-form-file>
                  <span class="form-text text-danger">{{ errors[0] }}</span>
                </ValidationProvider>
            </div>

            <div v-for="group in groupedBackImages" class="row col" v-bind:key="group.id" >
                <div v-for="(img,index) in previewBackImage" :key="index" class="col-4">
                  <div class="artist-collection-photo">
                    <button
                      class="close"
                      @click="deleteImage(index, 'back')"
                      type="button"
                    >
                      ×
                    </button>
                    <a data-target="#photo-fields-5-0" data-toggle="modal">
                      <img class="img-thumbnail col" :src="img" alt="" />
                    </a>
                  </div>
                </div>
              </div>
          </div>
          <div class="row" data-content="photo">
            <div class="form-group col" data-content="Fotografia col">
              <label for="file-default-side">Fotografía ( Lateral )</label>
                <ValidationProvider
                  rules="image"
                  name="imagenes"
                  v-slot="{ errors }"
                >
                  <b-form-file
                    browse-text="Buscar"
                    id="file-default-side"
                    @change="addFile($event, 'side')"
                    v-model="form.image_side"
                    type="file"
                    class="form-control-user form-control mb-4"
                    :class="errors[0] ? 'is-invalid' : ''"
                  ></b-form-file>
                  <span class="form-text text-danger">{{ errors[0] }}</span>
                </ValidationProvider>
            </div>

            <div v-for="group in groupedSideImages" class="row col" v-bind:key="group.id" >
                <div v-for="(img,index) in previewSideImage" :key="index" class="col-4">
                  <div class="artist-collection-photo">
                    <button
                      class="close"
                      @click="deleteImage(index, 'side')"
                      type="button"
                    >
                      ×
                    </button>
                    <a data-target="#photo-fields-5-0" data-toggle="modal">
                      <img class="img-thumbnail col" :src="img" alt="" />
                    </a>
                  </div>
                </div>
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
import Fuse from "fuse.js";
import Swal from "sweetalert2";
import Select2 from 'v-select2-component';
import XLSX from 'xlsx'
import FileSaver from 'file-saver'

const VueSelect = require("vue-select2");

export default {
  components:{
    Select2,
    VueSelect

  },
  data() {
    return {
      previewFrontImage: [],
      previewBackImage: [],
      previewSideImage: [],
      parkingData: [{ value: null, text: "Selecciona una opcion" }],
      typeData: [{ value: null, text: "Selecciona una opcion" }],
      bikersData: [{ value: null, text: "Selecciona una opcion" }],
      form: {
        id: "",
        code: "",
        serial : "",
        parkings_id: null,
        description: "",
        brand: "",
        document: "",
        color: "",
        tires: "",
        type_bicies_id: null,
        active: null,
        image_front: null,
        image_back: null,
        image_side: null,
      },
      columns: [
        {
          label : "#",
          field: "_id"
        },
        {
          label: "Bici Estación",
          field: "parking",
        },
        {
          label: "Codigo",
          field: "code",
        },
        {
          label : "Serial",
          field: "serial"
        },
        {
          label: "Documento",
          field: "document",
        },
        {
          label: "Marca",
          field: "brand",
        },
        {
          label: "Color",
          field: "color",
        },
        {
          label: "Llanta",
          field: "tires",
        },
        {
          label: "Rasgos",
          field: "description",
        },
        {
          label: "Tipo",
          field: "type",
        },
        {
          label: "Fecha Registro",
          field: "date",
        },
        {
          label: "Hora Registro",
          field: "time",
        },
        {
          label: "Fecha Actualización",
          field: "dateUp",
        },
        {
          label: "Hora Actualización",
          field: "timeUp",
        },
        {
           label: "Foto 1",
           field: "url_image_back",
        },
        {
           label: "Foto 2",
           field: "url_image_side",
        },
        {
           label: "Foto 3",
           field: "url_image_front",
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
    fuseSearch(options, search) {
      const fuse = new Fuse(options, {
        keys: ["text"],
        shouldSort: true
      });
      return search.length
        ? fuse.search(search).map(({ item }) => item)
        : fuse.list;
    },
    bringBikeConsecutive(){
      if(!this.form.parkings_id){
        return;
      }

      if(!this.form.id){
        this.$api.get("web/data/bicy/" + this.form.parkings_id + "/create").then((res) => {
          console.log(res.data.response.data);
          this.form.code = res.data.response.data.consecutive;
        });
        console.log(this.form.parkings_id);
      }
    },
    myChangeEvent(val){
        console.log(val);
    },
    mySelectEvent({id, text}){
        console.log({id, text})
    },
    deleteImage(e , which) {
      switch(which.toLowerCase()){
        case 'front':
          this.previewFrontImage.splice(e, 1);
          this.form.image_front = null;
        break;
        case 'back':
          this.previewBackImage.splice(e, 1);
          this.form.image_back = null;
        break;
        case 'side':
          this.previewSideImage.splice(e, 1);
          this.form.image_side = null;
        break;
      }
    },
    addFile(e, which) {
      const file = e.target.files[0];

      switch(which.toLowerCase()){
        case 'front':
          this.previewFrontImage.push(URL.createObjectURL(file));
        break;
        case 'back':
          this.previewBackImage.push(URL.createObjectURL(file));
        break;
        case 'side':
          this.previewSideImage.push(URL.createObjectURL(file));
        break;
      }
    },
    exportBicies(){
      let wb =  JSON.parse(localStorage.getItem('tableBicies'))
      let wopts = {
        bookType: 'xlsx',
        bookSST: false,
        type: 'binary'
      }
      let wbout = XLSX.write(wb, wopts);
      FileSaver.saveAs(new Blob([this.s2ab(wbout)], {
      type: "application/octet-stream;charset=utf-8"
      }), "Bicies.xlsx");
        
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
    addData() {
      this.$bvModal.show("modal-bicy");
    },
    handleOk(bvModalEvt) {
      bvModalEvt.preventDefault();
      this.dataSubmit();
    },
    dataSubmit() {
      var data = new FormData();
      data.append("code", this.form.code);
      data.append("description", this.form.description);
      data.append("document", this.form.document);
      data.append("bikers_id", this.form.bikers_id);
      data.append("brand", this.form.brand);
      data.append("color", this.form.color);
      data.append("tires", this.form.tires);
      data.append("type_bicies_id", this.form.type_bicies_id);
      data.append("parkings_id", this.form.parkings_id);
      data.append("serial", this.form.serial);
      data.append("active", this.form.active);
      
      data.append("image_front", this.form.image_front);
      data.append("image_back", this.form.image_back);
      data.append("image_side", this.form.image_side);

      if (this.form.id) {
        this.$api.post("web/data/bicy/" + this.form.id, data).then((res) => {
          if (res.status == 200) {
            this.getData(false);
            toastr.success("Dato Actualizado");
            this.$bvModal.hide("modal-bicy");
          }
        });
      } else {
        this.$api
          .post("web/data/bicy", data, {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          })
          .then((res) => {
            if (res.status == 201) {
              this.getData(false);
              toastr.success("Dato Guardado");
              this.$bvModal.hide("modal-bicy");
            }
          });
      }
    },
    resetModal() {
      toastr.clear();
      this.form.id = "";
      this.form.code = "";
      this.form.document = "";
      this.form.description = "";
      this.form.brand = "";
      this.form.parkings_id = null;
      this.form.bikers_id = null;
      this.form.color = "";
      this.form.tires = "";
      this.form.serial = "";
      this.form.type_bicies_id = null;
      this.form.active = null;
      this.form.image_front = null;
      this.form.image_back = null;
      this.form.image_side = null;

      this.previewFrontImage = [];
      this.previewBackImage = [];
      this.previewSideImage = [];

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
            this.$api.delete("web/data/bicy/" + id).then((res) => {
              if (res.status == 200) {
                this.getData(false);
                toastr.success("Dato Eliminado");
                this.$bvModal.hide("modal-bicy");
              }
            });
          }
        });
    },
    editData(id) {
      this.previewFrontImage = [];
      this.$api.get("web/data/bicy/" + id).then((res) => {
        const data = res.data.response.data;
        const {image_front, image_back, image_side} = data;
        data.image_front = data.image_back = data.image_side = null;
        if(image_front){
          this.previewFrontImage.push(image_front);
        }
        if(image_back){
          this.previewBackImage.push(image_back);
        }
        if(image_side){
          this.previewSideImage.push(image_side);
        }

        this.form = data;
      });
      this.$bvModal.show("modal-bicy");
      
    },
    getData(option = false) {
      this.$api.get("web/data/bicy").then((res) => {
        this.rows = res.data.response.bicies.map(el => {
          el._id = `0000${el.id}`.substr(-4,4);
          [el.date,el.time] = el.created_at.split(' ');
          [el.dateUp,el.timeUp] = el.updated_at.split(' ');
          return el;
        });
        if (option == true) {

          this.parkingData = [{ value: null, text: "Selecciona una opcion" }].concat( res.data.response.indexes.parking.map(el => el) );
          this.typeData = [{ value: null, text: "Selecciona una opcion" }].concat( res.data.response.indexes.type.map(el => el) );
          
        }
      }).finally(function() {  
        let element = document.getElementById("tableBicies")
        let wb = XLSX.utils.table_to_book(element)
        localStorage.setItem("tableBicies", JSON.stringify(wb))
      });
      this.$api.get("web/data/biker").then((res) => {
        const data = [];
        for(let biker of res.data.response.users){
          data.push({value : biker.document, text :  `${biker.document} ${biker.name}  ${biker.last_name}`})
        }
        this.bikersData = data;
      });
      
    },
    rowStyleClassFn(row){
      return row.active == 1 ? '' : 'alert alert-danger'
    }
  },
  created: function () {
    this.getData(true);
  },
  computed: {
    groupedFrontImages() {
      return _.chunk(this.previewFrontImage, 3);
    },
    groupedBackImages() {
      return _.chunk(this.previewBackImage, 3);
    },
    groupedSideImages() {
      return _.chunk(this.previewSideImage, 3);
    },
  },
};
</script>
<style>
.artist-collection-photo {
  float: left;
  margin: 10px;
  cursor: pointer;
  width: 120px;
  height: 120px;
  position: relative;
}

.close {
  position: absolute;
  top: 0;
  right: 0;
  z-index: 9999;
}

.img-thumbnail {
  object-fit: cover;
  opacity: 1;
  transition: opacity 0.25s ease-in-out;
  -moz-transition: opacity 0.25s ease-in-out;
  -webkit-transition: opacity 0.25s ease-in-out;
}

#selectt, #selectt *{
  min-width:300px !important;
}
.img-thumbnail:hover {
  opacity: 0.5;
}
</style>