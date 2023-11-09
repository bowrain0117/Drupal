/**
 * store.js
 * Stripe Payments Demo. Created by Romain Huet (@romainhuet)
 * and Thorsten Schaeff (@thorwebdev).
 *
 * Representation of products, and line items stored in Stripe.
 * Please note this is overly simplified class for demo purposes (all products
 * are loaded for convenience, there is no cart management functionality, etc.).
 * A production app would need to handle this very differently.
 */

class Store {
  constructor() {
    this.lineItems = [];
    this.products = {};
    this.productsFetchPromise = null;
    this.config = null;
    this.productDetails = [
      {
        key: 'increment',
        detail: `LIMITED TIME PRODUCT! Featuring a sleek and modern design, the Jetstream 4&1 Bamboo is adorned with an aluminum body, exuding elegance and sophistication. Its bamboo wood grip ensures a comfortable and secure hold, allowing you to write with precision and ease. The quick-drying, archival-quality hybrid ink technology guarantees smudge-resistant writing, making it perfect for any occasion.`,
        skuLabel: 'SKU 303479000',
        inkColors: ['black-ink']
      },
      {
        key: 'pins',
        detail: `Sleek sophistication brings a high-end appeal to Jetstream Premier. Its innovative hybrid ink combines the satisfyingly smooth, vivid writing of a gel pen with the quick-drying, smudge-resistant properties of a ballpoint.

        Indelible hybrid ink blends the smoothness and vibrancy of a gel pen with the fast drying speed of a ballpoint
        Acid-free ballpoint pens are great for all of your personal and professional writing needs
        Quick-drying ink technology resists smudges and smears, making it an ideal pen for left-handed writers
        A sophisticated design with embossed grip and stainless steel accents give this biro pen a stylish look.`,
        skuLabel: 'SKU 1807315',
        inkColors:['gray-ink']
      }
    ]
    this.displayPaymentSummary();
  }

  
  // Compute the total for the payment based on the line items (SKUs and quantity).
  getPaymentTotal() {
    /*return Object.values(this.lineItems).reduce(
      (total, {product, sku, quantity}) =>
        total + quantity * this.products[product].skus.data[0].price,
      0
    );*/
    return 300;
  }

  // Expose the line items for the payment using products and skus stored in Stripe.
  getLineItems() {
    let items = [];
    this.lineItems.forEach(item =>
      items.push({
        type: 'sku',
        parent: item.sku,
        quantity: item.quantity,
      })
    );
    return items;
  }

  async addCustomer(customerData){
    try {
      const response = await fetch('/saveCustomers', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(customerData),
      });
      const data = await response.json();
      if (data.error) {
        return {error: data.error};
      } else {
        return data;
      }
    } catch (err) {
      return {error: err.message};
    }
  }
  // Retrieve the configuration from the API.
  async getConfig() {
    try {
      const response = await fetch('/config');
      this.config = await response.json();
      
      if (config.stripePublishableKey.includes('live')) {
        // Hide the demo notice if the publishable key is in live mode.
        document.querySelector('#order-total .demo').style.display = 'none';
      }
      
      return this.config;
    } catch (err) {
      return {error: err.message};
    }
  }

  // Retrieve a SKU for the Product where the API Version is newer and doesn't include them on v1/product
  async loadSkus(product_id) {
    try {
      const response = await fetch(`/products/${product_id}/skus`);
      const skus = await response.json();
      this.products[product_id].skus = skus;
    } catch (err) {
      return {error: err.message};
    }
  }

  // Load the product details.
  loadProducts() {
    if (!this.productsFetchPromise) {
      this.productsFetchPromise = new Promise(async resolve => {
        const productsResponse = await fetch('/products');
        const products = (await productsResponse.json()).data;
        if (!products.length) {
          throw new Error(
            'No products on Stripe account! Make sure the setup script has run properly.'
          );
        }
        // Check if we have SKUs on the product, otherwise load them separately.
        for (const product of products) {
          this.products[product.id] = product;
          if (!product.skus) {
            await this.loadSkus(product.id);
          }
        }
        resolve();
      });
    }
    return this.productsFetchPromise;
  }

  // Create the PaymentIntent with the cart details.
  async createPaymentIntent(currency, items, card,  customer) {
    try {
      const response = await fetch('/payment_intents', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
          currency,
          items,
          card,
          customer
        }),
      });
      const data = await response.json();
      if (data.error) {
        return {error: data.error};
      } else {
        return data;
      }
    } catch (err) {
      return {error: err.message};
    }
  }

  // Create the PaymentIntent with the cart details.
  async updatePaymentIntentWithShippingCost(
    paymentIntent,
    items,
    shippingOption
  ) {
    try {
      const response = await fetch(
        `/payment_intents/${paymentIntent}/shipping_change`,
        {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({
            shippingOption,
            items,
          }),
        }
      );
      const data = await response.json();
      if (data.error) {
        return {error: data.error};
      } else {
        return data;
      }
    } catch (err) {
      return {error: err.message};
    }
  }

  // Update the PaymentIntent with the the currency and payment value.
  async updatePaymentIntentCurrency(
    paymentIntent,
    currency,
    payment_methods,
  ) {
    try {
      const response = await fetch(
        `/payment_intents/${paymentIntent}/update_currency`,
        {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({
            currency,
            payment_methods,
          }),
        }
      );
      const data = await response.json();
      if (data.error) {
        return {error: data.error};
      } else {
        return data;
      }
    } catch (err) {
      return {error: err.message};
    }
  }

  // Format a price (assuming a two-decimal currency like EUR or USD for simplicity).
  formatPrice(amount, currency) {
    let price = (amount / 100).toFixed(2);
    let numberFormat = new Intl.NumberFormat(['en-US'], {
      style: 'currency',
      currency: currency,
      currencyDisplay: 'symbol',
    });
    return numberFormat.format(price);
  }

  // Manipulate the DOM to display the payment summary on the right panel.
  // Note: For simplicity, we're just using template strings to inject data in the DOM,
  // but in production you would typically use a library like React to manage this effectively.
  async displayPaymentSummary() {
    // Fetch the products from the store to get all the details (name, price, etc.).
    await this.loadProducts();
    const orderItems = document.getElementById('order-items');
    const orderTotal = document.getElementById('order-total');
    let currency;
    console.log(this.projects);
    // Build and append the line items to the payment summary.
    for (let [id, product] of Object.entries(this.products)) {
      const randomQuantity = (min, max) => {
        min = Math.ceil(min);
        max = Math.floor(max);
        return Math.floor(Math.random() * (max - min + 1)) + min;
      };
      //const quantity = randomQuantity(1, 2);
      const quantity = 2;
      let sku = product.skus.data[0];
      let skuPrice = this.formatPrice(sku.price, sku.currency);
      let lineItemPrice = this.formatPrice(sku.price * quantity, sku.currency);
      let lineItem = document.createElement('div');
      
      let prodImg = document.createElement('img');
      prodImg.classList.add("image");
      prodImg.src = `/images/products/${product.id}.png`;

      let linePriceItem = document.createElement('div');
      linePriceItem.classList.add('d-flex');
      linePriceItem.classList.add('justify-content-between');
      linePriceItem.classList.add('w-100');
      
      let lineLabel = document.createElement('div');
      lineLabel.classList.add('label')
      lineLabel.innerHTML = `<p class="product">${product.name}</p>
        <p class="sku">${Object.values(sku.attributes).join(' ')}</p>`;
      
      let linePriceLabel = document.createElement('div');
      linePriceLabel.classList.add('label');
      linePriceLabel.classList.add('text-end');
      linePriceLabel.innerHTML = `<p ><span class="count price prod-Price">${skuPrice}</span> <span class="count price"> x ${quantity}<span> <span class="price prod-Price">${lineItemPrice}</span></p>`;

      let moreDetailBtn = document.createElement('span');
      moreDetailBtn.classList.add('prodDetaill-btn');
      moreDetailBtn.id = id;
      moreDetailBtn.innerHTML = "More Detail";
      linePriceLabel.append(moreDetailBtn);
      let prodDetail = this.productDetails.find(item => item.key == id);
      moreDetailBtn.addEventListener('click', function(){
        var detailHTML = '<h6 class="d-flex justify-content-between"><span>DESCRIPTION</span> <span>'+prodDetail.skuLabel+'</span></h6>' +
        '<p class="text-start"><span>'+prodDetail.detail+'</span> </p>'+
        '<div class="d-flex align-items-center"> <label>Ink Colors: </label>';
        prodDetail.inkColors.map(colorItem => {
          detailHTML += '<div class="ink-bolt '+colorItem+' mx-1"><div>';
        });
        detailHTML += '</div>';
        Swal.fire({
          title: product.name,
          html: detailHTML,
          imageUrl: `/images/products/${product.id}.png`,
          imageWidth: 400,
          imageHeight: 100,
          imageAlt: 'Custom image',
        })
      })
      

      linePriceItem.append(lineLabel);
      linePriceItem.append(linePriceLabel);

      lineItem.classList.add('line-item');
      lineItem.classList.add('prod-item');
      /*lineItem.innerHTML = `
        <img class="image" src="/images/products/${product.id}.png" alt="${product.name}">
        <div class="d-flex justify-content-between w-100">
          <div class="label">
          <p class="product">Retail Value</p>
          <!--  <p class="product">${product.name}</p>-->
            <p class="sku">${Object.values(sku.attributes).join(' ')}</p>
          </div>
          <div class="label text-end">
          <!--<p class="count">${quantity} x ${skuPrice}</p>-->
          <p class="price">${lineItemPrice}</p>
          ${moreDetailBtn.innerHTML}
          </div>
        </div>`;*/
        lineItem.append(prodImg);
        lineItem.append(linePriceItem);
      orderItems.appendChild(lineItem);
      currency = sku.currency;
      this.lineItems.push({
        product: product.id,
        sku: sku.id,
        quantity,
      });
    }
    // Add the subtotal and total to the payment summary.
    //const total = this.formatPrice(this.getPaymentTotal(), currency);
    const total = this.formatPrice('395', currency);
    const subtotal = this.formatPrice('0', currency);
    orderTotal.querySelector('[data-subtotal]').innerText = subtotal;
    orderTotal.querySelector('[data-shipping]').innerText = total;
    orderTotal.querySelector('[data-total]').innerText = total;
  }
}

window.store = new Store();
