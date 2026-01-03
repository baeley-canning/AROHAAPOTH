'use client'
import { assets } from "@/assets/assets";
import Image from "next/image";
import Link from "next/link";
import { useAppContext } from "@/context/AppContext";
import { useEffect } from "react";

const OrderPlaced = () => {
  const { setCartItems } = useAppContext();

  useEffect(() => {
    setCartItems({});
  }, [setCartItems]);

  return (
    <div className="min-h-screen flex items-center justify-center px-6">
      <div className="card-surface max-w-lg w-full text-center px-8 py-10">
        <div className="mx-auto flex h-20 w-20 items-center justify-center rounded-full border border-linen-100/80 bg-linen-50/80">
          <Image className="w-10 h-10" src={assets.checkmark} alt="Order confirmed" />
        </div>
        <h1 className="text-2xl md:text-3xl font-semibold mt-6">Order confirmed</h1>
        <p className="text-sm md:text-base text-ink-500 mt-3">
          Thank you for supporting Aroha Apothecary. Stripe will email your receipt and
          shipping confirmation. If you need anything, reach us at{" "}
          <a className="text-ink-900 hover:text-ink-700 transition" href="mailto:arohaapothecary@gmail.com">
            arohaapothecary@gmail.com
          </a>
          .
        </p>
        <div className="flex flex-col sm:flex-row gap-3 justify-center mt-6">
          <Link href="/all-products" className="btn-primary">
            Continue shopping
          </Link>
          <Link href="/#contact" className="btn-outline">
            Get in touch
          </Link>
        </div>
      </div>
    </div>
  );
};

export default OrderPlaced;
