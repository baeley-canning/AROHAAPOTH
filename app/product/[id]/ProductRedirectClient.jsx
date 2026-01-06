"use client";
import { useEffect } from "react";
import { useRouter } from "next/navigation";

const ProductRedirectClient = ({ id }) => {
  const router = useRouter();

  useEffect(() => {
    if (!id) return;
    router.replace(`/product?id=${encodeURIComponent(id)}`);
  }, [id, router]);

  return null;
};

export default ProductRedirectClient;
