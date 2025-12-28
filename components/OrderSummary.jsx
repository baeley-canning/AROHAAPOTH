import { addressDummyData } from "@/assets/assets";
import { useAppContext } from "@/context/AppContext";
import React, { useEffect, useState } from "react";

const OrderSummary = () => {

  const { currency, router, getCartCount, getCartAmount } = useAppContext()
  const [selectedAddress, setSelectedAddress] = useState(null);
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);

  const [userAddresses, setUserAddresses] = useState([]);

  const fetchUserAddresses = async () => {
    setUserAddresses(addressDummyData);
  }

  const handleAddressSelect = (address) => {
    setSelectedAddress(address);
    setIsDropdownOpen(false);
  };

  const createOrder = async () => {

  }

  useEffect(() => {
    fetchUserAddresses();
  }, [])

  return (
    <div className="w-full md:w-96 bg-linen-50/80 border border-linen-100/70 p-5 rounded-2xl">
      <h2 className="text-xl md:text-2xl font-medium text-ink-900">
        Order Summary
      </h2>
      <hr className="border-ink-500/20 my-5" />
      <div className="space-y-6">
        <div>
          <label className="text-base font-medium uppercase text-ink-500 block mb-2">
            Select Address
          </label>
          <div className="relative inline-block w-full text-sm border border-ink-900/15">
            <button
              className="peer w-full text-left px-4 pr-2 py-2 bg-linen-50 text-ink-700 focus:outline-none"
              onClick={() => setIsDropdownOpen(!isDropdownOpen)}
            >
              <span>
                {selectedAddress
                  ? `${selectedAddress.fullName}, ${selectedAddress.area}, ${selectedAddress.city}, ${selectedAddress.state}`
                  : "Select Address"}
              </span>
              <svg className={`w-5 h-5 inline float-right transition-transform duration-200 ${isDropdownOpen ? "rotate-0" : "-rotate-90"}`}
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#6B7280"
              >
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            {isDropdownOpen && (
              <ul className="absolute w-full bg-linen-50 border border-linen-100/70 shadow-md mt-1 z-10 py-1.5">
                {userAddresses.map((address, index) => (
                  <li
                    key={index}
                    className="px-4 py-2 hover:bg-linen-100 cursor-pointer"
                    onClick={() => handleAddressSelect(address)}
                  >
                    {address.fullName}, {address.area}, {address.city}, {address.state}
                  </li>
                ))}
                <li
                  onClick={() => router.push("/add-address")}
                  className="px-4 py-2 hover:bg-linen-100 cursor-pointer text-center"
                >
                  + Add New Address
                </li>
              </ul>
            )}
          </div>
        </div>

        <div>
          <label className="text-base font-medium uppercase text-ink-500 block mb-2">
            Promo Code
          </label>
          <div className="flex flex-col items-start gap-3">
            <input
              type="text"
              placeholder="Enter promo code"
              className="flex-grow w-full outline-none p-2.5 text-ink-700 border border-ink-900/15 bg-linen-50"
            />
            <button className="bg-sage-600 text-linen-50 px-9 py-2 hover:bg-sage-700">
              Apply
            </button>
          </div>
        </div>

        <hr className="border-ink-500/20 my-5" />

        <div className="space-y-4">
          <div className="flex justify-between text-base font-medium">
            <p className="uppercase text-ink-500">Items {getCartCount()}</p>
            <p className="text-ink-900">{currency}{getCartAmount()}</p>
          </div>
          <div className="flex justify-between">
            <p className="text-ink-500">Shipping Fee</p>
            <p className="font-medium text-ink-900">Free</p>
          </div>
          <div className="flex justify-between">
            <p className="text-ink-500">Tax (2%)</p>
            <p className="font-medium text-ink-900">{currency}{Math.floor(getCartAmount() * 0.02)}</p>
          </div>
          <div className="flex justify-between text-lg md:text-xl font-medium border-t pt-3">
            <p>Total</p>
            <p>{currency}{getCartAmount() + Math.floor(getCartAmount() * 0.02)}</p>
          </div>
        </div>
      </div>

      <button onClick={createOrder} className="w-full bg-sage-600 text-linen-50 py-3 mt-5 hover:bg-sage-700">
        Place Order
      </button>
    </div>
  );
};

export default OrderSummary;
