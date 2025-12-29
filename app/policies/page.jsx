import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";

const PoliciesPage = () => {
  return (
    <>
      <Navbar />
      <main className="px-6 md:px-16 lg:px-32 py-14">
        <div className="max-w-3xl">
          <p className="section-kicker">Policies</p>
          <h1 className="section-title mt-2">Refunds, CGA, and product care</h1>
          <p className="mt-3 text-sm md:text-base text-ink-500">
            Clear, straightforward information about returns, consumer rights, and
            looking after your natural products.
          </p>
        </div>

        <div className="mt-10 space-y-10 max-w-3xl text-sm md:text-base text-ink-500">
          <section className="space-y-3">
            <h2 className="text-xl font-semibold text-ink-900">Refunds and returns</h2>
            <p>
              Change of mind returns are accepted on unopened, unused items within 14 days
              of delivery. Please email us first so we can provide return instructions.
            </p>
            <p>
              For hygiene reasons, opened products are not eligible for change of mind
              returns. Custom products and made-to-order pieces are non-refundable unless
              they are faulty.
            </p>
            <p>
              Return shipping costs are the responsibility of the customer. Original
              shipping fees are not refundable.
            </p>
          </section>

          <section className="space-y-3">
            <h2 className="text-xl font-semibold text-ink-900">Faulty or incorrect items</h2>
            <p>
              If your order arrives damaged, faulty, or incorrect, contact us within 7 days
              with your order number and a photo. We will arrange a replacement or refund.
            </p>
          </section>

          <section className="space-y-3">
            <h2 className="text-xl font-semibold text-ink-900">Consumer Guarantees Act (NZ)</h2>
            <p>
              Our goods come with guarantees that cannot be excluded under the Consumer
              Guarantees Act 1993. You are entitled to a replacement or refund for a major
              failure and to compensation for any other reasonably foreseeable loss or damage.
            </p>
            <p>
              This policy is in addition to your rights under the CGA.
            </p>
          </section>

          <section className="space-y-3">
            <h2 className="text-xl font-semibold text-ink-900">Product care and shelf life</h2>
            <p>
              Our products are made with minimal preservatives and natural ingredients.
              Shelf life varies by product, but most are best used within 6 to 12 months
              of opening.
            </p>
            <p>
              Store products in a cool, dry place away from direct sunlight and heat.
              Use clean, dry hands or a spatula to avoid introducing moisture.
            </p>
            <p>
              Natural separation or minor changes in texture and color can occur. If a
              product develops an unusual smell or irritation occurs, discontinue use.
            </p>
          </section>

          <section className="space-y-3">
            <h2 className="text-xl font-semibold text-ink-900">Contact</h2>
            <p>
              For any questions about returns or your order, email us at
              <span className="text-ink-900"> arohaapothecary@gmail.com</span>.
            </p>
          </section>
        </div>
      </main>
      <Footer />
    </>
  );
};

export default PoliciesPage;
