"use client";
import { useEffect, useState } from "react";
import { useSearchParams } from "next/navigation";
import { useAppContext } from "@/context/AppContext";
import ProductDetail from "@/components/ProductDetail";

const ProductPage = () => {
  const searchParams = useSearchParams();
  const id = searchParams.get("id");
  const { products } = useAppContext();
  const [product, setProduct] = useState(null);
  const [isLoading, setIsLoading] = useState(true);

  const basePath = (process.env.NEXT_PUBLIC_BASE_PATH || "").replace(/\/$/, "");

  useEffect(() => {
    if (!id) {
      setIsLoading(false);
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
  }, [id, basePath, products]);

  return (
    <ProductDetail
      product={product}
      featuredProducts={products}
      isLoading={isLoading}
    />
  );
};

export default ProductPage;
