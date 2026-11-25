# Yireo SyncGraphQlSessionWithFrontend

<!-- badges.specs.start -->
<div class="github-specs-badges">

![Magento version](https://img.shields.io/badge/Magento-2.4.6%20%7C%202.4.9-orange)
![PHP version](https://img.shields.io/badge/PHP-8.2%E2%80%938.5-777BB4)
![License](https://img.shields.io/badge/License-OSL--3.0-blue)
![Latest Version](https://img.shields.io/packagist/v/yireo/magento2-sync-graph-ql-session-with-frontend)

</div>
<!-- badges.specs.end -->

Magento 2 module to sync the guest cart between the GraphQL session and the Knockout session.

## Summary
- The GraphQL cart token (aka `masked_id`) is added to the CustomerDatas section `cart` of the Knockout frontend (`customerData.get('cart')`) which is automatically synced by Knockout to local storage;
- Within local storage, you can fetch the GraphQL token again from `mage-cache-storage.cart.masked_id`, for instance if you are switching from a regular Knockout frontend to a React-based checkout;

## Kudos
Thanks to Willem Wigman for coming up with the idea for putting the cart token in the section data.

## Proof-of-Concept for cart
Use a GraphQL client within React to generate a cart token:

```graphql
mutation {
  createEmptyCart
}
```

The result might be something like the following:

```json
vGS4ZLj6LkFVrH5CkAPEapLhgfbsoQKF
```

You should now be able to navigate to the Knockout frontend. After making a modification to the cart (adding a new item, changing the quantity or just wiping out local storage), you can then inspect the local storage entry `mage-cache-storage.cart.masked_id`: It should hold the same token as mentioned above.

If you add a product to the cart in the Knockout frontend, the same product should be there in the GraphQL session as well:
```graphql

query cart($cartId: String!) {
  cart(cart_id: $cartId){
    id
    items {
      product {
        sku
      }
    }
  }
}
```

Now, add a product to the cart (in this case with a product SKU `24-MB04`:

```graphql
mutation addToCart($cartId: String!) {
  addSimpleProductsToCart(input: {
    cart_id: $cartId, 
    cart_items: [{data: {quantity: 1, sku: "24-MB04"}}]
  }) {
    cart {
      id
      total_quantity
      items {
        id
        prices {
          row_total {
            currency
            value
          }
        }
        product {
          url_key
        }
      }
    }
  }
}
```

Next, head over to the Knockout frontend, wipe out local storage `mage-cache-storage.cart` (or do this in Knockout via `customerData.clear()`) and inspect the cart again.

## Security
- The `masked_id` is only added for guest carts. For a guest cart, the masked ID is the only credential that GraphQL needs to read and modify that cart (email, addresses, placing the order). Because it is stored in local storage, any XSS or compromised third-party script on your site can read it. Keep a strict Content Security Policy and be careful with third-party scripts.
- For customer carts, no `masked_id` is added: GraphQL requires a customer token for those carts anyway.
- When using a headless frontend on another origin, keep the CORS allow-list strict and never combine `Access-Control-Allow-Credentials: true` with a wildcard or reflected origin, because `customer/section/load` would then be readable cross-origin.
- Older versions of this module also added the GraphQL customer token to the `customer` section (`mage-cache-storage.customer.customer_token`). This was removed, because it exposed a bearer token to JavaScript (also to visitors with only a persistent "Remember Me" session) and returned tokens that were revoked or issued to other devices. To get a customer token in a headless frontend, use the `generateCustomerToken` mutation instead.

## Current status

<!-- badges.test.start -->
<div class="github-pipeline-badges">

![Static Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_SyncGraphQlSessionWithFrontend/static-tests.yml?label=static-tests)
![Unit Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_SyncGraphQlSessionWithFrontend/unit-tests.yml?label=unit-tests)
![Integration Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_SyncGraphQlSessionWithFrontend/integration-tests.yml?label=integration-tests)
![Playwright](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_SyncGraphQlSessionWithFrontend/playwright.yml?label=playwright)
![DI Compilation](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_SyncGraphQlSessionWithFrontend/compile.yml?label=compile)

</div>
<!-- badges.test.end -->
