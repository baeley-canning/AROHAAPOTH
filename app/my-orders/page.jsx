"use client";
import Footer from "@/components/Footer";
import Navbar from "@/components/Navbar";
import { useEffect, useMemo, useState } from "react";
import { useSearchParams } from "next/navigation";

const statusStyles = {
  paid: "border-sage-600/20 bg-sage-600/10 text-sage-700",
  pending: "border-linen-100/70 bg-linen-50/80 text-ink-600",
  failed: "border-rose-200 bg-rose-50 text-rose-700",
  refunded: "border-violet-200 bg-violet-50 text-violet-700",
  refunding: "border-amber-200 bg-amber-50 text-amber-700",
  expired: "border-ink-300/40 bg-ink-100/60 text-ink-600",
};

const formatMoney = (amount, currency) => {
  const code = (currency || "nzd").toLowerCase();
  const symbol = code === "nzd" ? "NZ$" : "$";
  return `${symbol}${(Number(amount || 0) / 100).toFixed(2)}`;
};

const MyOrders = () => {
  const searchParams = useSearchParams();
  const [orderRef, setOrderRef] = useState("");
  const [email, setEmail] = useState("");
  const [result, setResult] = useState(null);
  const [error, setError] = useState("");
  const [isLoading, setIsLoading] = useState(false);

  const basePath = (process.env.NEXT_PUBLIC_BASE_PATH || "").replace(/\/$/, "");

  useEffect(() => {
    const ref = (searchParams.get("ref") || "").trim();
    const emailParam = (searchParams.get("email") || "").trim();
    if (ref) setOrderRef(ref);
    if (emailParam) setEmail(emailParam);
  }, [searchParams]);

  const statusClass = useMemo(() => {
    const status = (result?.status || "pending").toLowerCase();
    return statusStyles[status] || statusStyles.pending;
  }, [result]);

  const handleSubmit = async (event) => {
    event.preventDefault();
    setError("");
    setResult(null);
    setIsLoading(true);

    try {
      const response = await fetch(`${basePath}/api/order-lookup.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          orderRef: orderRef.trim(),
          email: email.trim(),
        }),
      });
      const data = await response.json();
      if (!response.ok) {
        throw new Error(data?.error || "Unable to find that order.");
      }
      setResult(data);
    } catch (fetchError) {
      setError(fetchError?.message || "Unable to find that order.");
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <>
      <Navbar />
      <div className="flex flex-col justify-between px-6 md:px-16 lg:px-32 py-14 min-h-screen">
        <div className="space-y-6 max-w-3xl">
          <div>
            <h2 className="text-2xl font-medium text-ink-900">Order lookup</h2>
            <p className="text-sm md:text-base text-ink-500 mt-2">
              We keep checkout simple with no accounts. Use your order reference and the
              email from checkout to view your order status.
            </p>
          </div>

          <form onSubmit={handleSubmit} className="card-surface px-6 py-6 space-y-4">
            <div>
              <label className="block text-xs uppercase tracking-[0.2em] text-ink-500">Order reference</label>
              <input
                type="text"
                value={orderRef}
                onChange={(event) => setOrderRef(event.target.value)}
                placeholder="AO12AB34"
                className="mt-2 w-full rounded-full border border-linen-100/70 bg-white px-4 py-3 text-sm text-ink-700"
                required
              />
            </div>
            <div>
              <label className="block text-xs uppercase tracking-[0.2em] text-ink-500">Checkout email</label>
              <input
                type="email"
                value={email}
                onChange={(event) => setEmail(event.target.value)}
                placeholder="you@example.com"
                className="mt-2 w-full rounded-full border border-linen-100/70 bg-white px-4 py-3 text-sm text-ink-700"
                required
              />
            </div>
            <button type="submit" className="btn-primary" disabled={isLoading}>
              {isLoading ? "Checking..." : "Check order"}
            </button>
            {error ? <p className="text-sm text-rose-600">{error}</p> : null}
          </form>

          {result ? (
            <div className="card-surface px-6 py-6 space-y-4">
              <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                  <p className="text-xs uppercase tracking-[0.2em] text-ink-500">Order</p>
                  <p className="text-lg font-semibold text-ink-900">{result.orderRef}</p>
                  <p className="text-xs text-ink-500 mt-1">{result.createdAt}</p>
                </div>
                <span className={`inline-flex items-center rounded-full border px-3 py-1 text-xs uppercase tracking-[0.2em] ${statusClass}`}>
                  {result.status}
                </span>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                <div className="rounded-2xl border border-linen-100/70 bg-linen-50/80 p-4">
                  <p className="text-ink-900 font-medium">Total</p>
                  <p className="mt-2 text-ink-700">{formatMoney(result.amountTotal, result.currency)}</p>
                  {result.amountRefunded ? (
                    <p className="text-xs text-ink-500 mt-2">
                      Refunded: {formatMoney(result.amountRefunded, result.currency)}
                    </p>
                  ) : null}
                </div>
                <div className="rounded-2xl border border-linen-100/70 bg-linen-50/80 p-4">
                  <p className="text-ink-900 font-medium">Payment</p>
                  <p className="mt-2 text-ink-700">{result.paymentStatus || "Pending"}</p>
                  {result.refundStatus ? (
                    <p className="text-xs text-ink-500 mt-2">Refund: {result.refundStatus}</p>
                  ) : null}
                </div>
                <div className="rounded-2xl border border-linen-100/70 bg-linen-50/80 p-4">
                  <p className="text-ink-900 font-medium">Fulfillment</p>
                  <p className="mt-2 text-ink-700">{result.fulfillmentStatus || "Pending"}</p>
                  {result.trackingUrl ? (
                    <a
                      className="mt-2 inline-flex text-xs text-ink-700 underline underline-offset-4"
                      href={result.trackingUrl}
                      target="_blank"
                      rel="noopener"
                    >
                      Tracking link
                    </a>
                  ) : (
                    <p className="text-xs text-ink-500 mt-2">Tracking appears after dispatch.</p>
                  )}
                </div>
                <div className="rounded-2xl border border-linen-100/70 bg-linen-50/80 p-4">
                  <p className="text-ink-900 font-medium">Shipping</p>
                  <p className="mt-2 text-ink-700 whitespace-pre-line">
                    {result.shippingAddress || "Not yet available."}
                  </p>
                </div>
              </div>

              <div>
                <p className="text-sm font-medium text-ink-900">Items</p>
                <div className="mt-3 space-y-2">
                  {(result.items || []).map((item, index) => (
                    <div key={`${item.product_name}-${index}`} className="flex items-center justify-between text-sm text-ink-700">
                      <span>{item.product_name} x {item.quantity}</span>
                      <span>{formatMoney(item.line_total, result.currency)}</span>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          ) : null}

          <div className="rounded-2xl border border-linen-100/70 bg-linen-50/80 p-5 text-sm text-ink-600">
            <p className="text-ink-900 font-medium">Need help?</p>
            <p className="mt-2">
              Email{" "}
              <a className="text-ink-900 hover:text-ink-700 transition" href="mailto:arohaapothecary@gmail.com">
                arohaapothecary@gmail.com
              </a>{" "}
              with your order reference and we will help.
            </p>
          </div>
        </div>
      </div>
      <Footer />
    </>
  );
};

export default MyOrders;
