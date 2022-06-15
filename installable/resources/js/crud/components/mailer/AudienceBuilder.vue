<template>
  <div class="mailer__audience-builder">
    <p v-if="hasConditions">
      <condition-builder 
        v-for="(condition, index) in conditions" :key="index"
        :index="index"
        :condition="condition"
        :field-selection-options="fieldSelectionOptions"
        @update="updateCondition"
        @delete="deleteCondition(index)"
      ></condition-builder>
    </p>
    <p class="notification has-text-centered" v-else>
      Having an Audience with no conditions will create an audience of all users.
    </p>
    <p class="has-text-centered button-container">
      <button
        class="button is-primary"
        @click.prevent="addCondition"
      >Add Condition</button>
    </p>
  </div>
</template>

<script>
  import Vue from 'vue';
  import ConditionBuilder from './ConditionBuilder.vue';
  import clone from 'lodash/clone';

  export default {
    components: {
      ConditionBuilder,
    },

    props: {
      conditions: {
        required: true,
        type: Array,
      },

      fieldSelectionOptions: {
        required: true,
        type: Object,
      },
    },

    computed: {
      hasConditions() {
        return !! this.conditions.length;
      }
    },

    methods: {
      addCondition() {
        let conditions = clone(this.conditions);

        conditions.push({});

        this.updateConditions(conditions);
      },

      updateCondition({ index, value }) {
        let conditions = clone(this.conditions);

        conditions[index] = value;

        this.updateConditions(conditions);
      },

      deleteCondition(value) {
        let conditions = clone(this.conditions);

        conditions.splice(value, 1);

        this.updateConditions(conditions);
      },

      updateConditions(current_conditions) {
        this.$emit('update', current_conditions);
      }
    },
  }
</script>

<style lang="scss" scoped>
  .button-container {
    margin-top: 1.5rem;
  }
</style>
