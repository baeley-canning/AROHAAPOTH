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

export function generateMetadata({ params }) {
  const product = productsDummyData.find((item) => item._id === params.id);
  if (!product) {
    return {
      title: "Product | Aroha Apothecary",
      description: "Small-batch botanicals and custom keepsakes from Aotearoa.",
    };
  }
  return {
    title: `${product.name} | Aroha Apothecary`,
    description: product.description,
  };
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
