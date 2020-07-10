# Yireo SyncGraphQlSessionWithFrontend
Magento 2 module to sync from the GraphQL session to the Knockout session and from the Knockout session to the GraphQL session.

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

## Proof-of-Concept for customer
Use a GraphQL client within React to generate a customer token:
```graphql
mutation login($email:String!, $password:String!) {
  generateCustomerToken(email:$email, password:$password) {
    token
  }
}
```

Next, login as the same customer in the Knockout frontend. The local storage item `mage-cache-storage.customer.customer_token` now refers to the same GraphQL token.

This probably needs some enhancement, so that you are logged in right away in either frontend, without loosing security.
