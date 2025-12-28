import React from "react";
import ProductCard from "./ProductCard";
import { useAppContext } from "@/context/AppContext";

const HomeProducts = () => {

  const { products, router } = useAppContext()

  return (
    <div className="flex flex-col items-center pt-14" id="shop">
      <div className="w-full">
        <p className="section-kicker">Apothecary favorites</p>
        <p className="section-title mt-2">Small batch best sellers</p>
        <p className="mt-3 text-sm md:text-base text-ink-500 max-w-2xl">
          Earthy, gentle, and made with care. Explore the blends our customers come back for.
        </p>
      </div>
      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 flex-col items-center gap-6 mt-8 pb-14 w-full">
        {products.map((product, index) => <ProductCard key={index} product={product} />)}
      </div>
      <button onClick={() => { router.push('/all-products') }} className="btn-outline">
        Browse all products
      </button>
    </div>
  );
};

export default HomeProducts;
