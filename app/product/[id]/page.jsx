import { productsDummyData } from "@/assets/assets";
import ProductRedirectClient from "./ProductRedirectClient";

export const dynamic = "force-static";
export const dynamicParams = false;

export function generateStaticParams() {
  return productsDummyData.map((product) => ({
    id: product._id,
  }));
}

export default function ProductRedirect({ params }) {
  return <ProductRedirectClient id={params?.id} />;
}
