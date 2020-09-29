<template>
    <q-input v-model="searchField"
        outlined
        class="col"
        label="Search">
        <template v-slot:append>
            <q-icon v-if="searchField !==''" name="close"
                @click="searchField = ''"
                class="cursor-pointer" />
            <q-icon name="search" />
        </template>
    </q-input>
</template>

<script>
import { mapState, mapActions } from 'vuex'

export default {
  computed: {
    ...mapState('tasks', ['search']),
    searchField: {
      get () {
        return this.search
      },
      set (value) {
        this.changeSearch(value)
      }
    }
  },
  methods: {
    ...mapActions('tasks', ['changeSearch'])
  }
}
</script>
