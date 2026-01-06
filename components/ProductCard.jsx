import React from 'react'
import Link from 'next/link';
import { assets } from '@/assets/assets'
import Image from 'next/image';
import { useAppContext } from '@/context/AppContext';

const ProductCard = ({ product }) => {

    const { currency, router, addToCart } = useAppContext()
    const productLink = `/product?id=${product._id}`;
    const imageAlt = product.imageAlt || product.name;

    return (
        <Link
            href={productLink}
            className="flex flex-col items-start gap-0.5 max-w-[220px] w-full cursor-pointer"
        >
            <div className="cursor-pointer group relative bg-linen-50/90 border border-linen-100/70 rounded-2xl w-full h-52 flex items-center justify-center">
                <Image
                    src={product.image[0]}
                    alt={imageAlt}
                    className="group-hover:scale-105 transition object-cover w-4/5 h-4/5 md:w-full md:h-full"
                    width={800}
                    height={800}
                />
                <button className="absolute top-2 right-2 bg-linen-50/90 p-2 rounded-full shadow-md border border-linen-100/70">
                    <Image
                        className="h-3 w-3"
                        src={assets.heart_icon}
                        alt="heart_icon"
                    />
                </button>
            </div>

            <p className="md:text-base font-medium pt-3 w-full truncate text-ink-900">{product.name}</p>
            <p className="w-full text-xs text-ink-500 max-sm:hidden truncate">{product.description}</p>
            <div className="flex items-center gap-2">
                <p className="text-xs text-ink-500">{4.5}</p>
                <div className="flex items-center gap-0.5">
                    {Array.from({ length: 5 }).map((_, index) => (
                        <Image
                            key={index}
                            className="h-3 w-3"
                            src={
                                index < Math.floor(4)
                                    ? assets.star_icon
                                    : assets.star_dull_icon
                            }
                            alt="star_icon"
                        />
                    ))}
                </div>
            </div>

            <div className="flex items-end justify-between w-full mt-1">
                <p className="text-base font-medium text-ink-900">{currency}{product.offerPrice}</p>
                <button
                    onClick={(event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        addToCart(product._id);
                    }}
                    className="max-sm:hidden px-4 py-1.5 text-ink-700 border border-ink-900/15 rounded-full text-xs hover:border-ink-900/35 hover:bg-linen-100 transition"
                >
                    Add to cart
                </button>
            </div>
        </Link>
    )
}

export default ProductCard
