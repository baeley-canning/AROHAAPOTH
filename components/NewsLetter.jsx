import React from "react";

const NewsLetter = () => {
  return (
    <div className="flex flex-col items-center justify-center text-center space-y-3 pt-8 pb-14" id="contact">
      <p className="section-kicker">Get in touch</p>
      <h1 className="md:text-4xl text-2xl font-medium">
        From our valley to your home
      </h1>
      <p className="md:text-base text-ink-500 max-w-2xl">
        Based in a quiet New Zealand valley in Aotearoa.
        Share your story for custom blends, or join the circle for seasonal releases.
      </p>
      <p className="text-sm text-ink-500 pb-6">
        Prefer email?{" "}
        <a className="text-ink-900 hover:text-ink-700 transition" href="mailto:hello@arohaapothecary.com">
          hello@arohaapothecary.com
        </a>
      </p>
      <div className="flex items-center justify-between max-w-2xl w-full md:h-14 h-12">
        <input
          className="border border-ink-900/20 rounded-md h-full border-r-0 outline-none w-full rounded-r-none px-3 text-ink-700 bg-linen-50/80"
          type="email"
          placeholder="Enter your email"
        />
        <button className="md:px-12 px-8 h-full text-linen-50 bg-sage-600 rounded-md rounded-l-none hover:bg-sage-700 transition">
          Join
        </button>
      </div>
    </div>
  );
};

export default NewsLetter;

