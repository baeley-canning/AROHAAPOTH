import { Cormorant_Garamond, Source_Sans_3 } from "next/font/google";
import "./globals.css";
import { AppContextProvider } from "@/context/AppContext";
import { Toaster } from "react-hot-toast";

const display = Cormorant_Garamond({
  subsets: ["latin"],
  weight: ["400", "500", "600", "700"],
  variable: "--font-display",
});

const body = Source_Sans_3({
  subsets: ["latin"],
  weight: ["300", "400", "500", "600"],
  variable: "--font-body",
});

export const metadata = {
  title: "Aroha Apothecary",
  description: "Botanical remedies, body rituals, and custom keepsakes made with care.",
  metadataBase: new URL("https://arohaapothecary.com"),
  alternates: {
    canonical: "https://arohaapothecary.com",
  },
  robots: {
    index: true,
    follow: true,
  },
  openGraph: {
    title: "Aroha Apothecary",
    description: "Botanical remedies, body rituals, and custom keepsakes made with care.",
    url: "https://arohaapothecary.com",
    siteName: "Aroha Apothecary",
    locale: "en_NZ",
    type: "website",
  },
  twitter: {
    card: "summary_large_image",
    title: "Aroha Apothecary",
    description: "Botanical remedies, body rituals, and custom keepsakes made with care.",
  },
};

export default function RootLayout({ children }) {
  return (
      <html lang="en">
        <body className={`${display.variable} ${body.variable} antialiased text-ink-900`} >
          <Toaster />
          <AppContextProvider>
            {children}
          </AppContextProvider>
        </body>
      </html>
  );
}
