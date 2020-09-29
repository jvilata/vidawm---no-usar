<template>
  <q-item
        tag="label" v-ripple
        clickable
        @click="updateTask({id: id, task:{selected: !taskedit.selected}})"
        :class="!taskedit.selected?'bg-orange-1':'bg-green-1'"
        >
        <q-item-section side top>
          <q-checkbox :value="taskedit.selected"
            class="no-pointer-events" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{taskedit.name}} {{id}}</q-item-label>
        </q-item-section>
        <q-item-section side top>
          <q-item-label caption>
            {{taskedit.dueDate | niceDate}}
          </q-item-label>
          <q-item-label>
            <small>{{taskedit.dueTime}}</small>
          </q-item-label>
        </q-item-section>
        <q-item-section side>
          <div class="row">
            <q-btn flat
              @click.stop="editTask(taskedit, id)"
              round
              dense
              color="primary"
              icon="edit"/>
            <q-btn flat
              @click.stop="promptToDelete(id)"
              round
              dense
              color="red"
              icon="delete"/>
          </div>
        </q-item-section>
      </q-item>

</template>

<script>
import { mapActions } from 'vuex'
import { date } from 'quasar'

export default {
  props: [
    'task', 'id', 'action'
  ],
  data () {
    return {
      taskedit: this.task
    }
  },
  methods: {
    ...mapActions('tasks', ['updateTask', 'deleteTask']),
    promptToDelete (id) {
      this.$q.dialog({
        title: 'Confirmar',
        message: '¿ Borrar esta fila ?',
        ok: true,
        cancel: true,
        persistent: true
      }).onOk(() => {
        this.deleteTask(id)
      })
    },
    editTask (task, id) {
      this.$emit('modifyTaskRow', { action: true, taskedit: this.taskedit, id: this.id })
      // this.changeModalD({ showDialog: true, isAdd: false, payload: { id: id, task: task } })
    }
  },
  filters: {
    niceDate (value) {
      return date.formatDate(value, 'DD-MM-YYYY')
    }
  }
}
</script>

<style>

</style>
