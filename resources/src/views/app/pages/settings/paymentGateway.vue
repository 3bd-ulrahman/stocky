<template>
  <div class="main-content">
    <breadcumb :page="$t('payment_gateway')" :folder="$t('Settings')" />
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>

    <!-- Payment Gateway -->
    <validation-observer ref="formPayment" v-if="!isLoading">
      <b-form @submit.prevent="storeStripe">
        <b-row class="mt-5">
          <b-col lg="12" md="12" sm="12">
            <b-card no-body :header="$t('Payment_Gateway')">
              <b-card-body>
                <b-row>

                  <!-- Strip key  -->
                  <b-col lg="6" md="6" sm="12">
                    <b-form-group label="STRIPE_KEY">
                      <b-form-input
                        type="password"
                        v-model="stripe.STRIPE_KEY"
                        :placeholder="$t('LeaveBlank')"
                      />
                    </b-form-group>
                  </b-col>

                  <!-- Strip Secret  -->
                  <b-col lg="6" md="6" sm="12">
                    <b-form-group label="STRIPE_SECRET">
                      <b-form-input
                        type="password"
                        v-model="stripe.STRIPE_SECRET"
                        :placeholder="$t('LeaveBlank')"></b-form-input>
                    </b-form-group>
                  </b-col>

                  <b-col md="6" class="mt-3 mb-3">
                    <label class="switch switch-primary mr-3">
                      {{ $t('active') }}
                      <input type="checkbox" v-model="stripe.is_active">
                      <span class="slider"></span>
                    </label>
                  </b-col>

                  <!-- Remove Stripe Key & Secret-->
                  <b-col md="6" class="mt-3 mb-3">
                    <label class="switch switch-primary mr-3">
                      {{ $t('Remove_Stripe_Key_Secret') }}
                      <input type="checkbox" v-model="stripe.delete_stripe">
                      <span class="slider"></span>
                    </label>
                  </b-col>

                  <b-col md="12">
                    <b-form-group>
                      <b-button variant="primary" type="submit"><i class="i-Yes me-2 font-weight-bold"></i>
                        {{ $t('submit') }}</b-button>
                    </b-form-group>
                  </b-col>
                </b-row>
              </b-card-body>
            </b-card>
          </b-col>
        </b-row>
      </b-form>

      <b-form @submit.prevent="storeCheckout">
        <b-row class="mt-5">
          <b-col lg="12" md="12" sm="12">
            <b-card no-body :header="$t('Payment_Gateway')">
              <b-card-body>
                <b-row>

                  <!-- Strip key  -->
                  <b-col lg="6" md="6" sm="12">
                    <b-form-group label="CHECKOUT_PUBLIC_KEY">
                      <b-form-input
                        type="password"
                        v-model="checkout.CHECKOUT_PUBLIC_KEY"
                        :placeholder="$t('LeaveBlank')"
                      />
                    </b-form-group>
                  </b-col>

                  <!-- Strip Secret  -->
                  <b-col lg="6" md="6" sm="12">
                    <b-form-group label="CHECKOUT_SECRET_KEY">
                      <b-form-input
                        type="password"
                        v-model="checkout.CHECKOUT_SECRET_KEY"
                        :placeholder="$t('LeaveBlank')"></b-form-input>
                    </b-form-group>
                  </b-col>

                  <b-col sm="12">
                    <b-form-group label="CHECKOUT_CHANNEL_ID">
                      <b-form-input
                        type="password"
                        v-model="checkout.CHECKOUT_CHANNEL_ID"
                        :placeholder="$t('LeaveBlank')"></b-form-input>
                    </b-form-group>
                  </b-col>

                  <b-col md="6" class="mt-3 mb-3">
                    <label class="switch switch-primary mr-3">
                      {{ $t('active') }}
                      <input type="checkbox" v-model="checkout.is_active">
                      <span class="slider"></span>
                    </label>
                  </b-col>

                  <!-- Remove Stripe Key & Secret-->
                  <b-col md="6" class="mt-3 mb-3">
                    <label class="switch switch-primary mr-3">
                      {{ $t('remove-checkout-keys') }}
                      <input type="checkbox" v-model="checkout.delete_checkout">
                      <span class="slider"></span>
                    </label>
                  </b-col>

                  <b-col md="12">
                    <b-form-group>
                      <b-button variant="primary" type="submit">
                        <i class="i-Yes me-2 font-weight-bold"></i>
                        {{ $t('submit') }}
                      </b-button>
                    </b-form-group>
                  </b-col>
                </b-row>
              </b-card-body>
            </b-card>
          </b-col>
        </b-row>
      </b-form>
    </validation-observer>

  </div>
</template>

<script>
import { mapActions, mapGetters } from "vuex";
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "Payment Gateway"
  },
  data() {
    return {
      isLoading: true,
      stripe: {
        STRIPE_KEY: "",
        STRIPE_SECRET: "",
        is_active: false,
        delete_stripe: false
      },
      checkout: {
        CHECKOUT_PUBLIC_KEY: '',
        CHECKOUT_SECRET_KEY: '',
        CHECKOUT_CHANNEL_ID: '',
        is_active: false,
        delete_checkout: false
      }
    };
  },

  methods: {
    ...mapActions(["refreshUserPermissions"]),

    validation() {
      this.$refs.formPayment.validate().then(success => {
        if (!success) {
          return this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
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

    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    //---------------------------------- Update Payment Gateway ----------------\\
    storeStripe() {
      this.validation();
      NProgress.start();
      NProgress.set(0.1);

      axios.post("settings/payment-gateway", {
        name: 'stripe',
        keys: {
          CHECKOUT_PUBLIC_KEY: this.stripe.STRIPE_KEY,
          CHECKOUT_SECRET_KEY: this.stripe.STRIPE_SECRET
        },
        is_active: this.stripe.is_active,
        delete: this.stripe.delete_stripe
      }).then(response => {
        Fire.$emit("Event_payment");
        this.makeToast(
          "success",
          this.$t("Successfully_Updated"),
          this.$t("Success")
        );
        NProgress.done();
      }).catch(error => {
        NProgress.done();
        this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
      });
    },

    storeCheckout() {
      this.validation();
      NProgress.start();
      NProgress.set(0.1);

      axios.post("settings/payment-gateway", {
        name: 'checkout',
        keys: {
          CHECKOUT_PUBLIC_KEY: this.checkout.CHECKOUT_PUBLIC_KEY,
          CHECKOUT_SECRET_KEY: this.checkout.CHECKOUT_SECRET_KEY,
          CHECKOUT_CHANNEL_ID: this.checkout.CHECKOUT_CHANNEL_ID,
        },
        is_active: this.checkout.is_active,
        delete: this.checkout.delete_checkout
      }).then(response => {
        Fire.$emit("Event_payment");
        this.makeToast(
          "success",
          this.$t("Successfully_Updated"),
          this.$t("Success")
        );
        NProgress.done();
      }).catch(error => {
        NProgress.done();
        this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
      });
    },

    //---------------------------------- GET Payment_Gateway ----------------\\
    indexPaymentGateway() {
      axios.get("settings/payment-gateway").then(response => {
        this.stripe = response.data.gateway.stripe;
        this.paymob = response.data.gateway.paymob;
        this.isLoading = false;
      }).catch(error => {
        this.isLoading = false;
      });
    },



  }, //end Methods

  //----------------------------- Created function-------------------

  created: function () {
    this.indexPaymentGateway();

    Fire.$on("Event_payment", () => {
      this.indexPaymentGateway();
    });
  }
};
</script>
