'use client'
import { productsDummyData, userDummyData } from "@/assets/assets";
import { useRouter } from "next/navigation";
import { createContext, useContext, useEffect, useState } from "react";

export const AppContext = createContext();

export const useAppContext = () => {
    return useContext(AppContext)
}

export const AppContextProvider = (props) => {

    const currency = process.env.NEXT_PUBLIC_CURRENCY
    const router = useRouter()
    const cartStorageKey = "aroha_cart"
    const basePath = (process.env.NEXT_PUBLIC_BASE_PATH || "").replace(/\/$/, "")

    const [products, setProducts] = useState([])
    const [userData, setUserData] = useState(false)
    const [cartItems, setCartItems] = useState({})
    const [hasHydratedCart, setHasHydratedCart] = useState(false)

    const fetchProductData = async () => {
        try {
            const response = await fetch(`${basePath}/api/products.php`, {
                cache: "no-store",
            });
            if (!response.ok) {
                throw new Error("Failed to fetch products");
            }
            const data = await response.json();
            if (Array.isArray(data) && data.length > 0) {
                setProducts(data);
                return;
            }
        } catch (error) {
            console.warn("Falling back to local products.", error);
        }
        setProducts(productsDummyData);
    }

    const fetchUserData = async () => {
        setUserData(userDummyData)
    }

    const addToCart = async (itemId) => {

        let cartData = structuredClone(cartItems);
        if (cartData[itemId]) {
            cartData[itemId] += 1;
        }
        else {
            cartData[itemId] = 1;
        }
        setCartItems(cartData);

    }

    const updateCartQuantity = async (itemId, quantity) => {

        let cartData = structuredClone(cartItems);
        if (quantity === 0) {
            delete cartData[itemId];
        } else {
            cartData[itemId] = quantity;
        }
        setCartItems(cartData)

    }

    const getCartCount = () => {
        let totalCount = 0;
        for (const items in cartItems) {
            if (cartItems[items] > 0) {
                totalCount += cartItems[items];
            }
        }
        return totalCount;
    }

    const getCartAmount = () => {
        let totalAmount = 0;
        for (const items in cartItems) {
            let itemInfo = products.find((product) => product._id === items);
            if (!itemInfo) continue;
            if (cartItems[items] > 0) {
                totalAmount += itemInfo.offerPrice * cartItems[items];
            }
        }
        return Math.floor(totalAmount * 100) / 100;
    }

    const hydrateCart = () => {
        if (typeof window === "undefined") return;
        try {
            const stored = window.localStorage.getItem(cartStorageKey);
            if (stored) {
                const parsed = JSON.parse(stored);
                if (parsed && typeof parsed === "object") {
                    const cleaned = {};
                    Object.entries(parsed).forEach(([key, value]) => {
                        const quantity = Number(value);
                        if (Number.isFinite(quantity) && quantity > 0) {
                            cleaned[key] = Math.floor(quantity);
                        }
                    });
                    setCartItems(cleaned);
                }
            }
        } catch (error) {
            console.warn("Cart persistence unavailable.", error);
        } finally {
            setHasHydratedCart(true);
        }
    }

    const persistCart = () => {
        if (typeof window === "undefined") return;
        if (!hasHydratedCart) return;
        try {
            window.localStorage.setItem(cartStorageKey, JSON.stringify(cartItems));
        } catch (error) {
            console.warn("Cart persistence unavailable.", error);
        }
    }

    useEffect(() => {
        fetchProductData()
    }, [])

    useEffect(() => {
        fetchUserData()
    }, [])

    useEffect(() => {
        hydrateCart()
    }, [])

    useEffect(() => {
        if (!hasHydratedCart || products.length === 0) return;
        const validIds = new Set(products.map((product) => product._id));
        setCartItems((prev) => {
            let changed = false;
            const next = {};
            Object.entries(prev).forEach(([key, value]) => {
                if (validIds.has(key) && value > 0) {
                    next[key] = value;
                } else {
                    changed = true;
                }
            });
            return changed ? next : prev;
        });
    }, [products, hasHydratedCart])

    useEffect(() => {
        persistCart()
    }, [cartItems, hasHydratedCart])

    const value = {
        currency, router,
        userData, fetchUserData,
        products, fetchProductData,
        cartItems, setCartItems,
        addToCart, updateCartQuantity,
        getCartCount, getCartAmount
    }

    return (
        <AppContext.Provider value={value}>
            {props.children}
        </AppContext.Provider>
    )
}
