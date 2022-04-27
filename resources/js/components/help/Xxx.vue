<template>
  <div class="wrapper">
    <div class="text-center">
      <iframe width="560" height="315" src="https://www.youtube.com/embed/jNQXAC9IVRw" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
    </div>
    <div>
      Descargas <br>
      <a
        v-for="(item, key) of downloads"
        :key="key"
        :href="item.url"
        v-text="item.label"
        @click.prevent="downloadItem(item)" />
    </div>
  </div>

</template>

<script>
export default {

  data(){
    return {
      downloads : [
        {
          url : '../storage/files/Home.txt',
          label : 'Home file',
          type : 'text/plain'
          // type: 'application/pdf' 
        }
      ]
    }
  },
  methods :{
    downloadItem ({ url, label, type }) {
      axios.get(url, { responseType: 'blob' })
        .then(response => {
          const blob = new Blob([response.data], { type })
          const link = document.createElement('a')
          link.href = URL.createObjectURL(blob)
          link.download = label
          link.click()
          URL.revokeObjectURL(link.href)
        }).catch(console.error)
    }
  }

}
</script>