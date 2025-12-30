import React from "react";
import { assets } from "@/assets/assets";
import Image from "next/image";

const galleryImages = [
  { src: assets.aroha_product_balm, label: "Balms" },
  { src: assets.aroha_product_scrub, label: "Scrubs" },
  { src: assets.aroha_product_tincture, label: "Tinctures" },
  { src: assets.aroha_product_tea, label: "Tea blends" },
  { src: assets.aroha_product_soap, label: "Foam soap" },
  { src: assets.aroha_product_horseshoe, label: "Horseshoe art" },
];

const Gallery = () => {
  return (
    <section className="mt-16" id="gallery">
      <div className="flex flex-col items-start">
        <p className="section-kicker">From the workroom</p>
        <h2 className="section-title mt-2">Small batch, photographed with care</h2>
        <p className="mt-3 text-sm md:text-base text-ink-500 max-w-2xl">
          A peek at our current blends and keepsakes. Each batch varies slightly with
          the season and the harvest.
        </p>
      </div>
      <div className="grid grid-cols-2 md:grid-cols-3 gap-4 mt-8">
        {galleryImages.map((image) => (
          <div
            key={image.label}
            className="group rounded-2xl overflow-hidden border border-linen-100/70 bg-linen-50/80"
          >
            <Image
              className="w-full h-auto object-cover group-hover:scale-[1.02] transition"
              src={image.src}
              alt={image.label}
            />
            <div className="px-4 py-3 text-sm text-ink-600">{image.label}</div>
          </div>
        ))}
      </div>
    </section>
  );
};

export default Gallery;
