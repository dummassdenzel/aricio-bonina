import typography from "@tailwindcss/typography";
import type { Config } from "tailwindcss";

export default {
  content: ["./src/**/*.{html,js,svelte,ts}"],

  theme: {
    extend: {
      fontFamily: {
        dmSans: ["DM Sans", "sans-serif"], // for body text
        inter: ["Inter", "serif"], // for headings
      },

      colors: {
        // requested by beneficiary
        beige: "#F2EBE2", // for dark theme text
        lilac: "#CCC9DC", // major elements
        midnight: "#1B2A41", // background color
        slate: "#495F76", // supporting elements
        teal: "#314A60", // for light theme text
        lightteal: "rgba(73, 95, 118, 0.4)",

        // added extras
        muted: "#686868",
        backdrop: "#E2E2E2",
        back: "rgba(239, 239, 239, 0.3)",
        drop: "rgba(239, 239, 239, 0.7)",

        green: "#A3C6B2", 
        orange: "#E78F81", 
        red: "#ef4444", 
        blue: "#394867",

        green20: "rgba(163, 198, 178, 0.1)",
        orange20: "rgb(231, 143, 129, 0.1)", 
        red20: "rgba(179, 100, 92, 0.1)",
        blue20: "rgb(57, 72, 103, 0.2)"
      },
    },
  },

  plugins: [typography],
} as Config;
