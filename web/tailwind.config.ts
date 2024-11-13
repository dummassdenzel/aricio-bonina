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

        // added extras
        muted: "#686868",
        backdrop: "#E2E2E2",
        back: "rgba(239, 239, 239, 0.3)",
      },
    },
  },

  plugins: [typography],
} as Config;
