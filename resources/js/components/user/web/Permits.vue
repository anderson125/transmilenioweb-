<template>
    <div>
        <h6 class="m-0 font-weight-bold text-danger">Permisos</h6>
        <div class="py-2">
            <b-button v-b-modal.modal-1 class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm" @click="modalShow = !modalShow"><i class="fas fa-plus fa-sm text-white-50"></i> Añadir</b-button>
        </div>
        <br>
        <div class="table-responsive">
          <table id="user_table" class="table table-bordered table-striped">
              <thead>
                  <tr>
                      <th class="singleLine">Nombre del Permisos</th>
                      <th class="singleLine">Rol</th>
                      <th class="singleLine">Estados</th>
                      <th class="singleLine">Acciones</th>
                  </tr>
              </thead>
          </table>
        </div>
    </div>
</template>