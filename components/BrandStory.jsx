import React from "react";
import { assets } from "@/assets/assets";
import Image from "next/image";
import Link from "next/link";

const BrandStory = () => {
  return (
    <section className="relative mt-16 md:mt-20" id="story">
      <div className="absolute -left-16 top-10 h-40 w-40 rounded-full bg-mist-200/60 blur-3xl"></div>
      <div className="absolute -right-10 bottom-0 h-44 w-44 rounded-full bg-blush-200/70 blur-3xl"></div>

      <div className="grid lg:grid-cols-[1.1fr_0.9fr] gap-10 items-center card-surface px-6 md:px-12 py-12">
        <div className="space-y-5">
          <p className="section-kicker">Our story</p>
          <h2 className="section-title">
            Born in a quiet valley in the lower North Island of Aotearoa.
          </h2>
          <p className="text-sm md:text-base text-ink-500">
            We grow native New Zealand plants on our own land and partner with trusted,
            professional cultivators who honor the whenua. Each small batch is blended
            by hand with you in mind and in heart.
          </p>
          <p className="text-sm md:text-base text-ink-500">
            Our rituals carry a little mysticism: dawn blends for light days, moonlit
            balms for slower nights, and a gentle nod to Maori healing traditions and
            kaitiakitanga. The result is a mix of light and dark, soft and earthy, always
            grounded in aroha.
          </p>
          <div className="grid sm:grid-cols-3 gap-3 text-xs md:text-sm text-ink-600">
            <div className="rounded-2xl border border-linen-100/80 bg-linen-50/80 p-4">
              Native NZ botanicals grown on site
            </div>
            <div className="rounded-2xl border border-linen-100/80 bg-linen-50/80 p-4">
              Small batches with minimal preservatives
            </div>
            <div className="rounded-2xl border border-linen-100/80 bg-linen-50/80 p-4">
              Custom pieces with your story at the center
            </div>
          </div>
          <Link href="/#contact" className="btn-primary">
            Share your story
          </Link>
        </div>

        <div className="relative">
          <div className="rounded-3xl overflow-hidden border border-linen-100/80 bg-linen-50/80">
            <Image
              className="w-full h-auto object-cover"
              src={assets.aroha_hero_botanical}
              alt="Botanical blend"
            />
          </div>
          <div className="absolute left-3 -bottom-6 sm:left-6 rounded-2xl border border-linen-100/80 bg-ink-900 text-linen-50 px-4 py-3 shadow-lg">
            <p className="text-xs uppercase tracking-[0.2em] text-linen-50/80">
              Aroha means love
            </p>
            <p className="text-sm font-semibold">Love is the first ingredient.</p>
          </div>
        </div>
      </div>
    </section>
  );
};

export default BrandStory;
