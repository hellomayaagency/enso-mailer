<template>
  <div class="mailer__audience-form">
    <div v-if="errors" class="notification is-danger content">
      <h2 class="subtitle is-5">Something's not quite right...</h2>
      <ul>
        <template v-for="error in errors">
          <li v-for="message in error" :key="message">{{ message }}</li>
        </template>
      </ul>
    </div>

    <form class="form-settings" @submit.prevent="submitForm">
      <div class="columns">
        <div class="column">
          <enso-field-text
            label="Audience Name"
            :inputValue="name"
            @input="setName"
            :errors="getErrors('name')"
            help-text="This is for admin use only. You use this name to search for audiences to add to a campaign"
          ></enso-field-text>
        </div>
      </div>

      <query-builder
        :field-selection-options="fieldSelectionOptions"
        :conditions="conditions"
        @update="setConditions"
      ></query-builder>

      <hr />

      <div class="has-text-right">
        <button type="submit" class="button is-primary">Submit</button>
      </div>
    </form>
  </div>
</template>

<script>
import get from 'lodash/get';
import map from 'lodash/map';
import compact from 'lodash/compact';
import swal from 'sweetalert2/dist/sweetalert2.js';

export default {
  components: {
    //
  },

  props: {
    method: {
      required: true,
      type: String,
    },

    action: {
      type: String,
      required: true,
    },

    item: {
      required: true,
      type: Object,
    },

    fieldSelectionOptions: {
      required: true,
      type: Object,
    },
  },

  data() {
    return {
      item_state: {},
      submitting: false,
      errors: null,
    };
  },

  created() {
    this.item_state = this.item;
  },

  computed: {
    name() {
      return get(this.item_state, 'name', '');
    },

    conditions() {
      let conditions = get(this.item_state, 'conditions', []);

      if (conditions.length) {
        return conditions;
      }

      return [
        {
          type: 'AND',
          component: 'query-group',
          conditions: [],
        },
      ];
    },
  },

  methods: {
    setName(value) {
      this.emptyErrors('name');
      this.$set(this.item_state, 'name', value);
    },

    setConditions(conditions) {
      this.$set(this.item_state, 'conditions', conditions);
    },

    submitForm(event) {
      this.submitting = true;
      this.errors = null;

      this.$root.axios
        .request({
          method: this.method,
          url: this.action,
          data: this.item_state,
        })
        .then(this.onResponse)
        .catch(this.onError);
    },

    onResponse(response) {
      this.submitting = false;

      if (response.data.status === 'success') {
        swal({
          title: 'Success!',
          html: this.sweetAlertHtml('Your changes were saved successfully.', response),
          type: 'success',
          onOpen: function(swal) {
            for (let i = 0; i < get(response, 'data.buttons', []).length; i++) {
              let button_elements = swal.getElementsByClassName('js-swal-button-' + i);
              for (let j = 0; j < button_elements.length; j++) {
                button_elements[j].addEventListener(
                  'click',
                  function() {
                    window.location = get(response, `data.buttons[${i}].url`, '');
                  },
                  false
                );
              }
            }
          },
          allowOutsideClick: false,
          allowEscapeKey: false,
          showCancelButton: false,
          showConfirmButton: false,
        });
      } else if (response.data.status === 'error') {
        swal({
          title: 'Oops!',
          text: response.data.message,
          type: 'error',
        });
      } else {
        swal({
          title: 'Oops!',
          text: 'Something went wrong. Please try again.',
          type: 'error',
        });
      }
    },

    onError(error) {
      this.submitting = false;

      if (get(error, 'response.status', false) === 422) {
        if (get(error, 'response.data.errors')) {
          this.errors = get(error, 'response.data.errors', []);
        } else {
          this.errors = get(error, 'response.data', []);
        }

        window.scrollTo(0, 0);
      } else {
        swal({
          title: 'Ooops...',
          text: 'Something went wrong! ' + error,
          type: 'error',
        });
      }
    },

    getErrors(type) {
      return get(this.errors, type, []);
    },

    getErrorClasses(type) {
      return this.getErrors(type).length ? 'is-danger' : '';
    },

    emptyErrors(type) {
      if (this.errors !== null) {
        delete this.errors[type];
      }
    },

    sweetAlertHtml(message, response) {
      let buttons = get(response, 'data.buttons', []);

      if (buttons.length === 0) {
        return message;
      }

      return (
        message +
        '<br><div class="crud-button-list">' +
        map(buttons, function(button, index) {
          let classes = compact([
            'button',
            get(button, 'class', ''),
            'js-swal-button-' + index,
          ]).join(' ');

          let icon = get(button, 'icon', '');

          if (icon.length > 0) {
            icon = '<i class="' + icon + '"></i>';
          }

          return (
            '<button type="button" role="button" tabindex="0" class="' +
            classes +
            '">' +
            icon +
            ' ' +
            get(button, 'label', '') +
            '</button>'
          );
        }).join('') +
        '</div>'
      );
    },
  },
};
</script>
