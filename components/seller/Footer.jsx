import React from "react";
import { assets } from "@/assets/assets";
import Image from "next/image";

const Footer = () => {
  return (
    <div className="flex md:flex-row flex-col-reverse items-center justify-between text-left w-full px-10">
      <div className="flex items-center gap-4">
        <div className="hidden md:flex flex-col leading-tight">
          <span className="font-display text-lg text-ink-900">Aroha</span>
          <span className="text-[10px] uppercase tracking-[0.3em] text-sage-700">Apothecary</span>
        </div>
        <div className="hidden md:block h-7 w-px bg-ink-500/30"></div>
        <p className="py-4 text-center text-xs md:text-sm text-ink-500">
          Copyright 2025 Aroha Apothecary. All rights reserved. Site by{" "}
          <a className="underline underline-offset-4 hover:text-ink-900 transition" href="https://slateseo.co.nz">
            SlateSEO
          </a>
          .
        </p>
      </div>
      <div className="flex items-center gap-3">
        <a href="https://www.facebook.com/arohaapothecary" aria-label="Facebook">
          <Image src={assets.facebook_icon} alt="facebook_icon" />
        </a>
        <a href="https://www.instagram.com/arohaapothecary" aria-label="Instagram">
          <Image src={assets.instagram_icon} alt="instagram_icon" />
        </a>
      </div>
    </div>
  );
};

export default Footer;
