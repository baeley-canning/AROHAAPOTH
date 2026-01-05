'use client'
import React from "react";
import { assets } from "@/assets/assets";
import OrderSummary from "@/components/OrderSummary";
import Image from "next/image";
import Navbar from "@/components/Navbar";
import { useAppContext } from "@/context/AppContext";

const Cart = () => {

  const { products, router, cartItems, addToCart, updateCartQuantity, clearCart, getCartCount, currency } = useAppContext();
  const cartCount = getCartCount();

  return (
    <>
      <Navbar />
      <div className="flex flex-col md:flex-row gap-10 px-6 md:px-16 lg:px-32 pt-14 mb-20">
        <div className="flex-1">
          <div className="flex flex-wrap items-center justify-between gap-4 mb-8 border-b border-ink-500/20 pb-6">
            <div>
              <p className="text-2xl md:text-3xl text-ink-500">
                Your <span className="font-medium text-clay-500">Cart</span>
              </p>
              <p className="text-sm text-ink-500/80 mt-1">{cartCount} Items</p>
            </div>
            {cartCount > 0 ? (
              <button
                className="text-xs uppercase tracking-[0.2em] text-clay-500 hover:text-ink-900 transition"
                onClick={clearCart}
              >
                Clear cart
              </button>
            ) : null}
          </div>
          {cartCount === 0 ? (
            <div className="rounded-2xl border border-linen-100/70 bg-linen-50/80 p-8 text-center">
              <p className="text-ink-900 text-lg font-medium">Your cart is empty.</p>
              <p className="text-sm text-ink-500 mt-2">
                Explore our small-batch blends and curated bundles.
              </p>
              <button
                onClick={() => router.push("/all-products")}
                className="btn-primary mt-5"
              >
                Shop all products
              </button>
            </div>
          ) : (
            <>
              <div className="overflow-x-auto">
                <table className="min-w-full table-auto">
                  <thead className="text-left">
                    <tr>
                      <th className="text-nowrap pb-6 md:px-4 px-1 text-ink-500 font-medium">
                        Product Details
                      </th>
                      <th className="pb-6 md:px-4 px-1 text-ink-500 font-medium">
                        Price
                      </th>
                      <th className="pb-6 md:px-4 px-1 text-ink-500 font-medium">
                        Quantity
                      </th>
                      <th className="pb-6 md:px-4 px-1 text-ink-500 font-medium">
                        Subtotal
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    {Object.keys(cartItems).map((itemId) => {
                      const product = products.find(product => product._id === itemId);

                      if (!product || cartItems[itemId] <= 0) return null;

                      return (
                        <tr key={itemId}>
                          <td className="flex items-center gap-4 py-4 md:px-4 px-1">
                            <div>
                              <div className="rounded-xl overflow-hidden bg-linen-50/90 border border-linen-100/70 p-2">
                                <Image
                                  src={product.image[0]}
                                  alt={product.name}
                                  className="w-16 h-auto object-cover"
                                  width={1280}
                                  height={720}
                                />
                              </div>
                              <button
                                className="md:hidden text-xs text-clay-500 mt-1"
                                onClick={() => updateCartQuantity(product._id, 0)}
                              >
                                Remove
                              </button>
                            </div>
                            <div className="text-sm hidden md:block">
                              <p className="text-ink-900">{product.name}</p>
                              {product.price > product.offerPrice ? (
                                <p className="text-xs text-sage-700 mt-1">
                                  You save {currency}{(product.price - product.offerPrice).toFixed(2)}
                                </p>
                              ) : null}
                              <button
                                className="text-xs text-clay-500 mt-1"
                                onClick={() => updateCartQuantity(product._id, 0)}
                              >
                                Remove
                              </button>
                            </div>
                          </td>
                          <td className="py-4 md:px-4 px-1">
                            <div className="flex flex-col">
                              <span className="text-ink-900">{currency}{product.offerPrice}</span>
                              {product.price > product.offerPrice ? (
                                <span className="text-xs text-ink-500 line-through">{currency}{product.price}</span>
                              ) : null}
                            </div>
                          </td>
                          <td className="py-4 md:px-4 px-1">
                            <div className="flex items-center md:gap-2 gap-1">
                              <button onClick={() => updateCartQuantity(product._id, cartItems[itemId] - 1)}>
                                <Image
                                  src={assets.decrease_arrow}
                                  alt="decrease_arrow"
                                  className="w-4 h-4"
                                />
                              </button>
                              <input
                                onChange={e => updateCartQuantity(product._id, e.target.value)}
                                type="number"
                                min="0"
                                value={cartItems[itemId]}
                                aria-label={`${product.name} quantity`}
                                className="w-10 border border-ink-500/20 rounded-md text-center appearance-none bg-white/70 text-ink-900"
                              />
                              <button onClick={() => addToCart(product._id)}>
                                <Image
                                  src={assets.increase_arrow}
                                  alt="increase_arrow"
                                  className="w-4 h-4"
                                />
                              </button>
                            </div>
                          </td>
                          <td className="py-4 md:px-4 px-1 text-ink-500">{currency}{(product.offerPrice * cartItems[itemId]).toFixed(2)}</td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8 text-sm text-ink-600">
                <div className="rounded-2xl border border-linen-100/70 bg-linen-50/80 p-4">
                  <p className="text-ink-900 font-medium">What happens next</p>
                  <p className="mt-1">
                    You will complete checkout on Stripe and receive a receipt by email.
                  </p>
                </div>
                <div className="rounded-2xl border border-linen-100/70 bg-linen-50/80 p-4">
                  <p className="text-ink-900 font-medium">Shipping timeline</p>
                  <p className="mt-1">
                    In-stock items ship within 2-5 business days. NZ only.
                  </p>
                </div>
                <div className="rounded-2xl border border-linen-100/70 bg-linen-50/80 p-4">
                  <p className="text-ink-900 font-medium">Custom orders</p>
                  <p className="mt-1">
                    Made to order. Allow 7-14 days before dispatch.
                  </p>
                </div>
              </div>
            </>
          )}
          <button onClick={()=> router.push('/all-products')} className="group flex items-center mt-6 gap-2 text-clay-500">
            <Image
              className="group-hover:-translate-x-1 transition"
              src={assets.arrow_right_icon_colored}
              alt="arrow_right_icon_colored"
            />
            Continue Shopping
          </button>
        </div>
        {cartCount > 0 ? <OrderSummary /> : null}
      </div>
    </>
  );
};

export default Cart;
