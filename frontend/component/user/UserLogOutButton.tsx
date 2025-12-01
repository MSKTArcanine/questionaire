"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";

export function UserLogoutButton() {
  const router = useRouter();
  const [loading, setLoading] = useState(false);

  async function handleLogout() {
    try {
      setLoading(true);

      const res = await fetch("/auth/logout", {
        method: "GET",
      });

      // Log.
      if (!res.ok) {
        console.error("Erreur pendant le logout", await res.text());
      }

      // On renvoi vers le login
      router.push("/login/user");
    } catch (e) {
      console.error(e);
      router.push("/login/user");
    } finally {
      setLoading(false);
    }
  }

  return (
    <button
      type="button"
      className="btn btn-ghost btn-sm"
      onClick={handleLogout}
      disabled={loading}
    >
      {loading ? (
        <span className="loading loading-spinner loading-xs" />
      ) : (
        "Se déconnecter"
      )}
    </button>
  );
}
