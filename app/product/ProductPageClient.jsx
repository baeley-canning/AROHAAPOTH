"use client";
import { useEffect, useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { useAppContext } from "@/context/AppContext";
import ProductDetail from "@/components/ProductDetail";

const ProductPageClient = () => {
  const searchParams = useSearchParams();
  const id = searchParams.get("id");
  const router = useRouter();
  const { products } = useAppContext();
  const [product, setProduct] = useState(null);
  const [isLoading, setIsLoading] = useState(true);

  const basePath = (process.env.NEXT_PUBLIC_BASE_PATH || "").replace(/\/$/, "");

  const updateMeta = (selector, content) => {
    if (!content || typeof document === "undefined") return;
    let tag = document.querySelector(selector);
    if (!tag) {
      tag = document.createElement("meta");
      const match = selector.match(/\[(.+?)=['"](.+?)['"]\]/);
      if (match) {
        tag.setAttribute(match[1], match[2]);
      }
      document.head.appendChild(tag);
    }
    tag.setAttribute("content", content);
  };

  useEffect(() => {
    if (!id) {
      setIsLoading(false);
      router.replace("/all-products");
      return;
    }

    const existing = (products || []).find((item) => item._id === id);
    if (existing) {
      setProduct(existing);
      setIsLoading(false);
      return;
    }

    const fetchProduct = async () => {
      try {
        const response = await fetch(`${basePath}/api/product.php?id=${encodeURIComponent(id)}`, {
          cache: "no-store",
        });
        if (!response.ok) {
          throw new Error("Product not found");
        }
        const data = await response.json();
        setProduct(data);
      } catch (error) {
        setProduct(null);
      } finally {
        setIsLoading(false);
      }
    };

    fetchProduct();
  }, [id, basePath, products, router]);

  useEffect(() => {
    if (!product) return;
    const title = product.seoTitle || `${product.name} | Aroha Apothecary`;
    const description = product.seoDescription || product.description;

    document.title = title;
    updateMeta('meta[name="description"]', description);
    updateMeta('meta[property="og:title"]', title);
    updateMeta('meta[property="og:description"]', description);
    updateMeta('meta[name="twitter:title"]', title);
    updateMeta('meta[name="twitter:description"]', description);
  }, [product]);

  return (
    <ProductDetail
      product={product}
      featuredProducts={products}
      isLoading={isLoading}
    />
  );
};

export default ProductPageClient;
