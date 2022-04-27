<template>
<div>
  
  <div class="row">

    <div class="col-3 text-center" style="background-color:#fff;width:fit-content" v-for="n in parseInt(quantity)" :key="n">
        <div class="container border border-dark" >
          <div class="" style="display:inline-block">
            <img :src="'https://chart.googleapis.com/chart?cht=qr&chs=140x140&chl='+ JSON.stringify({code : data.parkingCode+(`0000${data.initial_consecutive - 1 + n}`.substr(-4,4))})  +'&chld=1|2'">
          </div>
          <div class="" style="display:inline-block">
            <div class="text-center font-weight-bold" style="font-size:1em">{{ data.parkingCode + `0000${data.initial_consecutive - 1 + n}`.substr(-4,4)}}</div>
            <img :src="'../storage/files/vectoring.png'">
          </div>
        </div>
    </div>

  </div>

</div>

</template>
<script>
export default {

  data(){
    return {
      data : this.$route.query,
      quantity : this.$route.query.print
    }
  },
  created(){
    setTimeout(()=>{
      document.querySelectorAll('#accordionSidebar,.navbar').forEach((el)=>{ el.classList.add('d-none')});
    },1)
  }
  

}
</script>