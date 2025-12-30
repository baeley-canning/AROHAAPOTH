import Footer from "@/components/Footer";
import Navbar from "@/components/Navbar";

const MyOrders = () => {
  return (
    <>
      <Navbar />
      <div className="flex flex-col justify-between px-6 md:px-16 lg:px-32 py-14 min-h-screen">
        <div className="space-y-5 max-w-2xl">
          <h2 className="text-2xl font-medium text-ink-900">Order updates</h2>
          <p className="text-sm md:text-base text-ink-500">
            We do not require accounts. Your receipt and shipping updates are emailed
            by Stripe after checkout. If you need help, reply to your receipt or
            contact us directly.
          </p>
          <div className="rounded-2xl border border-linen-100/70 bg-linen-50/80 p-5 text-sm text-ink-600">
            <p className="text-ink-900 font-medium">Need an update?</p>
            <p className="mt-2">
              Email{" "}
              <a className="text-ink-900 hover:text-ink-700 transition" href="mailto:arohaapothecary@gmail.com">
                arohaapothecary@gmail.com
              </a>{" "}
              with your order number.
            </p>
          </div>
        </div>
      </div>
      <Footer />
    </>
  );
};

export default MyOrders;
