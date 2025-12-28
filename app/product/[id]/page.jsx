import { productsDummyData } from "@/assets/assets";
import { notFound } from "next/navigation";
import ProductClient from "./ProductClient";

export const dynamic = "force-static";
export const dynamicParams = false;

export function generateStaticParams() {
  return productsDummyData.map((product) => ({
    id: product._id,
  }));
}

export default function ProductPage({ params }) {
  const product = productsDummyData.find((item) => item._id === params.id);

  if (!product) {
    notFound();
  }

  return (
    <ProductClient
      product={product}
      featuredProducts={productsDummyData}
    />
  );
}
