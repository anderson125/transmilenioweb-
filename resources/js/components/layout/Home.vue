<template>
  <div id="wrapper">
    <!-- change: color -->
    <ul
      class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion"
      id="accordionSidebar"
    >
      <!-- Sidebar - Brand -->
      <a
        class="sidebar-brand d-flex align-items-center justify-content-center"
        href="home"
      >
        <div class="sidebar-brand-icon"></div>
        <!-- change: nombre -->
        <div class="sidebar-brand-text mx-3">{{ nameApp }}</div>
      </a>
      <!-- Divider -->
      <hr class="sidebar-divider my-0" />
      <!-- Nav Item - Dashboard -->

      <!-- Nav Item - Utilities Collapse Menu -->

      <!-- Divider -->
      <hr class="sidebar-divider" />
      <!-- Heading -->
      <div class="sidebar-heading">Módulos</div>
      <!-- Nav Item -  -->

      <li class="nav-item">
        <router-link class="nav-link collapsed" :to="{ name: 'parking' }">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Bici Estación</span>
        </router-link>
      </li>

      <li class="nav-item">
        <a
          class="nav-link collapsed"
          data-toggle="collapse"
          data-target="#collapseLogs_Records"
          aria-expanded="true"
          aria-controls="collapseLogs_Records"
        >
          <i class="fas fa-fw fa-wrench"></i>
          <span>Registros</span>
        </a>
        <div
          id="collapseLogs_Records"
          class="collapse"
          aria-labelledby="headingUtilities"
          data-parent="#accordionSidebar"
        >
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header text-center">-o-</h6>

            <router-link class="collapse-item" :to="{ name: 'bicy' }"> Bicicletas </router-link>
            <router-link class="collapse-item" :to="{ name: 'user.biker' }">Ciclistas</router-link>
          </div>
        </div>
      </li>

      <li class="nav-item">
        <router-link class="nav-link collapsed" :to="{ name: 'young' }">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Autorizaciones</span>
        </router-link>
      </li>

      <li class="nav-item">
        <router-link class="nav-link collapsed" :to="{ name: 'visit' }">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Visitas</span>
        </router-link>
      </li>

      <li class="nav-item">
        <router-link class="nav-link collapsed" :to="{ name: 'inventory' }">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Inventario</span>
        </router-link>
      </li>
      
      
      


      


      <!--<li class="nav-item" v-if="debug">
        <router-link class="nav-link collapsed" :to="{ name: 'permissions' }">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Permisos</span>
        </router-link>
      </li>-->

      <!-- Divider -->
      <hr class="sidebar-divider" />
      <!-- Heading -->
      <div class="sidebar-heading">Entidad</div>
      <!-- Nav Item -  -->
      <!-- <li class="nav-item">
        <router-link class="nav-link collapsed" :to="{ name: 'sms' }">
          <i class="fas fa-store-alt"></i>
          <span>Sms</span>
        </router-link>
      </li> -->
      <li class="nav-item">
        <router-link class="nav-link collapsed" :to="{ name: 'report' }">
          <i class="fas fa-store-alt"></i>
          <span>Reportes</span>
        </router-link>
      </li>
      <li class="nav-item">
        <router-link class="nav-link collapsed" :to="{ name: 'qr' }">
          <i class="fas fa-store-alt"></i>
          <span>Generar Código QR</span>
        </router-link>
      </li>
      <li class="nav-item">
        <router-link class="nav-link collapsed" :to="{ name: 'help' }">
          <i class="fas fa-store-alt"></i>
          <span>Consultas</span>
        </router-link>
      </li>

      <li class="nav-item">
        <router-link class="nav-link collapsed" :to="{ name: 'user.web' }">
          <i class="fas fa-store-alt"></i>
          <span>Administradores</span>
        </router-link>
      </li>

      <!-- Nav Item - Utilities Collapse Menu -->
      <!-- <li class="nav-item">
        <a
          class="nav-link collapsed"
          data-toggle="collapse"
          data-target="#collapseUtilities"
          aria-expanded="true"
          aria-controls="collapseUtilities"
        >
          <i class="fas fa-fw fa-wrench"></i>
          <span>Usuarios</span>
        </a>
        <div
          id="collapseUtilities"
          class="collapse"
          aria-labelledby="headingUtilities"
          data-parent="#accordionSidebar"
        >
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Configuraci&oacute;n</h6>
            <router-link class="collapse-item" :to="{ name: 'user.web' }"
              >Administradores</router-link
            >
          </div>
        </div>
      </li> -->

      <!-- Divider -->
      <hr class="sidebar-divider d-none d-md-block" />
      <!-- Sidebar Toggler (Sidebar) -->
      <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
      </div>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
      <!-- Main Content -->
      <div id="content">
        <!-- Topbar -->
        <nav
          class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow"
        >
          <!-- Sidebar Toggle (Topbar) -->
          <button
            id="sidebarToggleTop"
            class="btn btn-link d-md-none rounded-circle mr-3"
          >
            <i class="fa fa-bars"></i>
          </button>
          <!-- Topbar Navbar -->
          <ul class="navbar-nav ml-auto">
            <!-- Nav Item - User Information -->
            <li class="nav-item dropdown no-arrow">
              <a
                class="nav-link dropdown-toggle"
                href="#"
                id="userDropdown"
                role="button"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
              >
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{
                  nameUser
                }}</span>
                <div class="topbar-divider d-none d-sm-block"></div>
                <img
                  class="img-profile rounded-circle"
                  :src="'no-avatar.png'"
                />
              </a>
              <!-- Dropdown - User Information -->
              <div
                class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="userDropdown"
              >
                <!-- <a class="dropdown-item" href="user/reset">
                  <i class="fas fa-key fa-sm fa-fw mr-2 text-gray-400"></i>
                  Cambiar Contrase&ntilde;a
                </a> -->
                <a
                  class="dropdown-item"
                  href="#"
                  data-toggle="modal"
                  data-target="#modalLogout"
                  @click="logout"
                >
                  <i
                    class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"
                  ></i>
                  Cerrar Sesi&oacute;n
                </a>
              </div>
            </li>
          </ul>
        </nav>
        <!-- End of Topbar -->
        <!-- Begin Page Content -->
        <div class="container-fluid">
          <main class="py-4">
            <router-view />
          </main>
        </div>
        <!-- /.container-fluid -->
      </div>
      <!-- End of Main Content -->
      <!-- Footer -->
      <footer class="sticky-footer bg-white">
        <div class="container my-auto">
          <div class="copyright text-center my-auto">
            <span>Copyright &copy; Transmilenio 2020</span>
          </div>
        </div>
      </footer>
      <!-- End of Footer -->
    </div>
  </div>
  
</template>
<script>
import toastr from "toastr";
export default {
  data() {
    return {
      nameApp: process.env.MIX_APP_NAME,
      nameUser: "Usuario no identificado",
      debug : !! this.$route.query?.test
    };
  },
  methods: {
    logout() {
      this.$store.dispatch("logout").then((res) => {
        if (res == 200) {
          toastr.clear();
          toastr.success("Sesion Cerrada");
          this.$router.push("/login");
        }
      });
    },
  },
  mounted() {
    if (this.$store.getters.currentUser) {
      this.nameUser = this.$store.getters.currentUser[0].name;
    }
  },
};
</script>
