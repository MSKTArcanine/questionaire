import { NextRequest, NextResponse } from "next/server";

const BACKEND_URL =
process.env.BACKEND_URL ?? "http://backend:8000";

export async function POST(req: NextRequest) {
    const { email, password } = await req.json();
    
    if (!email || !password) {
        return NextResponse.json(
            { error: "Email et mot de passe requis." },
            { status: 400 }
        );
    }
    
    // Auth symfo
    const symfonyRes = await fetch(`${BACKEND_URL}/api/auth/login`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({ email, password }),
    });
    
    if (!symfonyRes.ok) {
        const data = await symfonyRes.json().catch(() => null);
        const message =
        data?.message ||
        data?.error ||
        "Identifiants invalides ou erreur serveur.";
        return NextResponse.json({ error: message }, { status: 401 });
    }
    
    const data = await symfonyRes.json();
    
    const token = data.token;
    if (!token) {
        return NextResponse.json(
            { error: "Réponse de login invalide (pas de token)." },
            { status: 500 }
        );
    }
    
    // JWT en cookie HTTP-only
    const res = NextResponse.json({ ok: true }, { status: 200 });
    
    res.cookies.set("jwt", token, {
        httpOnly: true,
        secure: true,
        sameSite: "lax",
        path: "/",
        maxAge: 60 * 60, // ttl : 3600
    });
    
    return res;
}
