<template>
  <q-page>
    <div class="row">
      <task-search />
      <q-btn
        @click="$emit('close')"
        flat
        round
        dense
        icon="close"/>
    </div>
    <q-list separator
      bordered>
      <task-row v-for="(task,key) in tasksFiltered"
        :key="key"
        :task="task"
        :id="key"
        v-on:modifyTaskRow="modifyTaskRow">
      </task-row>

      <div class="absolute-bottom text-center q-mb-lg">
        <q-btn
          @click.stop="openAddTask"
          round
          dense
          color="primary"
          size="24px"
          icon="add"/>
      </div>
    </q-list>
    <q-dialog v-model="action">
      <task-form @close="action=false"
        :value="taskedit"
        :id="id"
        :keyValue="id"
        :isAdd="isAdd"/>
    </q-dialog>
  </q-page>
</template>

<script>
import { mapGetters, mapActions } from 'vuex'
import TaskRow from 'components/Tasks/TaskRow.vue'
import TaskForm from 'components/Tasks/TaskForm.vue'
import TaskSearch from 'components/Tasks/SearchTask.vue'
export default {
  data () {
    return {
      action: false,
      isAdd: false,
      id: '',
      taskedit: {}
    }
  },
  computed: {
    ...mapGetters('tasks', ['tasksFiltered'])
  },
  methods: {
    ...mapActions('tabs', ['addTab', 'updateTabData']),
    openAddTask () {
      this.taskedit = {}
      this.isAdd = true
      this.id = null
      this.action = true
      // this.addTab(['TaskForm', 'Tarea (Nueva)'])
    },
    modifyTaskRow (rowChanges) {
      Object.assign(this.taskedit, rowChanges.taskedit)
      this.id = rowChanges.id
      this.isAdd = false
      this.action = rowChanges.action
      // this.addTab(['TaskForm', 'Tarea-' + rowChanges.taskedit.name.substr(0, 5), rowChanges.taskedit, rowChanges.id])
    }
  },
  mounted () {
    console.log('tasklist moint ', this.taskedit)
  },
  components: {
    'task-row': TaskRow,
    'task-form': TaskForm,
    'task-search': TaskSearch
  }
}
</script>
