import { Suspense } from "react";
import OrderLookupClient from "./OrderLookupClient";

export default function MyOrdersPage() {
  return (
    <Suspense fallback={<div className="min-h-screen px-6 md:px-16 lg:px-32 py-14" />}>
      <OrderLookupClient />
    </Suspense>
  );
}
