"use client";
import React from "react";
import { useAppContext } from "@/context/AppContext";
import ProductCard from "@/components/ProductCard";

const Bundles = () => {
  const { products } = useAppContext();
  const bundles = (products || []).filter((product) => product.category === "Bundle");

  if (!bundles.length) return null;

  return (
    <section className="mt-16" id="bundles">
      <div className="flex flex-col items-start">
        <p className="section-kicker">Bundles</p>
        <h2 className="section-title mt-2">Ritual sets made to gift</h2>
        <p className="mt-3 text-sm md:text-base text-ink-500 max-w-2xl">
          Three curated sets for calming nights, soft skin, and thoughtful gifting.
        </p>
      </div>
      <div className="grid grid-cols-2 md:grid-cols-3 gap-6 mt-8">
        {bundles.map((bundle) => (
          <ProductCard key={bundle._id} product={bundle} />
        ))}
      </div>
    </section>
  );
};

export default Bundles;
