import { Suspense } from "react";
import Loading from "@/components/Loading";
import ProductPageClient from "./ProductPageClient";

export default function ProductPage() {
  return (
    <Suspense fallback={<Loading />}>
      <ProductPageClient />
    </Suspense>
  );
}
