<template>
  <!-- Approach -->
  <div>
    <b-card no-body class="shadow mb-4">
      <b-tabs
        v-model="tabComponent"
        card
        nav-class="font-weight-bold"
        active-nav-item-class="font-weight-bold text-danger"
        content-class="mt-3"
      >
        <b-tab title="Codigo Individual" active>
          <div v-if="tabComponent == 0">
            <DetailedStickerComponent />
          </div>
        </b-tab>
        <b-tab title="Stickers Provisionales">
          <div v-if="tabComponent == 1">
            <TempStickerComponent />
          </div>
        </b-tab>
      </b-tabs>
    </b-card>
  </div>
</template>
<script>
import DetailedStickerComponent from "./DetailedSticker";
import TempStickerComponent from "./TempSticker";
export default {
  components: {
    DetailedStickerComponent,
    TempStickerComponent,
  },
  data() {
    return {
      tabComponent: null,
    };
  },
};
</script>
