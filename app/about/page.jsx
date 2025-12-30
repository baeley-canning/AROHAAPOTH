import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";

const AboutPage = () => {
  return (
    <>
      <Navbar />
      <main className="px-6 md:px-16 lg:px-32 py-14">
        <div className="max-w-3xl">
          <p className="section-kicker">About</p>
          <h1 className="section-title mt-2">Aroha Apothecary</h1>
          <p className="mt-3 text-sm md:text-base text-ink-500">
            We are a small-batch apothecary based in a quiet valley in the lower North
            Island of Aotearoa New Zealand. We grow native NZ botanicals on our own land
            and work with professional cultivators who honor the whenua.
          </p>
        </div>

        <div className="mt-10 space-y-10 max-w-3xl text-sm md:text-base text-ink-500">
          <section className="space-y-3">
            <h2 className="text-xl font-semibold text-ink-900">Our approach</h2>
            <p>
              Every blend is mixed by hand, with you in mind and in heart. We lean on
              the wisdom of nature, respectful Maori healing traditions, and the idea
              of kaitiakitanga: caring for the land that cares for us.
            </p>
            <p>
              We keep our ingredients simple and honest. You will find native plants,
              gentle oils, and small-batch methods that honor the season.
            </p>
          </section>

          <section className="space-y-3">
            <h2 className="text-xl font-semibold text-ink-900">Mysticism and ritual</h2>
            <p>
              Some days call for light, others for deep rest. Our rituals carry a touch
              of mysticism: dawn blends for clarity, moonlit balms for calm, and
              slow-crafted keepsakes with your story at the center.
            </p>
          </section>

          <section className="space-y-3">
            <h2 className="text-xl font-semibold text-ink-900">Custom work</h2>
            <p>
              We create custom tinctures and horseshoe art pieces. Share your story and
              we will guide you through timing, pricing, and design. Custom pieces are
              made to order and crafted with care.
            </p>
          </section>

          <section className="space-y-3">
            <h2 className="text-xl font-semibold text-ink-900">Get in touch</h2>
            <p>
              Email us at{" "}
              <a className="text-ink-900 hover:text-ink-700 transition" href="mailto:arohaapothecary@gmail.com">
                arohaapothecary@gmail.com
              </a>{" "}
              to share your story or request a custom order.
            </p>
          </section>
        </div>
      </main>
      <Footer />
    </>
  );
};

export default AboutPage;
