<template>
  <div class="row q-gutter-md">
    <q-date :locale="myLocale"
        v-model="myDate"
        :mask="myMask"
        today-btn
        @input="dateChange"/>
      <q-time v-if="myShowTime"
        v-model="myDate"
        format24h
        :mask="myMask"
        @input="dateChange" />
  </div>
</template>
<script>
export default {
  props: ['value', 'maskValue', 'showTime'],
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
      myDate: '',
      myMask: 'YYYY-MM-DD HH:mm:ss',
      myShowTime: false
    }
  },
  methods: {
    dateChange (value, reason, details) {
      this.$emit('input', value)
    }
  },
  mounted () {
    if (this.showTime !== undefined) this.myShowTime = this.showTime
    if (this.maskValue !== undefined) this.myMask = this.maskValue
    this.myDate = this.value
  }
}
</script>
