"use client";

import { FormEvent, useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";

export default function LoginPage() {
    const router = useRouter();
    const searchParams = useSearchParams();
    const redirectTo = searchParams.get("redirect") ?? "/questionnaires";
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [error, setError] = useState<string | null>(null);
    
    async function handleSubmit(e: FormEvent<HTMLFormElement>) {
        e.preventDefault();
        setError(null);
        setIsSubmitting(true);
        
        try {
            const res = await fetch("/auth/login", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                // Payload symfo
                body: JSON.stringify({ email, password }),
            });
            
            if (!res.ok) {
                // Error ici
                const data = await res.json().catch(() => null);
                const message =
                data?.error ||
                data?.message ||
                "Impossible de vous connecter. Vérifiez vos identifiants.";
                throw new Error(message);
            }
            
            // reroute
            router.push(redirectTo);
        } catch (err) {
            const message =
            err instanceof Error
            ? err.message
            : "Erreur inattendue. Veuillez réessayer.";
            setError(message);
        } finally {
            setIsSubmitting(false);
        }
    }
    
    return (
        <div className="min-h-screen flex items-center justify-center bg-base-200">
        <div className="w-full max-w-md px-4">
        <div className="text-center mb-8">
        <h1 className="text-3xl font-bold">Connexion</h1>
        <p className="mt-2 text-sm opacity-70">
        Accédez à l’espace administration des questionnaires.
        </p>
        </div>
        
        <div className="card shadow-xl bg-base-100">
        <form className="card-body space-y-4" onSubmit={handleSubmit}>
        {error && (
            <div className="alert alert-error text-sm">
            <span>{error}</span>
            </div>
        )}
        
        <div className="form-control">
        <label className="label" htmlFor="email">
        <span className="label-text">E-mail</span>
        </label>
        <input
        id="email"
        type="email"
        className="input input-bordered w-full"
        placeholder="admin@mediatheque.fr"
        autoComplete="email"
        value={email}
        onChange={(e) => setEmail(e.target.value)}
        required
        disabled={isSubmitting}
        />
        </div>
        
        <div className="form-control">
        <label className="label" htmlFor="password">
        <span className="label-text">Mot de passe</span>
        </label>
        <input
        id="password"
        type="password"
        className="input input-bordered w-full"
        placeholder="••••"
        inputMode="numeric"
        pattern="[0-9]{4}"
        minLength={4}
        maxLength={4}
        autoComplete="current-password"
        value={password}
        onChange={(e) => {
            const digitOnly = e.target.value.replace(/\D/g, "").slice(0,4);
            setPassword(digitOnly);
        }}
        required
        disabled={isSubmitting}
        />
        </div>
        
        <div className="form-control mt-4">
        <button
        type="submit"
        className="btn btn-primary w-full"
        disabled={isSubmitting}
        >
        {isSubmitting ? (
            <span className="loading loading-spinner loading-sm" />
        ) : (
            "Se connecter"
        )}
        </button>
        </div>
        </form>
        </div>
        
        <p className="mt-4 text-center text-xs opacity-70">
            Visiteur : Email + PIN 4 chiffres.
        </p>
        </div>
        </div>
    );
}
