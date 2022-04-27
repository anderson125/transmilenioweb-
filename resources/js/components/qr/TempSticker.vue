<template>
  <div class="wrapper">

    <div>
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
          
          <div :class="editing?'row':''">
            <div class="form-group col" data-content="Cicloparqueadero">
              <label for="name">Bici Estación</label>
              <ValidationProvider
                name="cicloparqueadero"
                rules="required"
                v-slot="{ errors }"
              >
                <b-form-select
                  :options="parkingsData"
                  :disabled="editing"
                  v-model="form.parkings_id"
                  class="form-control-user form-control"
                  :class="errors[0] ? 'is-invalid' : ''"
                >
                </b-form-select>
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>
            <div class="form-group col" data-content="Cantidad">
              <label for="name">Cantidad</label>
              <ValidationProvider
                name="cantidad"
                rules="required"
                v-slot="{ errors }"
              >
                <b-form-input
                  type="number" min="1" :disabled="editing" v-model="form.quantity"
                  class="form-control-user form-control" :class="errors[0] ? 'is-invalid' : ''"
                >
                </b-form-input>
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>

            <div class="form-group col" v-if="editing" data-content="Cantidad">
              <label for="printed">Impresos</label>
              <ValidationProvider
                name="cantidad impresa"
                rules="required"
                v-slot="{ errors }"
              >
                <b-form-input
                  type="number" min="1" :disabled="editing" v-model="form.printed"
                  class="form-control-user form-control" :class="errors[0] ? 'is-invalid' : ''"
                >
                </b-form-input>
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>
          </div>
          <div v-if="editing">
            <div class="form-group col" data-content="Imprimir">
              <label for="print">Imprimir <small>Máximo ({{form.quantity-form.printed}})</small></label>
              <ValidationProvider
                name="cantidad a imprimir"
                rules="required"
                v-slot="{ errors }"
              >
                <b-form-input id="print"
                  type="number" min="1" :max="form.quantity-form.printed" v-model="form.print"
                  class="form-control-user form-control" :class="errors[0] ? 'is-invalid' : ''"
                >
                </b-form-input>
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
      parkingsData : [],
      form : {
        id: null,
        parkings_id: null,
        final_consecutive : null,
        initial_consecutive : null,
        print : 0,
        printed : 0,
        quantity : 1,
        parking_code : null,
      },
      rows : [],
      columns : [
        {
          label : "Bici Estación",
          field : "parking",
        },
        {
          label : "Cantidad",
          field : "quantity",
        },
        {
          label : "No impresos",
          field : "printed",
        },
        {
          label : "Consecutivo actual",
          field : "current_consecutive",
        },
        {
          label : "Controles",
          field : "id",
        }
      ]
    }
  },
  methods : {
    getData(){
      this.$api.get('web/data/stickers').then(res =>{
        if(res.status == 200){
          this.parkingsData = [{value: null, text : 'Seleccione uno'}].concat(res.data.response.indexes.parkings
            .map(el => { return {value: el.id , text : el.name}}));
          this.rows = res.data.response.data.map(el => {
            const parking = this.parkingsData.find(_el => el.parkings_id == _el.value);
            el.current_consecutive = el.initial_consecutive + el.printed;
            el.parking = parking.text;
            return el;
          });
        }else{
          toastr.error('Se ha presentado un problema al consultar la información');
        }
      })
    },
    editData(id){
      this.editing = true;
      this.$api.get('web/data/stickers/'+id).then(res =>{
        if(res.status == 200){
          this.$bvModal.show('addDataModel');
          const data = res.data.response.data;
          console.log({data});
          this.form.parkings_id = data.parkings_id;
          this.form.quantity = data.quantity;
          this.form.id = data.id;
          this.form.final_consecutive = data.final_consecutive;
          this.form.printed = data.printed;
          this.form.initial_consecutive = data.initial_consecutive;
          this.form.parking_code = data.parking_code;
          
        }else{
          toastr.error('Se ha presentado un problema al consultar la información');
        }
      })
    },
    print(num){
      let routeData = this.$router.resolve({name: 'printQr', query: {data: "someData"+num}});
      window.open(routeData.href, '_blank');
    },
    resetModal(){
      this.form.parkings_id = null;
      this.form.quantity = 1;
      this.form.print = 0;

      this.form.id = null;
      this.form.final_consecutive = null;
      this.form.initial_consecutive = null;
      this.form.printed = 0;
      this.form.parking_code = null;

    },
    addData(){
      this.editing = false;
      this.$bvModal.show('addDataModel');
    },
    dataSubmit(){
      
      if(this.editing || this.form.id){
        this.$api.put('web/data/stickers/'+this.form.id, { printed : this.form.print }).then(res =>{
          if(res.status == 200){
            let routeData = this.$router.resolve({name: 'printQr', 
              query: {parkingCode: this.form.parking_code, initial_consecutive: this.form.initial_consecutive, print : this.form.print }
            });
            window.open(routeData.href, '_blank');
            this.getData(); toastr.success('Orden registrada.'); this.$bvModal.hide('addDataModel');
          }else{
            toastr.error('Se ha presentado un error al guardar el registro.');
          }
        });
      
      }else{
      
        const form = {
          parkings_id : this.form.parkings_id,
          quantity : this.form.quantity,
        };

        this.$api.post('web/data/stickers', form).then(res =>{
        if(res.status == 201){
          this.getData();
          toastr.success('Orden registrada.')
          this.$bvModal.hide('addDataModel')
        }else{
          toastr.error('Se ha presentado un error al guardar el registro.');
        }
      });
      
      }
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