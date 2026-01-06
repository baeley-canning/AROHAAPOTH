import { Suspense } from "react";
import OrderPlacedClient from "./OrderPlacedClient";

export default function OrderPlacedPage() {
  return (
    <Suspense fallback={<div className="min-h-screen px-6 md:px-16 lg:px-32 py-14" />}>
      <OrderPlacedClient />
    </Suspense>
  );
}
