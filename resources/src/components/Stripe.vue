<template>
  <section>
    <div id="card-element">
    </div>
    <div id="card-errors" class="is-invalid" role="alert"></div>
  </section>
</template>

<script>
import { loadStripe } from "@stripe/stripe-js";

export default {
  props: {
    STRIPE_KEY: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      stripe: {}
    }
  },
  methods: {
    async loadStripe_payment() {
      this.stripe = await loadStripe(this.STRIPE_KEY);
      const elements = this.stripe.elements();
      this.cardElement = elements.create("card", {
        classes: {
          base: "bg-gray-100 rounded border border-gray-300 focus:border-indigo-500 text-base outline-none text-gray-700 p-3 leading-8 transition-colors duration-200 ease-in-out"
        },
        hidePostalCode: true
      });
      this.cardElement.mount("#card-element");
    },

    payment() {
      return this.stripe.createToken(this.cardElement);
    }
  },
  mounted() {
    this.loadStripe_payment();
  }
};
</script>
