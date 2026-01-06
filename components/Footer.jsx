import React from "react";
import { assets } from "@/assets/assets";
import Image from "next/image";

const Footer = () => {
  return (
    <footer className="mt-10">
      <div className="flex flex-col md:flex-row items-start justify-center px-6 md:px-16 lg:px-32 gap-10 py-14 border-t border-linen-100/80 text-ink-500">
        <div className="w-full md:w-2/5">
          <div className="flex flex-col leading-tight">
            <span className="font-display text-2xl text-ink-900">Aroha</span>
            <span className="text-[11px] uppercase tracking-[0.35em] text-sage-700">
              Apothecary
            </span>
          </div>
          <p className="mt-6 text-sm max-w-sm">
            Natural healing from the heart. Small batch rongoa, gentle body rituals, and
            custom keepsakes grounded in care and kaitiakitanga.
          </p>
          <p className="mt-4 text-xs text-ink-500 max-w-sm">
            NZ shipping, 14-day returns on unopened items, and secure checkout via Stripe.
          </p>
          <div className="flex items-center gap-4 mt-6">
            <a href="https://www.facebook.com/arohaapothecary" aria-label="Facebook">
              <Image src={assets.facebook_icon} alt="facebook_icon" />
            </a>
            <a href="https://www.instagram.com/arohaapothecary" aria-label="Instagram">
              <Image src={assets.instagram_icon} alt="instagram_icon" />
            </a>
          </div>
        </div>

        <div className="w-full md:w-1/5">
          <h2 className="font-medium text-ink-900 mb-5">Shop</h2>
          <ul className="text-sm space-y-2">
            <li>
              <a className="hover:text-ink-900 transition" href="/all-products">All products</a>
            </li>
            <li>
              <a className="hover:text-ink-900 transition" href="/#rituals">Rituals</a>
            </li>
            <li>
              <a className="hover:text-ink-900 transition" href="/#custom">Custom orders</a>
            </li>
          </ul>
        </div>

        <div className="w-full md:w-1/5">
          <h2 className="font-medium text-ink-900 mb-5">Support</h2>
          <ul className="text-sm space-y-2">
            <li>
              <a className="hover:text-ink-900 transition" href="/about">About</a>
            </li>
            <li>
              <a className="hover:text-ink-900 transition" href="/faq">FAQs</a>
            </li>
            <li>
              <a className="hover:text-ink-900 transition" href="/policies">Refunds &amp; CGA</a>
            </li>
            <li>
              <a className="hover:text-ink-900 transition" href="/policies">Product care</a>
            </li>
            <li>
              <a className="hover:text-ink-900 transition" href="/my-orders">Order lookup</a>
            </li>
            <li>
              <a className="hover:text-ink-900 transition" href="/admin/">Owner portal</a>
            </li>
          </ul>
        </div>

        <div className="w-full md:w-1/5">
          <h2 className="font-medium text-ink-900 mb-5">Get in touch</h2>
          <div className="text-sm space-y-2">
            <p>Lower North Island valley, Aotearoa New Zealand</p>
            <p>arohaapothecary@gmail.com</p>
          </div>
        </div>
      </div>
      <p className="py-4 text-center text-xs md:text-sm text-ink-500">
        Copyright 2025 Aroha Apothecary. All rights reserved. Site by{" "}
        <a className="underline underline-offset-4 hover:text-ink-900 transition" href="https://slateseo.co.nz">
          SlateSEO
        </a>
        .
      </p>
    </footer>
  );
};

export default Footer;
