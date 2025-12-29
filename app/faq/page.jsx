import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";

const faqs = [
  {
    question: "How long do your products last?",
    answer:
      "Most items are best used within 6 to 12 months of opening. Because we avoid heavy preservatives, shelf life can be shorter depending on the blend. Store in a cool, dry place away from direct sun and heat.",
  },
  {
    question: "Do you use preservatives?",
    answer:
      "We keep formulas as natural as possible and use minimal preservatives where needed. This helps maintain the integrity of botanicals but means products should be used within their best-before window.",
  },
  {
    question: "Can I request a custom blend?",
    answer:
      "Yes. We offer custom tinctures and horseshoe keepsakes. Contact us with your needs and we will confirm timing, pricing, and details before we begin.",
  },
  {
    question: "Are your products safe for sensitive skin?",
    answer:
      "Everyone is different. We recommend patch testing on a small area first and discontinuing use if irritation occurs.",
  },
  {
    question: "What if my order arrives damaged?",
    answer:
      "Please email us within 7 days with a photo and your order number. We will arrange a replacement or refund for items damaged in transit.",
  },
  {
    question: "Do you ship outside New Zealand?",
    answer:
      "At this time we ship within New Zealand only. If you are overseas, contact us and we can discuss options.",
  },
  {
    question: "Are your products vegan or cruelty free?",
    answer:
      "Most products are plant based, but some include beeswax or honey. If you have specific preferences, get in touch and we will guide you to the right options.",
  },
  {
    question: "Do you offer refunds for change of mind?",
    answer:
      "Unopened items can be returned within 14 days. For hygiene reasons, opened products are not eligible for change of mind returns. See our Refunds and CGA policy for details.",
  },
];

const FaqPage = () => {
  return (
    <>
      <Navbar />
      <main className="px-6 md:px-16 lg:px-32 py-14">
        <div className="max-w-3xl">
          <p className="section-kicker">FAQ</p>
          <h1 className="section-title mt-2">Frequently asked questions</h1>
          <p className="mt-3 text-sm md:text-base text-ink-500">
            Answers to common questions about our products, shelf life, and ordering.
          </p>
        </div>

        <div className="mt-10 space-y-4 max-w-3xl">
          {faqs.map((faq) => (
            <details
              key={faq.question}
              className="group rounded-2xl border border-linen-100/70 bg-linen-50/80 p-5"
            >
              <summary className="flex cursor-pointer list-none items-center justify-between text-base font-medium text-ink-900">
                <span>{faq.question}</span>
                <span className="text-ink-500 group-open:rotate-45 transition">+</span>
              </summary>
              <p className="mt-3 text-sm md:text-base text-ink-500">
                {faq.answer}
              </p>
            </details>
          ))}
        </div>
      </main>
      <Footer />
    </>
  );
};

export default FaqPage;
