import React from "react";
import { assets } from "@/assets/assets";
import Image from "next/image";
import Link from "next/link";

const Banner = () => {
  return (
    <div className="relative flex flex-col md:flex-row items-center justify-between my-16 rounded-3xl overflow-hidden border border-linen-100/70 bg-linen-50/80" id="custom">
      <div className="absolute -left-20 -top-16 h-56 w-56 rounded-full bg-mist-200/70 blur-3xl"></div>
      <div className="absolute -right-20 -bottom-24 h-64 w-64 rounded-full bg-blush-200/70 blur-3xl"></div>
      <div className="relative flex flex-col items-start justify-center text-left space-y-4 px-6 md:px-12 py-12 md:py-16 max-w-xl">
        <p className="section-kicker">Custom made for you</p>
        <h2 className="section-title">Custom tinctures and horseshoe art</h2>
        <p className="text-sm md:text-base text-ink-500">
          Share your story and we will craft a tailored remedy or a keepsake piece with heart.
        </p>
        <Link href="/#contact" className="btn-primary">
          Start a custom order
        </Link>
      </div>
      <div className="relative flex items-center gap-4 px-6 pb-10 md:pb-0">
        <Image
          className="w-44 md:w-52 drop-shadow-[0_16px_40px_rgba(43,36,31,0.25)]"
          src={assets.aroha_product_tincture}
          alt="Custom tincture bottle"
        />
        <Image
          className="w-44 md:w-52 drop-shadow-[0_16px_40px_rgba(43,36,31,0.25)]"
          src={assets.aroha_product_horseshoe}
          alt="Custom horseshoe art"
        />
      </div>
    </div>
  );
};

export default Banner;
