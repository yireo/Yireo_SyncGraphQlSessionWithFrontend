# Yireo SyncGraphQlSessionWithFrontend
Magento 2 module to sync a GraphQL session with the regular session

## Proof of Concept
Use a GraphQL client within React to generate a customer token:

```graphql
mutation {
  createEmptyCart
}
```

The result might be something like the following:

```json
vGS4ZLj6LkFVrH5CkAPEapLhgfbsoQKF
```

Now, add a product to the cart (in this case with a product SKU `24-MB04`:

```graphql
mutation {
  addSimpleProductsToCart(input: {
    cart_id: "mGLHJNxb73Pw2HmT1sar9IspLwKPLoaL", 
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

There is now one product in the cart of the GraphQL session, but still zero in the original frontend session.

Now navigate to a URL `http://magento/checkout/cart?graphql_token=mGLHJNxb73Pw2HmT1sar9IspLwKPLoaL` or `http://magento/checkout=graphql_token=mGLHJNxb73Pw2HmT1sar9IspLwKPLoaL`. The sessions are now in sync.

## Todo
- Allow for the syncing to happen on any page
- Redirect to the same page to remove the GraphQL token from the URL
- Allow for `graphql_token` to be customized
