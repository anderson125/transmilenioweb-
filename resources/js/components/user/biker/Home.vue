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
        <b-tab title="Ciclistas">
          <div v-if="tabComponent == 0" active>
            <BikerComponent />
          </div>
        </b-tab>
        <b-tab title="Tipo de Documentos">
          <div v-if="tabComponent == 1">
            <TypeDocumentComponent />
          </div>
        </b-tab>
        <b-tab title="Generos">
          <div v-if="tabComponent == 2">
            <GenderComponent />
          </div>
        </b-tab>
        <b-tab title="Ocupaciones">
          <div v-if="tabComponent == 3">
            <JobComponent />
          </div>
        </b-tab>
        <b-tab title="Barrios">
          <div v-if="tabComponent == 4">
            <NeighborhoodComponent />
          </div>
        </b-tab>
        <b-tab title="Almacén Masivo">
          <div v-if="tabComponent == 5">
            <MasiveStoreComponent />
          </div>
        </b-tab>
        <b-tab title="Mensajería Masiva">
          <div v-if="tabComponent == 6">
            <MassiveSMSComponent />
          </div>
        </b-tab>
      </b-tabs>
    </b-card>
  </div>
</template>
<script>
import TypeDocumentComponent from "./Type";
import GenderComponent from "./Gender";
import JobComponent from "./Job";
import NeighborhoodComponent from "./Neighborhood";
import LevelComponent from "./Level";
import BikerComponent from "./Biker";
import MasiveStoreComponent from "./MasiveStore";
import MassiveSMSComponent from "./MassiveSMS";

export default {
  components: {
    TypeDocumentComponent,
    GenderComponent,
    JobComponent,
    NeighborhoodComponent,
    LevelComponent,
    BikerComponent,
    MasiveStoreComponent,
    MassiveSMSComponent,
  },
  data() {
    return {
      tabComponent: null,
    };
  },
};
</script>
