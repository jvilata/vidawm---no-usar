<template>
    <q-card>
        <q-form @submit="submitForm" @keyup.esc="$emit('close')">
          <q-card-section class="row">
              <div class="text-h6">{{title}}</div>
              <q-space />
              <q-btn
                  @click="$emit('close')"
                  flat
                  round
                  dense
                  icon="close"/>
          </q-card-section>
          <q-card-section>
            <div class="row q-mb-sm">
              <q-input outlined v-model="taskToSubmit.name" label="Task name" class="col"
                autofocus
                :rules="[val => !!val || 'Campo requerido']"
                ref="taskName"
              />
            </div>
            <div class="row q-mb-sm">
              <q-input outlined v-model="taskToSubmit.dueDate"  label="dueDate" class="col">
                <template v-slot:append>
                  <q-icon name="event" class="cursos-pointer">
                    <q-popup-proxy>
                      <wgDate :dateValue="taskToSubmit.dueDate"
                        v-on:dateChange="(value) => taskToSubmit.dueDate=value"/>
                    </q-popup-proxy>
                  </q-icon>
                </template>
              </q-input>
            </div>
            <div class="row q-mb-sm">
              <q-input outlined v-model="taskToSubmit.dueTime" label="dueTime" class="col">
                <template v-slot:append>
                  <q-icon name="access_time" class="cursos-pointer">
                    <q-popup-proxy>
                      <q-time v-model="taskToSubmit.dueTime" />
                    </q-popup-proxy>
                  </q-icon>
                </template>
              </q-input>
            </div>

          </q-card-section>
          <q-card-actions align=right>
              <q-btn type="submit" label="Save" color="primary"/>
          </q-card-actions>
        </q-form>
    </q-card>
</template>

<script>
import { mapActions } from 'vuex'
import wgDate from 'components/General/wgDate.vue'

export default {
  props: ['value', 'id', 'keyValue'],
  data () {
    return {
      myLocale: {
        /* starting with Sunday */
        days: 'Domingo_Lunes_Martes_Miércoles_Jueves_Viernes_Sábado'.split('_'),
        daysShort: 'Dom_Lun_Mar_Mié_Jue_Vie_Sáb'.split('_'),
        months: 'Enero_Febrero_Marzo_Abril_Mayo_Junio_Julio_Agosto_Septiembre_Octubre_Noviembre_Diciembre'.split('_'),
        monthsShort: 'Ene_Feb_Mar_Abr_May_Jun_Jul_Ago_Sep_Oct_Nov_Dic'.split('_'),
        firstDayOfWeek: 1
      },
      title: 'Add Task',
      taskToSubmit: { // inicializamos los campos, sino no funciona bien
        name: '',
        dueDate: '',
        dueTime: '',
        selected: false
      }
    }
  },
  methods: {
    ...mapActions('tasks', ['addTask', 'updateTask']),
    submitForm () {
      this.$refs.taskName.validate()
      if (!this.$refs.taskName.hasError) {
        if (this.keyValue === null) {
          this.addTask(this.taskToSubmit)
        } else {
          this.updateTask({ id: this.keyValue, task: this.taskToSubmit })
        }
        this.$emit('close')
      }
    }
  },
  mounted () {
    Object.assign(this.taskToSubmit, this.value) // v-model: en 'value' podemos leer el valor del v-model
    if (this.keyValue === null) {
      this.title = 'Add Task'
    } else {
      this.title = 'Update Task'
    }
  },
  destroyed () {
    this.$emit('input', this.taskToSubmit) // v-model: para devolver el valor a atributo 'value', evento input
  },
  components: {
    wgDate: wgDate
  }
}
</script>

<style>

</style>
