<template>
  <div id="tableBiker">
    <!-- DATATABLE -->
    <vue-good-table
      :columns="columns"
      :rows="rows"
      :search-options="{ enabled: true }"
      :pagination-options="{ enabled: true }"
    >
      <div slot="table-actions">
        <button v-on:click="addData()" class="btn btn-primary">
          A&ntilde;adir
        </button>
        <label for="file-upload" class="btn btn-success my-auto">
          Importar
        </label>
        <button v-on:click="exportBikers()" class="btn btn-success">
          Exportar
        </button>
        <input id="file-upload" class="d-none" type="file" />
      </div>
      <template id="tableBiker" slot="table-row" slot-scope="props">
        <span v-if="props.column.field === 'active'">
          <div v-if="props.row.active == 1">Activo</div>
          <div v-else-if="props.row.active == 2">Inactivo</div>
          <div v-else>Bloqueado</div>
        </span>
        <span v-else-if="props.column.field === 'auth'">
          <div v-if="props.row.auth == 1">Si</div>
          <div v-else>No</div>
        </span>
          <span v-else-if="props.column.field === 'url_img'">
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
            <div class="form-group col" data-content="Nombre">
              <label for="name">Nombre</label>
              <ValidationProvider
                name="nombre"
                rules="required|min:2|max:100"
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
              rules="required|min:2|max:100"
              v-slot="{ errors }"
            >
              <input
                v-model="form.last_name"
                type="text"
                class="form-control-user form-control"
                :class="errors[0] ? 'is-invalid' : ''"
              />
              <span class="form-text text-danger">{{ errors[0] }}</span>
            </ValidationProvider>

            </div>
            <div class="form-group col" data-content="Fecha de Nacimiento">
              <label for="name">Fecha de Nacimiento</label>
              <ValidationProvider
                name="fecha de nacimiento"
                rules="required"
                v-slot="{ errors }"
              >
                <datepicker
                  :bootstrap-styling="true"
                  :language="es"
                  :calendar-button="true"
                  calendar-button-icon="fa fa-calendar"
                  format="yyyy MMM dd"
                  :full-month-name="true"
                  v-model="form.birth"
                  :input-class="
                    errors[0]
                      ? 'form-control-user form-control is-invalid'
                      : 'form-control-user form-control'
                  "
                />

                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>
          </div>

          <div class="row">
            <div class="form-group col" data-content="Tipo de Documento">
              <label for="name">Tipo de Documento</label>
              <ValidationProvider
                name="tipo de documento"
                rules="required"
                v-slot="{ errors }"
              >
                <b-form-select
                  :options="typeData"
                  v-model="form.type_documents_id"
                  class="form-control-user form-control"
                  :class="errors[0] ? 'is-invalid' : ''"
                >
                </b-form-select>
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>
            <div class="form-group col" data-content="Documento">
            <label for="name">Documento</label>
            <ValidationProvider
              name="documento"
              rules="required|min:4|max:20|numeric"
              v-slot="{ errors }"
            >
              <input
                v-model="form.document"
                type="number"
                class="form-control-user form-control"
                :class="errors[0] ? 'is-invalid' : ''"
              />
              <span class="form-text text-danger">{{ errors[0] }}</span>
            </ValidationProvider>
            </div>
            <div class="form-group col" data-content="Genero">
              <label for="name">Género</label>
              <ValidationProvider
                name="genero"
                rules="required"
                v-slot="{ errors }"
              >
                <b-form-select
                  :options="genderData"
                  v-model="form.genders_id"
                  class="form-control-user form-control"
                  :class="errors[0] ? 'is-invalid' : ''"
                >
                </b-form-select>
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>
          </div>

          <div class="row">
            <div class="form-group col" data-content="Correo">
              <label for="name">Correo</label>
              <ValidationProvider
                name="correo"
                rules="required|email|max:60|min:6"
                v-slot="{ errors }"
              >
                <input
                  v-model="form.email"
                  type="email"
                  class="form-control-user form-control"
                  :class="errors[0] ? 'is-invalid' : ''"
                />
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>

            <div class="form-group col" data-content="Telefono">
              <label for="name">Teléfono</label>
              <ValidationProvider
                name="telefono"
                rules="required|numeric|max:10|min:7"
                v-slot="{ errors }"
              >
              <div class="row">
                <input
                  v-model="form.phone"
                  type="number"
                  class="form-control-user form-control offset-1 col-8"
                  :class="errors[0] ? 'is-invalid' : ''"
                />
                <div class="btn btn-info btn-sm text-light fa fa-envelope col-2" v-show="!errors.length && form.phone.length" @click="sendMessage()"></div>
              </div>
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>

          <div class="form-group col" data-content="Confirmacion SMS">
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
          </div>

          <div class="row">
            <div class="form-group col" data-content="Ocupacion">
              <label for="name">Ocupación</label>
              <ValidationProvider
                name="ocupacion"
                rules="required"
                v-slot="{ errors }"
              >
                <b-form-select
                  :options="jobData"
                  v-model="form.jobs_id"
                  class="form-control-user form-control"
                  :class="errors[0] ? 'is-invalid' : ''"
                >
                </b-form-select>
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>
            <div class="form-group col" data-content="Barrio">
              <label for="name">Barrio</label>
              <ValidationProvider
                name="barrio"
                rules="required"
                v-slot="{ errors }"
              >
                <input
                  v-model="form.neighborhoods_id"
                  type="text"
                  class="form-control-user form-control"
                  :class="errors[0] ? 'is-invalid' : ''"
                />
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>
            <div class="form-group col" data-content="Estrato">
              <label for="name">Estrato</label>
              <ValidationProvider
                name="estrato"
                rules="required"
                v-slot="{ errors }"
              >
                <b-form-select
                  :options="levelData"
                  v-model="form.levels_id"
                  class="form-control-user form-control"
                  :class="errors[0] ? 'is-invalid' : ''"
                >
                </b-form-select>
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>
          </div>

          <div class="row">


            <div class="form-group col" data-content="Parqueadero">
              <label for="parkings_id">Parqueadero</label>
              <ValidationProvider
                name="Parqueadero"
                rules="required"
                v-slot="{ errors }"
              >
                <b-form-select
                  :options="parkingsData"
                  v-model="form.parkings_id"
                  class="form-control-user form-control"
                  :class="errors[0] ? 'is-invalid' : ''"
                >
                </b-form-select>
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>

            <div class="form-group col" data-content="Estado">
              <label for="status">Estado</label>
              <ValidationProvider
                rules="required"
                name="estado"
                v-slot="{ errors }"
              >
                <select
                  id="status"
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

            <div class="form-group col" data-content="Autorizacion">
              <label for="auth">Autorizado/a(Mayor de edad)</label>
              <ValidationProvider
                rules="required"
                name="autorizacion"
                v-slot="{ errors }"
              >
                <select
                  id="auth"
                  v-model="form.auth"
                  class="form-control-user form-control"
                  :class="errors[0] ? 'is-invalid' : ''"
                >
                  <option :value="null">Seleccione una opcion</option>
                  <option value="1">Si</option>
                  <option value="2">No</option>
                </select>
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>

          </div>

          <div class="row">
            <div class="form-group col" data-content="Fotografia col">
              <label for="file-default">Fotografía</label>
                <ValidationProvider
                  rules="image"
                  name="imagenes"
                  v-slot="{ errors }"
                >
                  <b-form-file
                    browse-text="Buscar"
                    id="file-default"
                    @change="addFile"
                    v-model="form.photo"
                    type="file"
                    class="form-control-user form-control mb-4"
                    :class="errors[0] ? 'is-invalid' : ''"
                  ></b-form-file>
                  <span class="form-text text-danger">{{ errors[0] }}</span>
                </ValidationProvider>



            </div>
            <div
                v-for="group in groupedImages"
                class="row col"
                v-bind:key="group.id"
              >
                <div v-for="(img,index) in previewImage" :key="index" class="col-4">
                  <div class="artist-collection-photo">
                    <button
                      class="close"
                      @click="deleteImage(index)"
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
  import Swal from "sweetalert2";
  import Datepicker from "vuejs-datepicker";
  import { en, es } from "vuejs-datepicker/dist/locale";
  import XLSX from 'xlsx'
  import FileSaver from 'file-saver'

  export default {
    components: {
      Datepicker,
    },
    data() {

      return {
        es: es,
        previewImage: [],
        validateImage: null,
        typeData: [{ value: null, text: "Selecciona una opcion" }],
        genderData: [{ value: null, text: "Selecciona una opcion" }],
        jobData: [{ value: null, text: "Selecciona una opcion" }],
        parkingsData: [{ value: null, text: "Selecciona una opcion" }],
        levelData: [
          { value: null, text: "Selecciona una opcion" },
          { value: "1", text: "Estrato 1" },
          { value: "2", text: "Estrato 2" },
          { value: "3", text: "Estrato 3" },
          { value: "4", text: "Estrato 4" },
          { value: "5", text: "Estrato 5" },
          { value: "6", text: "Estrato 6" },
        ],
        form: {
          id: "",
          name: "",
          last_name: "",
          auth: null,
          // birth: new Date().toLocaleDateString("en-CA"),
          birth: null,
          confirmation: "",
          document: "",
          email: "",
          genders_id: null,
          jobs_id: null,
          levels_id: null,
          neighborhoods_id: null,
          parkings_id: null,
          phone: "",
          photo: null,
          register: new Date().toLocaleDateString("en-CA"),
          type_documents_id: null,
          active: null,
        },
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
            label: "Género",
            field: "gender",
          },
          {
            label: "Teléfono",
            field: "phone",
          },
          {
            label: "Correo",
            field: "email",
          },
          {
            label: "Ocupación",
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
            label: "Foto",
            field: "url_img",
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
      resetSelects(){
        this.typeData = [{ value: null, text: "Selecciona una opcion" }];
        this.genderData = [{ value: null, text: "Selecciona una opcion" }];
        this.jobData = [{ value: null, text: "Selecciona una opcion" }];
        this.parkingsData = [{ value: null, text: "Selecciona una opcion" }];
      },
      deleteImage(e) {
        this.previewImage.splice(e, 1);
        this.form.photo = null;
      },
      addFile(e) {
        const file = e.target.files[0];
        this.form.photo = file;
        this.previewImage.push(URL.createObjectURL(file));
        if (this.previewImage) {
          this.validateImage = file;
        }
      },
      addData() {
        this.$bvModal.show("modal-biker");
      },
      exportBikers(){
        let wb =  JSON.parse(localStorage.getItem('table'))
        let wopts = {
          bookType: 'xlsx',
          bookSST: false,
          type: 'binary'
        }
        let wbout = XLSX.write(wb, wopts);
        FileSaver.saveAs(new Blob([this.s2ab(wbout)], {
          type: "application/octet-stream;charset=utf-8"
          }), "Bikers.xlsx");

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
      handleOk(bvModalEvt) {
        bvModalEvt.preventDefault();
        this.dataSubmit();
      },
      sendMessage(){
        this.$api.get("web/data/biker/verificationCode/" + this.form.phone).then((res) => {
          if(res.status == 200){
            toastr.success("Código de verificación enviado");
          }else{
            toastr.error("Error inesperado");
          }
        });
        this.$bvModal.show("modal-biker");
      },
      dataSubmit() {
        var data = new FormData();

        let birthDate = "";

        if(typeof this.form.birth == 'object'){
          birthDate = this.form.birth.toISOString();
          birthDate = birthDate.split('T')[0];
        }else{
          birthDate = this.form.birth;
        }

        // const level = this.form.levels_id.substring(8,9);

        data.append("name", this.form.name);
        data.append("lastName", this.form.last_name);
        data.append("auth", this.form.auth);
        data.append("birth", birthDate);
        data.append("confirmation", this.form.confirmation);
        data.append("document", this.form.document);
        data.append("email", this.form.email);
        data.append("gender", this.form.genders_id);
        data.append("job", this.form.jobs_id);
        data.append("level", this.form.levels_id);
        data.append("neighborhood", this.form.neighborhoods_id);
        data.append("parkings_id", this.form.parkings_id);
        data.append("phone", this.form.phone);
        data.append("register", this.form.register);
        data.append("type", this.form.type_documents_id);
        data.append("active", this.form.active);
        if(this.form.photo){data.append("photo", this.form.photo);}

        if (this.form.id) {
          this.$api.post("web/data/biker/" + this.form.id, data).then((res) => {
            if (res.status == 200) {
              this.getData();
              toastr.success("Dato Actualizado");
              this.$bvModal.hide("modal-biker");
            }
          });
        } else {
          // for (var i = 0; i < this.form.photo.length; i++) {
          //   let file = this.form.photo[i];
          //   data.append("photo[" + i + "]", file);
          // }
          this.$api
            .post("web/data/biker", data, {
              headers: {
                "Content-Type": "multipart/form-data",
              },
            })
            .then((res) => {
              if (res.status == 201) {
                console.log(res);
                this.getData();
                toastr.success("Dato Guardado");
                this.$bvModal.hide("modal-biker");
              }
            });
        }

      },
      resetModal() {
        toastr.clear();
        this.form.id = "";
        this.form.name = "";
        this.form.active = null;
        this.form.last_name = "";
        this.form.auth = null;
        this.form.birth = new Date().toLocaleDateString("en-CA");
        this.form.confirmation = "";
        this.form.document = "";
        this.form.email = "";
        this.form.genders_id = null;
        this.form.jobs_id = null;
        this.form.levels_id = null;
        this.form.neighborhoods_id = null;
        this.form.parkings_id = null;
        this.form.phone = "";
        this.form.register = new Date().toLocaleDateString("en-CA");
        this.form.type_documents_id = null;
        this.form.photo = [];
        this.previewImage = [];
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
      editData(id) {
        this.previewImage = [];
        this.$api.get("web/data/biker/" + id).then((res) => {
          const data = res.data.response.data;

          if(data.photo && data.photo.length){
            this.previewImage.push(data.photo);
          }

          data.birth = `${data.birth} 00:00` ;
          data.photo = null;
          this.form = data;

        });
        this.$bvModal.show("modal-biker");
      },
      getData() {
        this.$api.get("web/data/biker").then((res) => {
          this.rows = res.data.response.users;

          this.resetSelects();
          res.data.response.indexes.type.forEach((element) => {
            this.typeData.push(element);
          });
          res.data.response.indexes.gender.forEach((element) => {
            this.genderData.push(element);
          });
          res.data.response.indexes.job.forEach((element) => {
            this.jobData.push(element);
          });

          res.data.response.indexes.parkings.forEach((element) => {
            this.parkingsData.push(element);
          });
        }).finally(function() {
          let element = document.getElementById("tableBiker")
          let wb = XLSX.utils.table_to_book(element)
          localStorage.setItem("table", JSON.stringify(wb))
        });
      },
    },

    created: function () {
      this.getData();
    },
    computed: {
      groupedImages() {

        return _.chunk(this.previewImage, 3);
        // returns a nested array:
        // [[article, article, article], [article, article, article], ...]
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

.img-thumbnail:hover {
  opacity: 0.5;
}
</style>
