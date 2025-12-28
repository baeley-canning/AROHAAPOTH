import React, { useState, useEffect } from "react";
import { assets } from "@/assets/assets";
import Image from "next/image";
import Link from "next/link";

const HeaderSlider = () => {
  const sliderData = [
    {
      id: 1,
      title: "Botanical remedies for calm, comfort, and glow.",
      offer: "Small batch and made with care",
      description:
        "Balms, elixirs, and body rituals grounded in nature and crafted for everyday healing.",
      buttonText1: "Shop balms",
      buttonText2: "Meet the blends",
      primaryHref: "/all-products",
      secondaryHref: "/#rituals",
      imgSrc: assets.aroha_hero_botanical,
    },
    {
      id: 2,
      title: "Gentle rituals for skin, body, and soul.",
      offer: "Earthy, natural, and beautifully simple",
      description:
        "Scrubs, body butters, and foam soaps to soften skin and ground the senses.",
      buttonText1: "Explore body care",
      buttonText2: "See rituals",
      primaryHref: "/all-products",
      secondaryHref: "/#rituals",
      imgSrc: assets.aroha_hero_ritual,
    },
    {
      id: 3,
      title: "Custom horseshoe art with meaning and memory.",
      offer: "A keepsake made to order",
      description:
        "Upcycled horseshoes turned into treasured ornaments with story, charm, and heart.",
      buttonText1: "Commission a piece",
      buttonText2: "View keepsakes",
      primaryHref: "/#custom",
      secondaryHref: "/#custom",
      imgSrc: assets.aroha_hero_horseshoe,
    },
  ];

  const [currentSlide, setCurrentSlide] = useState(0);

  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % sliderData.length);
    }, 3000);
    return () => clearInterval(interval);
  }, [sliderData.length]);

  const handleSlideChange = (index) => {
    setCurrentSlide(index);
  };

  return (
    <div className="overflow-hidden relative w-full">
      <div
        className="flex transition-transform duration-700 ease-in-out"
        style={{
          transform: `translateX(-${currentSlide * 100}%)`,
        }}
      >
        {sliderData.map((slide, index) => (
          <div
            key={slide.id}
            className="relative flex flex-col-reverse md:flex-row items-center justify-between card-surface py-8 md:px-14 px-5 mt-6 rounded-2xl min-w-full overflow-hidden"
          >
            <div className="absolute -left-16 -top-24 h-56 w-56 rounded-full bg-mist-200/70 blur-3xl"></div>
            <div className="absolute -right-16 -bottom-20 h-64 w-64 rounded-full bg-blush-200/70 blur-3xl"></div>
            <div className="relative md:pl-8 mt-10 md:mt-0">
              <p className="section-kicker pb-2">{slide.offer}</p>
              <h1 className="max-w-lg text-2xl md:text-[42px] md:leading-[48px] font-semibold text-ink-900">
                {slide.title}
              </h1>
              <p className="mt-3 max-w-lg text-sm md:text-base text-ink-500">
                {slide.description}
              </p>
              <div className="flex flex-wrap items-center mt-5 md:mt-7 gap-3">
                <Link href={slide.primaryHref} className="btn-primary">
                  {slide.buttonText1}
                </Link>
                <Link href={slide.secondaryHref} className="btn-outline">
                  {slide.buttonText2}
                </Link>
              </div>
            </div>
            <div className="flex items-center flex-1 justify-center">
              <Image
                className="md:w-80 w-56 drop-shadow-[0_20px_50px_rgba(43,36,31,0.25)]"
                src={slide.imgSrc}
                alt={`Slide ${index + 1}`}
              />
            </div>
          </div>
        ))}
      </div>

      <div className="flex items-center justify-center gap-2 mt-8">
        {sliderData.map((_, index) => (
          <div
            key={index}
            onClick={() => handleSlideChange(index)}
            className={`h-2 w-2 rounded-full cursor-pointer ${
              currentSlide === index ? "bg-sage-600" : "bg-ink-500/30"
            }`}
          ></div>
        ))}
      </div>
    </div>
  );
};

export default HeaderSlider;
