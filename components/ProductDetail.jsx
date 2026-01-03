"use client";
import { useEffect, useState } from "react";
import { assets } from "@/assets/assets";
import ProductCard from "@/components/ProductCard";
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";
import Image from "next/image";
import Loading from "@/components/Loading";
import { useAppContext } from "@/context/AppContext";
import React from "react";

const ProductDetail = ({ product, featuredProducts, isLoading }) => {
  const { router, addToCart, currency } = useAppContext();
  const [mainImage, setMainImage] = useState(product?.image?.[0] ?? null);

  useEffect(() => {
    setMainImage(product?.image?.[0] ?? null);
  }, [product]);

  const detailsByCategory = {
    "Balms": {
      ingredients: "Native botanicals with gentle oils and beeswax where needed.",
      usage: "Warm a small amount between fingertips and apply to dry areas.",
      shelf: "Best used within 6-12 months of opening.",
    },
    "Lip Balm": {
      ingredients: "Plant oils, butters, and natural flavoring oils.",
      usage: "Apply throughout the day as needed.",
      shelf: "Best used within 6-12 months of opening.",
    },
    "Scrubs": {
      ingredients: "Sugar, botanical oils, and gentle essential oils.",
      usage: "Massage on damp skin, rinse, and pat dry.",
      shelf: "Use within 6 months. Keep water out of the jar.",
    },
    "Body": {
      ingredients: "Whipped butters with nourishing plant oils.",
      usage: "Apply after showering for lasting softness.",
      shelf: "Best used within 6-12 months of opening.",
    },
    "Soap": {
      ingredients: "Mild cleansers with essential oil blends.",
      usage: "Lather with water and rinse.",
      shelf: "Use within 12 months for best scent.",
    },
    "Tea": {
      ingredients: "Dried native leaves and seasonal herbs.",
      usage: "Steep 1 tsp in hot water for 5-7 minutes.",
      shelf: "Use within 6 months for peak flavor.",
    },
    "Elixir": {
      ingredients: "Botanical oils and hydrosols. See label for details.",
      usage: "Apply 2-3 drops to clean skin.",
      shelf: "Best used within 6-12 months of opening.",
    },
    "Oil": {
      ingredients: "Lightweight botanical oils and extracts.",
      usage: "Massage into nails and cuticles daily.",
      shelf: "Best used within 6-12 months of opening.",
    },
    "Tincture": {
      ingredients: "Herbal extracts crafted to order. See label.",
      usage: "Follow the directions on your bottle.",
      shelf: "Best used within 12 months of opening.",
    },
    "Remedy": {
      ingredients: "Herbal extracts crafted to support balance.",
      usage: "Follow the directions on your bottle.",
      shelf: "Best used within 12 months of opening.",
    },
    "Art": {
      ingredients: "Upcycled horseshoe, charms, and custom accents.",
      usage: "Display indoors away from direct weather.",
      shelf: "Keepsake pieces last for years with care.",
    },
    "Bundle": {
      ingredients: "A curated set of small-batch blends.",
      usage: "Enjoy as a ritual set or gift to someone you love.",
      shelf: "See each product label for care and shelf life.",
    },
  };

  const detail = product
    ? detailsByCategory[product.category] || {
        ingredients: "Small-batch botanicals crafted with care.",
        usage: "Use as directed on the label.",
        shelf: "Best used within 6-12 months of opening.",
      }
    : null;

  const isCustom = product
    ? product._id.includes("custom") ||
      product.category === "Art" ||
      product.category === "Tincture"
    : false;

  if (isLoading) {
    return <Loading />;
  }

  if (!product) {
    return (
      <>
        <Navbar />
        <div className="px-6 md:px-16 lg:px-32 pt-16">
          <div className="rounded-2xl border border-linen-100/70 bg-linen-50/80 p-10 text-center">
            <p className="text-ink-900 text-lg font-medium">Product not found.</p>
            <p className="text-sm text-ink-500 mt-2">
              Try browsing the shop to find something similar.
            </p>
            <button
              onClick={() => router.push("/all-products")}
              className="btn-primary mt-5"
            >
              Browse products
            </button>
          </div>
        </div>
        <Footer />
      </>
    );
  }

  return (
    <>
      <Navbar />
      <div className="px-6 md:px-16 lg:px-32 pt-14 space-y-10">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-16">
          <div className="px-5 lg:px-16 xl:px-20">
            <div className="rounded-2xl overflow-hidden bg-linen-50/90 border border-linen-100/70 mb-4">
              <Image
                src={mainImage || product.image[0]}
                alt={product.name}
                className="w-full h-auto object-cover"
                width={1280}
                height={720}
              />
            </div>

            <div className="grid grid-cols-4 gap-4">
              {product.image.map((image, index) => (
                <div
                  key={index}
                  onClick={() => setMainImage(image)}
                  className="cursor-pointer rounded-xl overflow-hidden bg-linen-50/90 border border-linen-100/70"
                >
                  <Image
                    src={image}
                    alt={`${product.name} view ${index + 1}`}
                    className="w-full h-auto object-cover"
                    width={1280}
                    height={720}
                  />
                </div>
              ))}
            </div>
          </div>

          <div className="flex flex-col">
            <h1 className="text-3xl font-medium text-ink-900 mb-4">
              {product.name}
            </h1>
            <div className="flex items-center gap-2">
              <div className="flex items-center gap-0.5">
                <Image className="h-4 w-4" src={assets.star_icon} alt="star_icon" />
                <Image className="h-4 w-4" src={assets.star_icon} alt="star_icon" />
                <Image className="h-4 w-4" src={assets.star_icon} alt="star_icon" />
                <Image className="h-4 w-4" src={assets.star_icon} alt="star_icon" />
                <Image className="h-4 w-4" src={assets.star_dull_icon} alt="star_dull_icon" />
              </div>
              <p className="text-ink-500">(4.5)</p>
            </div>
            <p className="text-ink-500 mt-3">
              {product.description}
            </p>
            <p className="text-3xl font-medium mt-6">
              {currency}{product.offerPrice}
              <span className="text-base font-normal text-ink-500/70 line-through ml-2">
                {currency}{product.price}
              </span>
            </p>
            <hr className="bg-ink-500/30 my-6" />
            <div className="overflow-x-auto">
              <table className="table-auto border-collapse w-full max-w-72">
                <tbody>
                  <tr>
                    <td className="text-ink-500 font-medium">Brand</td>
                    <td className="text-ink-700/70">Aroha Apothecary</td>
                  </tr>
                  <tr>
                    <td className="text-ink-500 font-medium">Batch</td>
                    <td className="text-ink-700/70">Small batch</td>
                  </tr>
                  <tr>
                    <td className="text-ink-500 font-medium">Ritual</td>
                    <td className="text-ink-700/70">{product.category}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            {detail ? (
              <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-8 text-sm text-ink-600">
                <div className="rounded-2xl border border-linen-100/70 bg-linen-50/80 p-4">
                  <p className="text-ink-900 font-medium">Ingredients</p>
                  <p className="mt-1">{detail.ingredients}</p>
                </div>
                <div className="rounded-2xl border border-linen-100/70 bg-linen-50/80 p-4">
                  <p className="text-ink-900 font-medium">Usage</p>
                  <p className="mt-1">{detail.usage}</p>
                </div>
                <div className="rounded-2xl border border-linen-100/70 bg-linen-50/80 p-4">
                  <p className="text-ink-900 font-medium">Shelf life</p>
                  <p className="mt-1">{detail.shelf}</p>
                </div>
              </div>
            ) : null}

            {isCustom ? (
              <div className="mt-6 rounded-2xl border border-linen-100/70 bg-linen-50/80 p-4 text-sm text-ink-600">
                <p className="text-ink-900 font-medium">Custom lead time</p>
                <p className="mt-1">
                  This piece is made to order. Allow 7-14 days before dispatch.
                </p>
              </div>
            ) : null}

            <div className="flex items-center mt-10 gap-4">
              <button
                onClick={() => addToCart(product._id)}
                className="w-full py-3.5 bg-linen-100 text-ink-700 hover:bg-linen-50 transition"
              >
                Add to Cart
              </button>
              <button
                onClick={() => { addToCart(product._id); router.push("/cart"); }}
                className="w-full py-3.5 bg-sage-600 text-linen-50 hover:bg-sage-700 transition"
              >
                Buy now
              </button>
            </div>
          </div>
        </div>
        <div className="flex flex-col items-center">
          <div className="flex flex-col items-center mb-4 mt-16">
            <p className="text-3xl font-medium">
              Featured <span className="font-medium text-clay-500">Products</span>
            </p>
            <div className="w-28 h-0.5 bg-clay-500 mt-2"></div>
          </div>
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 mt-6 pb-14 w-full">
            {(featuredProducts || []).slice(0, 5).map((item, index) => (
              <ProductCard key={index} product={item} />
            ))}
          </div>
          <button className="btn-outline mb-16">
            See more
          </button>
        </div>
      </div>
      <Footer />
    </>
  );
};

export default ProductDetail;
