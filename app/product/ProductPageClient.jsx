"use client";
import { useEffect, useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { useAppContext } from "@/context/AppContext";
import ProductDetail from "@/components/ProductDetail";

const ProductPageClient = () => {
  const searchParams = useSearchParams();
  const rawId = searchParams.get("id") || "";
  const router = useRouter();
  const { products } = useAppContext();
  const [product, setProduct] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const [resolvedId, setResolvedId] = useState("");
  const [hasResolvedId, setHasResolvedId] = useState(false);

  const basePath = (process.env.NEXT_PUBLIC_BASE_PATH || "").replace(/\/$/, "");

  const isInvalidId = (value) => {
    const normalized = String(value || "").trim().toLowerCase();
    if (!normalized) return true;
    return ["product", "index.html", "index.txt"].includes(normalized);
  };

  const resolveIdFromPath = () => {
    if (typeof window === "undefined") return "";
    const path = window.location.pathname.replace(/\/$/, "");
    const segments = path.split("/").filter(Boolean);
    const productIndex = segments.lastIndexOf("product");
    if (productIndex === -1) return "";
    const candidate = segments[productIndex + 1] || "";
    if (isInvalidId(candidate)) return "";
    return candidate;
  };

  useEffect(() => {
    let nextId = rawId;
    if (isInvalidId(nextId)) {
      nextId = "";
    }

    if (!nextId && typeof window !== "undefined") {
      const params = new URLSearchParams(window.location.search);
      const paramId = params.get("id") || "";
      if (!isInvalidId(paramId)) {
        nextId = paramId;
      }
    }

    if (!nextId) {
      const pathId = resolveIdFromPath();
      if (pathId) {
        nextId = pathId;
      }
    }

    setResolvedId(nextId);
    setHasResolvedId(true);
  }, [rawId]);

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
    if (!hasResolvedId) return;
    if (!resolvedId) {
      router.replace("/all-products");
      return;
    }

    setIsLoading(true);
    const existing = (products || []).find((item) => item._id === resolvedId);
    if (existing) {
      setProduct(existing);
      setIsLoading(false);
      return;
    }

    const fetchProduct = async () => {
      try {
        const response = await fetch(`${basePath}/api/product.php?id=${encodeURIComponent(resolvedId)}`, {
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
  }, [resolvedId, hasResolvedId, basePath, products, router]);

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
