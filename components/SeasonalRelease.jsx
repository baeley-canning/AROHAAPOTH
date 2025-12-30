import React from "react";
import { assets } from "@/assets/assets";
import Image from "next/image";
import Link from "next/link";

const SeasonalRelease = () => {
  return (
    <section className="mt-16 md:mt-20">
      <div className="relative card-surface px-6 md:px-12 py-12 overflow-hidden">
        <div className="absolute -right-20 -top-20 h-48 w-48 rounded-full bg-mist-200/70 blur-3xl"></div>
        <div className="absolute -left-16 -bottom-20 h-56 w-56 rounded-full bg-blush-200/70 blur-3xl"></div>

        <div className="grid lg:grid-cols-[1.1fr_0.9fr] gap-10 items-center">
          <div className="space-y-4">
            <p className="section-kicker">Seasonal release</p>
            <h2 className="section-title">A monthly custom blend, made once.</h2>
            <p className="text-sm md:text-base text-ink-500">
              Each month we craft a limited remedy or ritual guided by the season.
              Quantities are small, and blends change with the harvest.
            </p>
            <div className="flex flex-wrap gap-3 text-sm text-ink-600">
              <span className="rounded-full border border-linen-100/80 bg-linen-50/80 px-4 py-1.5">
                NZ natives + garden botanicals
              </span>
              <span className="rounded-full border border-linen-100/80 bg-linen-50/80 px-4 py-1.5">
                Small batch release
              </span>
              <span className="rounded-full border border-linen-100/80 bg-linen-50/80 px-4 py-1.5">
                Next drop announced by email
              </span>
            </div>
            <Link href="/#contact" className="btn-primary">
              Join the seasonal list
            </Link>
          </div>
          <div className="flex justify-center">
            <Image
              className="w-64 md:w-72 drop-shadow-[0_18px_40px_rgba(43,36,31,0.25)]"
              src={assets.aroha_product_tincture}
              alt="Seasonal blend"
            />
          </div>
        </div>
      </div>
    </section>
  );
};

export default SeasonalRelease;
