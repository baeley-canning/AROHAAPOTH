import { useAppContext } from "@/context/AppContext";
import React, { useState } from "react";

const OrderSummary = () => {

  const { currency, getCartCount, getCartAmount, cartItems } = useAppContext();
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState("");

  const cartCount = getCartCount();
  const cartAmount = getCartAmount();
  const basePath = (process.env.NEXT_PUBLIC_BASE_PATH || "").replace(/\/$/, "");

  const buildLineItems = () => {
    return Object.entries(cartItems)
      .filter(([, quantity]) => quantity > 0)
      .map(([id, quantity]) => ({ id, quantity }));
  };

  const createOrder = async () => {
    setError("");
    const items = buildLineItems();

    if (!items.length) {
      setError("Your cart is empty.");
      return;
    }

    setIsLoading(true);
    try {
      const response = await fetch(`${basePath}/stripe/create-checkout-session.php`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ items }),
      });
      const data = await response.json();
      if (!response.ok) {
        throw new Error(data?.error || "Checkout failed.");
      }
      if (data?.url) {
        window.location.href = data.url;
        return;
      }
      throw new Error("Checkout failed.");
    } catch (err) {
      setError(err?.message || "Checkout unavailable right now.");
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="w-full md:w-96 bg-linen-50/80 border border-linen-100/70 p-5 rounded-2xl">
      <h2 className="text-xl md:text-2xl font-medium text-ink-900">
        Order Summary
      </h2>
      <p className="text-sm text-ink-500 mt-2">
        Shipping and contact details are collected securely at checkout.
      </p>
      <hr className="border-ink-500/20 my-5" />

      <div className="space-y-4">
        <div className="flex justify-between text-base font-medium">
          <p className="uppercase text-ink-500">Items {cartCount}</p>
          <p className="text-ink-900">{currency}{cartAmount}</p>
        </div>
        <div className="flex justify-between">
          <p className="text-ink-500">Shipping (NZ)</p>
          <p className="font-medium text-ink-900">Free</p>
        </div>
        <div className="flex justify-between text-lg md:text-xl font-medium border-t pt-3">
          <p>Total</p>
          <p>{currency}{cartAmount}</p>
        </div>
      </div>

      {error ? (
        <p className="text-sm text-clay-500 mt-4">{error}</p>
      ) : null}

      <div className="mt-4 text-xs text-ink-500 space-y-1">
        <p>Secure checkout by Stripe.</p>
        <p>Email receipt sent automatically.</p>
      </div>

      <button
        onClick={createOrder}
        disabled={isLoading || cartCount === 0}
        className="w-full bg-sage-600 text-linen-50 py-3 mt-5 hover:bg-sage-700 disabled:opacity-70 disabled:cursor-not-allowed"
      >
        {isLoading ? "Opening Stripe..." : "Checkout with Stripe"}
      </button>
    </div>
  );
};

export default OrderSummary;
