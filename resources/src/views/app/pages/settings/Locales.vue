<template>
  <div class="main-content">
    <breadcumb :page="$t('Locales')" :folder="$t('Settings')" />

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <b-card class="wrapper" v-if="!isLoading">
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="locales"
        @on-page-change="onPageChange"
        @on-per-page-change="onPerPageChange"
        @on-sort-change="onSortChange"
        @on-search="onSearch"
        :search-options="{
          enabled: true,
          placeholder: $t('Search_this_table'),
        }"
        :select-options="{
          enabled: true,
          clearSelectionText: '',
        }"
        @on-selected-rows-change="selectionChanged"
        :pagination-options="{
          enabled: true,
          mode: 'records',
          nextLabel: 'next',
          prevLabel: 'prev',
        }"
        styleClass="table-hover tableOne vgt-table"
      >
        <div slot="selected-row-actions">
          <button class="btn btn-danger btn-sm" @click="delete_by_selected()">{{ $t('Del') }}</button>
        </div>
        <div slot="table-actions" class="mt-2 mb-3">
          <b-button
            @click="createLocale()"
            class="btn-rounded"
            variant="btn btn-primary btn-icon m-1"
          >
            <i class="i-Add"></i>
            {{ $t('Add') }}
          </b-button>
        </div>

        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
            <a @click="editLocale(props.row)" title="Edit" v-b-tooltip.hover>
              <i class="i-Edit text-25 text-success"></i>
            </a>
            <a title="Delete" v-b-tooltip.hover @click="deleteLocale(props.row.id)">
              <i class="i-Close-Window text-25 text-danger"></i>
            </a>
          </span>
          <span v-else-if="props.column.field === 'flag'">
            <span v-html="props.column.formatFn(props.row[props.column.field])"></span>
          </span>
          <span v-else>
            {{ props.formattedRow[props.column.field] }}
          </span>
        </template>
      </vue-good-table>
    </b-card>

    <validation-observer ref="Create_Category">
      <b-modal hide-footer size="md" id="createLocale" :title="editmode ? $t('Edit') : $t('Add')">
        <b-form @submit.prevent="submitForm" ref="test">
          <b-row>
            <!-- Name -->
            <b-col md="12">
              <validation-provider
                name="name"
                :rules="{ required: true, max: 50 }"
                v-slot="validationContext"
              >
                <b-form-group :label="`${$t('Name')}*`">
                  <b-form-input
                    :placeholder="$t('Enter_Code_category')"
                    :state="getValidationState(validationContext)"
                    aria-describedby="Code-feedback"
                    label="Name"
                    v-model="locale.name"
                  />
                  <b-form-invalid-feedback id="Code-feedback">
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- abbreviation -->
            <b-col md="12">
              <validation-provider
                name="abbreviation"
                :rules="{ required: true, max: 20 }"
                v-slot="validationContext"
              >
                <b-form-group :label="`${$t('Abbreviation')}*`">
                  <b-form-select v-model="locale.abbreviation" class="mb-3">
                    <b-form-select-option v-for="abbreviation in abbreviations" :key="abbreviation" :value="abbreviation">
                      {{ abbreviation }}
                    </b-form-select-option>
                  </b-form-select>
                  <b-form-invalid-feedback id="Name-feedback">
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- flag -->
            <b-col md="12">
              <validation-provider
                name="flags"
                :rules="{ required: true, max: 20 }"
                v-slot="validationContext"
              >
                <b-form-group :label="`${$t('flags')}*`">
                  <b-form-select v-model="locale.flag" class="mb-3">
                    <b-form-select-option v-for="flag in flags" :key="flag" :value="flag">
                      {{ flag }}
                    </b-form-select-option>
                  </b-form-select>
                  <b-form-invalid-feedback id="Name-feedback">
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <b-col md="12" class="mt-3">
              <b-button variant="primary" type="submit" :disabled="SubmitProcessing">
                <i class="i-Yes me-2 font-weight-bold"></i> {{ $t('submit') }}
              </b-button>
              <div v-once class="typo__p" v-if="SubmitProcessing">
                <div class="spinner sm spinner-primary mt-3"></div>
              </div>
            </b-col>

          </b-row>
        </b-form>
      </b-modal>
    </validation-observer>
  </div>
</template>

<script>
import NProgress from "nprogress";
import flags from "../../../../translations/countries";

export default {
  metaInfo: {
    title: "Locales"
  },
  data() {
    return {
      flags: flags,
      isLoading: true,
      SubmitProcessing: false,
      serverParams: {
        columnFilters: {},
        sort: {
          field: "id",
          type: "desc"
        },
        page: 1,
        perPage: 10
      },
      selectedIds: [],
      totalRows: "",
      search: "",
      limit: "10",
      editmode: false,
      categories: [],
      locale: {
        id: '',
        name: '',
        abbreviation: '',
        flag: ''
      },
      defaultLocale: '',
      locales: {},
      abbreviations: {},
    };
  },
  computed: {
    columns() {
      return [
        {
          label: this.$t("Name"),
          field: "name",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Abbreviation"),
          field: "abbreviation",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Status"),
          field: "status",
          tdClass: "text-left",
          thClass: "text-left",
        },
        {
          label: this.$t("Flag"),
          field: "flag",
          html: true,
          tdClass: "text-left",
          thClass: "text-left",
          formatFn: (value) => {
            return `<i class="flag-icon flag-icon-squared flag-icon-${value}" title="sa"></i>`;
          },
          sortable: true // Set to true if you want the column to be sortable
        },
        {
          label: this.$t("Action"),
          field: "actions",
          html: true,
          tdClass: "text-right",
          thClass: "text-right",
          sortable: false
        }
      ];
    }
  },

  methods: {
    //---- update Params Table
    updateParams(newProps) {
      this.serverParams = Object.assign({}, this.serverParams, newProps);
    },

    //---- Event Page Change
    onPageChange({ currentPage }) {
      if (this.serverParams.page !== currentPage) {
        this.updateParams({ page: currentPage });
        this.indexLocales(currentPage);
      }
    },

    //---- Event Per Page Change
    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.indexLocales(1);
      }
    },

    //---- Event Select Rows
    selectionChanged({ selectedRows }) {
      this.selectedIds = [];
      selectedRows.forEach((row, index) => {
        this.selectedIds.push(row.id);
      });
    },

    //---- Event on Sort Change
    onSortChange(params) {
      this.updateParams({
        sort: {
          type: params[0].type,
          field: params[0].field
        }
      });
      this.indexLocales(this.serverParams.page);
    },

    //---- Event on Search

    onSearch(value) {
      this.search = value.searchTerm;
      this.indexLocales(this.serverParams.page);
    },

    //---- Validation State Form

    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    //------------- Submit Validation Create & Edit Category
    submitForm(event) {
      this.$refs.Create_Category.validate().then(success => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
          if (!this.editmode) {
            this.storeLocale();
          } else {
            this.updateLocale();
          }
        }
      });
    },

    //------ Toast
    makeToast(variant, msg, title) {
      this.$root.$bvToast.toast(msg, {
        title: title,
        variant: variant,
        solid: true
      });
    },

    //------------------------------ Modal  (create locale) -------------------------------\\
    createLocale() {
      this.resetForm();
      this.editmode = false;
      this.$bvModal.show("createLocale");
    },

    //------------------------------ Modal (Update locale) -------------------------------\\
    editLocale(locale) {
      this.indexLocales(this.serverParams.page);
      this.resetForm();
      this.locale = locale;
      this.editmode = true;
      this.$bvModal.show("createLocale");
    },

    //--------------------------Get ALL Locales & Sub locale ---------------------------\\

    indexLocales(page) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios.get('settings/locales', {
        params: {
          page: page,
          SortField: this.serverParams.sort.field,
          SortType: this.serverParams.sort.type,
          search: this.search,
          limit: this.limit
        }
      }).then(response => {
        this.totalRows = response.data.locales.length;
        this.defaultLocale = response.data.defaultLocale;
        this.locales = response.data.locales;

        // Complete the animation of theprogress bar.
        NProgress.done();
        this.isLoading = false;
      }).catch(response => {
        // Complete the animation of theprogress bar.
        NProgress.done();
        setTimeout(() => {
          this.isLoading = false;
        }, 500);
      });
    },

    //----------------------------------Create new Locales ----------------\\
    storeLocale() {
      this.SubmitProcessing = true;
      axios.post("settings/locales", {
        ...this.locale
      }).then(response => {
        this.SubmitProcessing = false;
        Fire.$emit("Event_Category");
        this.makeToast(
          "success",
          this.$t("Create.TitleCat"),
          this.$t("Success")
        );
      }).catch(error => {
        this.SubmitProcessing = false;
        this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
      });
    },

    //---------------------------------- Update Category ----------------\\
    updateLocale() {
      this.SubmitProcessing = true;
      axios.put(`settings/locales/${this.locale.id}`, {
          name: this.locale.name,
          abbreviation: this.locale.abbreviation
        }).then(response => {
          this.SubmitProcessing = false;
          Fire.$emit("Event_Category");
          this.makeToast(
            "success",
            this.$t("Update.TitleCat"),
            this.$t("Success")
          );
        }).catch(error => {
          this.SubmitProcessing = false;
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
        });
    },

    //--------------------------- reset Form ----------------\\

    resetForm() {
      this.locale = {
        id: '',
        name: '',
        abbreviation: ''
      };
    },

    //--------------------------- Remove Category----------------\\
    deleteLocale(id) {
      this.$swal({
        title: this.$t("Delete.Title"),
        text: this.$t("Delete.Text"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: this.$t("Delete.cancelButtonText"),
        confirmButtonText: this.$t("Delete.confirmButtonText")
      }).then(result => {
        if (result.value) {
          axios.delete(`settings/locales/${id}`).then(() => {
            this.$swal(
              this.$t("Delete.Deleted"),
              this.$t("Delete.CatDeleted"),
              "success"
            );

            Fire.$emit("deleteLocale");
          }).catch(() => {
            this.$swal(
              this.$t("Delete.Failed"),
              this.$t("Delete.Therewassomethingwronge"),
              "warning"
            );
          });
        }
      });
    },

    //---- Delete category by selection

    delete_by_selected() {
      this.$swal({
        title: this.$t("Delete.Title"),
        text: this.$t("Delete.Text"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: this.$t("Delete.cancelButtonText"),
        confirmButtonText: this.$t("Delete.confirmButtonText")
      }).then(result => {
        if (result.value) {
          // Start the progress bar.
          NProgress.start();
          NProgress.set(0.1);
          axios.delete(`settings/locales/${this.selectedIds}`, {
            selectedIds: this.selectedIds
          }).then(() => {
            this.$swal(
              this.$t("Delete.Deleted"),
              this.$t("Delete.CatDeleted"),
              "success"
            );

            Fire.$emit("deleteLocale");
          }).catch(() => {
            // Complete the animation of theprogress bar.
            setTimeout(() => NProgress.done(), 500);
            this.$swal(
              this.$t("Delete.Failed"),
              this.$t("Delete.Therewassomethingwronge"),
              "warning"
            );
          });
        }
      });
    }
  }, //end Methods

  //----------------------------- Created function-------------------

  created: function () {
    this.indexLocales(1);

    this.abbreviations = this.$i18n.availableLocales;

    Fire.$on("Event_Category", () => {
      setTimeout(() => {
        this.indexLocales(this.serverParams.page);
        this.$bvModal.hide("createLocale");
      }, 500);
    });

    Fire.$on("deleteLocale", () => {
      setTimeout(() => {
        this.indexLocales(this.serverParams.page);
      }, 500);
    });
  }
};
</script>

<style scoped>
.custom-select .icon {
  display: inline-block;
  margin-right: 5px;
}
</style>
