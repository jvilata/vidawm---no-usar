<template>
    <q-input v-model="myValue"
        outlined
        class="col"
        label="Search">
        <template v-slot:append>
            <q-icon v-if="myValue !==''" name="close"
                @click="myValue = ''"
                class="cursor-pointer" />
            <q-icon name="search" />
        </template>
    </q-input>
</template>

<script>

export default {
  props: ['value'],
  data () {
    return {
      myValue: this.value
    }
  },
  destroyed () {
    this.$emit('input', this.myValue)
  }
}
</script>
