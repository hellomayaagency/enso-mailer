<template>
  <div class="mailer__condition-builder">
    <div class="columns">
      <div class="column is-3">
        <label class="label">Value to match</label>
        <div class="select is-fullwidth" :class="operandClasses">
          <select 
            :value="operandValue"
            @input="updateOperand($event.target.value)"
          >
            <option :value="null" disabled selected>Please select...</option>
            <option v-for="(option, value) in operandOptions" :key="value" :value="value">{{ option }}</option>
          </select>
        </div>
      </div>
      <div class="column is-3">
        <label class="label">How to match</label>
        <div class="select is-fullwidth" :class="operatorClasses">
          <select 
            :value="operatorValue"
            @input="updateOperantor($event.target.value)"
          >
            <option :value="null" disabled selected>Please select...</option>
            <option v-for="(option, value) in operatorOptions" :key="value" :value="value">{{ option }}</option>
          </select>
        </div>
      </div>

      <div class="column is-5">
        <label class="label">What to match</label>
        <component 
          :is="operandComponent"
          :fieldset-classes="['has-addons']"
          :field-classes="['is-expanded']"
          v-bind="operandComponentProps"
          v-for="(datum, datum_key) in data"
          :key="datum_key"
          :input-value="datum"
          @input="updateData($event, datum_key)"
          ref="data_selection"
        >
          <p slot="appends" class="control" @click.prevent="removeData(datum_key)">
            <a
              class="button is-danger"
              :disabled="!canRemoveData"
            >
              <i class="fa fa-times"></i>
            </a>
          </p>
        </component>
        <div class="has-text-right">
          <button
            class="button is-info"
            @click.prevent="addData()"
          >OR</button>
        </div>
      </div>

      <delete-button
        class="delete-button column is-1"
        @click="deleteCondition"
      >
      </delete-button> 
    </div>
  </div>
</template>

<script>
  import DeleteButton from '../../../components/DeleteButton.vue';
  import clone from 'lodash/clone';
  import get from 'lodash/get';
  import isArray from 'lodash/isArray';
  import first from 'lodash/head';
  import mapValues from 'lodash/mapValues'; 
  import pickBy from 'lodash/pickBy';
  import includes from 'lodash/includes';

  export default {
    components: {
      DeleteButton,
    },

    props: {
      condition: {
        required: true,
        type: Object,
      },

      index: {
        required: true,
        type: Number,
      },

      fieldSelectionOptions: {
        required: true,
        type: Object,
      },
    },

    computed: {
      operandValue() {
        return get(this.condition, 'operand', null);
      },

      operatorValue() {
        return get(this.condition, 'operator', null);
      },

      data() {
        let value = get(this.condition, 'data', []);

        if (value.length === 0) {
          return [""];
        }

        if (isArray(value)) {
          return value;
        }

        return [value];
      },

      operands() {
        return get(this.fieldSelectionOptions, 'operands', {});
      },

      operand() {
        return this.operandByName(this.operandValue);
      },

      operandComponent() {
        return get(this.operand, 'component', 'enso-field-text');
      },

      operandComponentProps() {
        return get(this.operand, 'component_props', {});
      },

      operandClasses() {
        return this.operandValue ? '' : 'is-danger';
      },

      operators() {
        return get(this.fieldSelectionOptions, 'operators', {});
      },

      operator() {
        return this.operatorByName(this.operatorValue);
      },

      operatorClasses() {
        return this.operandAllowsOperator(this.operandValue, this.operatorValue) ? '' : 'is-danger'
      },

      operandOptions() {
        return mapValues(this.operands, function(item) {
          return item.label;
        });
      },

      operatorOptions() {
        return mapValues(pickBy(this.operators, (value, name) => {
          return includes(get(this.operand, 'allowed_operators', []), name);
        }), function(item) {
          return item.label;
        });
      },

      isArrayableOperator() {
        return true;
      },

      dataClasses() {
        return this.operatorAllowsData(this.operatorValue, this.data) ? '' : 'is-danger';
      },

      canRemoveData() {
        return this.data.length > 1;
      }
    },

    methods: {
      operandByName(name) {
        return get(this.operands, name, undefined);
      },

      operatorByName(name) {
        return get(this.operators, name, undefined);
      },

      operandAllowsOperator(operand, operator) {
        return includes(
          get(this.operandByName(operand), 'allowed_operators', []),
          operator
        );
      },

      operatorAllowsData(operator, data) {
        return !! (data.length > 0);
      },

      updateOperand(value) {
        let condition = clone(this.condition);
        
        condition.operand = value;

        /**
         * If new operand does not support existing operator, clear the operator.
         * This should trigger an error state class to be applied
         */
        if (!this.operandAllowsOperator(value, this.operatorValue)) {
          condition.operator = null;
        }

        this.updateCondition({index: this.index, value: condition});
      },

      updateOperantor(value) {
        let condition = clone(this.condition);
        
        condition.operator = value;

        if (!this.operatorAllowsData(value, this.data) && this.data.length > 0) {
          condition.data = [];
        }

        this.updateCondition({index: this.index, value: condition});
      },

      addData() {
        let condition = clone(this.condition);

        condition.data = this.data; // Ensure data is in array format.
        condition.data.push('');

        this.updateCondition({index: this.index, value: condition});

        this.$nextTick(() => {
          // Focus the new element
          let last_data_element = get(this.$refs['data_selection'], this.$refs['data_selection'].length -1);

          if (last_data_element) {
            let element_input = get(last_data_element, '$refs.input');

            if (element_input) {
              element_input.focus();
            }
          }
        });
      },

      removeData(key) {
        if (!this.canRemoveData) {
          return;
        }

        let condition = clone(this.condition);

        condition.data = this.data; // Ensure data is in array format.
        condition.data.splice(key, 1);

        this.updateCondition({index: this.index, value: condition});
      },

      updateData(value, key) {
        let condition = clone(this.condition);
        
        condition.data = this.data;
        condition.data[key] = value;

        this.updateCondition({index: this.index, value: condition});
      },

      updateCondition(condition) {
        this.$emit('update', condition);
      },

      deleteCondition() {
        this.$emit('delete', this.index);
      },
    }
  }
</script>

<style lang="scss">
  .mailer__condition-builder {
    position: relative;
    background-color: #eee;
    padding: 15px;
    margin: 10px 0;
    border: solid 1px #dbdbdb;
    box-shadow: 0 2px 4px rgba(163,136,190,0.5);
  }
</style>

<style lang="scss" scoped>
  .select:not(.is-multiple):not(.is-loading)::after {
    border-color: rgb(163,136,190);
  }

  .delete-button {
    position: absolute;
    top: 0;
    right: 0;
    justify-content: center;
    align-items: center;
    display: flex;

    &:hover {
      background-color: #ddd;
      cursor: pointer;
    }
  }
</style>