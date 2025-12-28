import React from "react";
import { assets } from "@/assets/assets";
import Image from "next/image";
import Link from "next/link";

const products = [
  {
    id: 1,
    image: assets.aroha_hero_botanical,
    title: "Balms + Elixirs",
    description: "Plant-powered remedies crafted for calm, comfort, and glow.",
  },
  {
    id: 2,
    image: assets.aroha_hero_ritual,
    title: "Body Rituals",
    description: "Scrubs, butters, and soaps that soften the skin and senses.",
  },
  {
    id: 3,
    image: assets.aroha_hero_horseshoe,
    title: "Horseshoe Keepsakes",
    description: "Custom art pieces made from well-worn horseshoes with story.",
  },
];

const FeaturedProduct = () => {
  return (
    <div className="mt-16" id="rituals">
      <div className="flex flex-col items-center text-center">
        <p className="section-kicker">Rituals and keepsakes</p>
        <p className="section-title mt-2">Made with heart and hand</p>
        <div className="w-28 h-0.5 bg-clay-500 mt-4"></div>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-14 mt-12 md:px-14 px-4">
        {products.map(({ id, image, title, description }) => (
          <div key={id} className="relative group overflow-hidden rounded-3xl border border-linen-100/70 bg-linen-50/80">
            <Image
              src={image}
              alt={title}
              className="group-hover:brightness-95 transition duration-300 w-full h-auto object-cover"
            />
            <div className="group-hover:-translate-y-3 transition duration-300 absolute bottom-8 left-8 text-ink-900 space-y-2">
              <p className="font-semibold text-xl lg:text-2xl">{title}</p>
              <p className="text-sm lg:text-base leading-5 max-w-64 text-ink-700">
                {description}
              </p>
              <Link href="/all-products" className="btn-primary">
                Explore
              </Link>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default FeaturedProduct;
