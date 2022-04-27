<template>
  <div class="wrapper">
    <div>
      <vue-good-table
        ref="recordsTable"
        :columns="columns"
        :rows="rows"
        :search-options="{ enabled: true }"
        :pagination-options="{ enabled: true,perPage }"
        :select-options="{ enabled: true}"
        :row-style-class="rowStyleClassFn"
        @on-selected-rows-change="selectionChanged"
      >
        <div slot="table-actions">
          <!-- <span>Registros por página</span>
          <b-input v-model="perPage"/> -->

          <button v-on:click="print()" :disabled="!selectedOrders.length" class="btn btn-primary">
            Imprimir
          </button>
          <button v-on:click="addData()" class="btn btn-primary">
            A&ntilde;adir
          </button>
        </div>
        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'id' ">
            <!-- <div class="btn btn-info text-light" @click="print(props.formattedRow[props.column.field])">Print me</div> -->
            <div class="btn btn-info text-light" @click="editData(props.formattedRow[props.column.field])">Detalle</div>
          </span>
          <span v-else>
            {{ props.formattedRow[props.column.field] }}
          </span>
          
        </template>
      </vue-good-table>
    </div>


    <b-modal hide-footer id="addDataModel" :size="editing ? 'xl' : ''" ref="modal" title="Crear Orden de Stickers" 
         ok="handleOk" @show="resetModal" @hidden="resetModal" >

      <ValidationObserver ref="observer" v-slot="{ handleSubmit }">
        <form ref="form" @submit.prevent="handleSubmit(dataSubmit)">
          
          <div class="row">
            
            <div class="form-group col" data-content="Bicicleta">
              <label>Bicicleta</label>
             
              <ValidationProvider
                name="bicicletas"
                rules="required"
                v-slot="{ errors }"
              >
                <v-select v-model="form.bicies_id" :options="biciesData" :reduce="bicy => bicy.value" label="text"/>
                
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
import 'vue-select/dist/vue-select.css';
export default {

  data(){
    return {
      editing: false,
      perPage:10,
      parkingsData : [],
      biciesData : [],
      selectedOrders : [],
      form : {
        id: null,
        bicies_id : null
      },
      rows : [],
      columns : [
        {
          label : "Bici Estación",
          field : "parking",
        },
        {
          label : "Bicicleta",
          field : "bicy_code",
        },
        {
          label : "Cédula",
          field : "biker_document",
        },
        {
          label : "Fecha solicitud",
          field : "date",
        },
        // {
        //   label : "Controles",
        //   field : "id",
        // }
      ]
    }
  },
  methods : {
    getData(){
      this.$api.get('web/data/Dstickers').then(res =>{
        if(res.status == 200){
          this.parkingsData = [{value: null, text : 'Seleccione uno'}].concat(res.data.response.indexes.parkings
            .map(el => { return {value: el.id , text : el.name}}));
          this.biciesData = [{value: null, text : 'Seleccione uno'}].concat(res.data.response.indexes.bicies
            .map(el => { return {value: el.id , text : el.code}}));
          this.rows = res.data.response.data.map(el => {
            const parking = this.parkingsData.find(_el => el.parkings_id == _el.value);
            el.parking = parking.text;
            el.date = el.created_at.split('T')[0];
            return el;
          });

        }else{
          toastr.error('Se ha presentado un problema al consultar la información');
        }
      })
    },
    selectionChanged(){
      this.selectedOrders = this.$refs['recordsTable'].selectedRows.map(el => el.id );
    },
    print(){
      let routeData = this.$router.resolve({name: 'printDQr', query: {data: this.selectedOrders}});
      window.open(routeData.href, '_blank');
    },
    resetModal(){
      this.form.bicies_id = null;
      this.form.id = null;
    },
    addData(){
      this.editing = false;
      this.$bvModal.show('addDataModel');
    },
    dataSubmit(){

        this.$api.post('web/data/Dstickers', {bicies_id : this.form.bicies_id}).then(res =>{
        if(res.status == 201){
          this.getData();
          toastr.success('Orden registrada.')
          this.$bvModal.hide('addDataModel')
        }else{
          toastr.error('Se ha presentado un error al guardar el registro.');
        }
      });
      
    },
    
    rowStyleClassFn(row){
      return row.active == 1 ? '' : 'alert alert-danger'
    },
    displayRecovPaswModal(){
      this.$bvModal.show('addDataModel')
    },
    closeModal(){
      this.$bvModal.hide('addDataModel')
    }
  },
  created(){
    this.getData();
  }

}
</script>