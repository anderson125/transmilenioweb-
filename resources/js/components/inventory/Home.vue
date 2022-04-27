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
        <b-tab title="Inventarios" active>
          <div v-if="tabComponent == 0">
            <InventoryComponent />
          </div>
        </b-tab>
      </b-tabs>
    </b-card>
  </div>
</template>
<script>
import InventoryComponent from "./Inventory";

export default {
  components: {
    InventoryComponent,
  },
  data() {
    return {
      tabComponent: null,
    };
  },
};
</script>
