"use client";
import React from "react";
import Link from "next/link";
import { assets } from "@/assets/assets";
import { useAppContext } from "@/context/AppContext";
import Image from "next/image";

const Navbar = () => {

  const { isSeller, router } = useAppContext();

  return (
    <nav className="flex items-center justify-between px-6 md:px-16 lg:px-32 py-4 border-b border-linen-100/80 text-ink-700 bg-linen-50/70 backdrop-blur">
      <button
        className="flex flex-col leading-tight text-left"
        onClick={() => router.push("/")}
      >
        <span className="font-display text-xl md:text-2xl text-ink-900">Aroha</span>
        <span className="text-[10px] uppercase tracking-[0.3em] text-sage-700">
          Apothecary
        </span>
      </button>
      <div className="flex items-center gap-4 lg:gap-8 max-md:hidden">
        <Link href="/" className="hover:text-ink-900 transition">
          Home
        </Link>
        <Link href="/all-products" className="hover:text-ink-900 transition">
          Shop
        </Link>
        <Link href="/#rituals" className="hover:text-ink-900 transition">
          Rituals
        </Link>
        <Link href="/#custom" className="hover:text-ink-900 transition">
          Custom
        </Link>
        <Link href="/#contact" className="hover:text-ink-900 transition">
          Contact
        </Link>
        <Link href="/faq" className="hover:text-ink-900 transition">
          FAQ
        </Link>

        {isSeller && <button onClick={() => router.push('/seller')} className="text-xs border border-ink-900/20 px-4 py-1.5 rounded-full hover:border-ink-900/40 transition">Seller Dashboard</button>}

      </div>

      <ul className="hidden md:flex items-center gap-4 ">
        <Image className="w-4 h-4" src={assets.search_icon} alt="search icon" />
        <button className="flex items-center gap-2 hover:text-ink-900 transition">
          <Image src={assets.user_icon} alt="user icon" />
          Account
        </button>
      </ul>

      <div className="flex items-center md:hidden gap-3">
        {isSeller && <button onClick={() => router.push('/seller')} className="text-xs border border-ink-900/20 px-4 py-1.5 rounded-full">Seller Dashboard</button>}
        <button className="flex items-center gap-2 hover:text-ink-900 transition">
          <Image src={assets.user_icon} alt="user icon" />
          Account
        </button>
      </div>
    </nav>
  );
};

export default Navbar;
