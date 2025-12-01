import nextVitals from "eslint-config-next/core-web-vitals";
import nextTs from "eslint-config-next/typescript";
import sonarjs from "eslint-plugin-sonarjs";
import { defineConfig, globalIgnores } from "eslint/config";

export default defineConfig([
  // Config Next.js
  ...nextVitals,
  ...nextTs,

  // Règles SonarJS recommandées (active déjà le plugin)
  sonarjs.configs.recommended,

  // Overrides perso des règles SonarJS (sans redéclarer le plugin)
  {
    rules: {
      "sonarjs/cognitive-complexity": ["warn", 15],
      // Tu pourras en rajouter ici au besoin
      // "sonarjs/no-duplicate-string": "warn",
      // "sonarjs/no-identical-functions": "warn",
    },
  },

  // Fichiers ignorés
  globalIgnores([
    ".next/**",
    "out/**",
    "build/**",
    "next-env.d.ts",
  ]),
]);
