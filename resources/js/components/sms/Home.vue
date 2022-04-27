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
        <b-tab title="Parqueaderos" active>
          <div v-if="tabComponent == 0">
            <SmsComponent />
          </div>
        </b-tab>
      </b-tabs>
    </b-card>
  </div>
</template>
<script>
import SmsComponent from "./Sms";

export default {
  components: {
    SmsComponent,
  },
  data() {
    return {
      tabComponent: null,
    };
  },
};
</script>
